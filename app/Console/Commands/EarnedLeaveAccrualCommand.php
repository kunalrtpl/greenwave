<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LeaveType;
use App\UserLeaveSetting;
use App\UserLeaveQuota;
use App\UserElAccrualLog;
use Carbon\Carbon;
use DB;

/**
 * EarnedLeaveAccrualCommand
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Schedule: 1st of every month at 00:05
 * Kernel:   $schedule->command('attendance:el-accrual')->monthlyOn(1, '00:05');
 *
 * What it does:
 * ─────────────────────────────────────────────────────────────────────────────
 * CASE A — Regular Monthly Accrual (any month except April = FY start)
 *   For each active user with an EL setting for the current FY:
 *     1. Look up their monthly_accrual rate (e.g. 1.25)
 *     2. Get current EL balance (total_quota - used_quota)
 *     3. If carry_forward_limit > 0, cap the addition so balance never exceeds limit
 *     4. Increment user_leave_quotas.total_quota
 *     5. Log the event in user_el_accrual_logs
 *     6. Skip if this month was already processed (idempotent)
 *
 * CASE B — April 1 = New Financial Year Rollover
 *   For each active user with an EL setting for the PREVIOUS FY:
 *     1. Calculate leftover balance = total_quota - used_quota
 *     2. If carry_forward = true:
 *          - Carry balance to new FY (capped at carry_forward_limit)
 *          - Create new FY quota row seeded with carry-forward amount
 *          - Log source='fy_carry_forward'
 *        If carry_forward = false:
 *          - Start new FY with 0 balance
 *          - Log source='fy_reset' (old quota row preserved for history)
 *     3. Clone user_leave_settings to new FY (so accrual continues)
 *     4. Then apply April's regular monthly accrual on top
 *
 * Idempotency:
 *   Checks user_el_accrual_logs for (user_id, accrual_month, accrual_year)
 *   before processing — safe to re-run if cron fires twice.
 *
 * Usage:
 *   php artisan attendance:el-accrual              ← runs for current month
 *   php artisan attendance:el-accrual --month=4 --year=2026   ← backfill
 *   php artisan attendance:el-accrual --dry-run    ← preview without saving
 */
class EarnedLeaveAccrualCommand extends Command
{
    protected $signature = 'attendance:el-accrual
                            {--month= : Override month (1-12). Defaults to current month.}
                            {--year=  : Override year. Defaults to current year.}
                            {--user=  : Process only this user_id (for testing).}
                            {--dry-run : Preview what would happen without saving.}';

    protected $description = 'Monthly Earned Leave accrual + Financial Year rollover';

    public function handle()
    {
        $isDryRun     = $this->option('dry-run');
        $targetMonth  = (int) ($this->option('month') ?: now()->month);
        $targetYear   = (int) ($this->option('year')  ?: now()->year);
        $onlyUserId   = $this->option('user') ? (int) $this->option('user') : null;

        $this->info("=== EL Accrual: {$targetMonth}/{$targetYear} " . ($isDryRun ? '[DRY RUN]' : '') . " ===");

        // Identify the EL leave type (code = 'EL')
        $elLeaveType = LeaveType::where('code', 'EL')->where('is_active', true)->first();

        if (!$elLeaveType) {
            $this->error('Earned Leave type (code=EL) not found or inactive. Aborting.');
            return 1;
        }

        $this->info("EL Leave Type ID: {$elLeaveType->id}");

        // Is this April? → FY rollover month
        $isFyRollover = ($targetMonth === 4);

        if ($isFyRollover) {
            $this->info('April detected → Running FY Rollover first...');
            $this->runFyRollover($elLeaveType, $targetYear, $onlyUserId, $isDryRun);
        }

        // Always run regular monthly accrual after rollover (April gets both)
        $this->runMonthlyAccrual($elLeaveType, $targetMonth, $targetYear, $onlyUserId, $isDryRun);

        $this->info('=== Done ===');
        return 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MONTHLY ACCRUAL
    // ─────────────────────────────────────────────────────────────────────────
    protected function runMonthlyAccrual(
        LeaveType $elLeaveType,
        int $month,
        int $year,
        ?int $onlyUserId,
        bool $isDryRun
    ): void {
        $fy = $this->getFinancialYear($month, $year);

        $this->info("FY for this month: {$fy}");

        // ── Step 1: Fetch all active users (or just one if --user flag used) ──
        // We do NOT query user_leave_settings first — that would miss users who
        // have never had a setting row yet. Instead we fetch all users and call
        // ensureQuota() which auto-creates settings + quota rows with defaults.
        $userQuery = DB::table('users')->where('status', 1)->select('id');

        if ($onlyUserId) {
            $userQuery->where('id', $onlyUserId);
        }

        $users = $userQuery->get();

        if ($users->isEmpty()) {
            $this->warn("No active users found. Nothing to process.");
            return;
        }

        $this->info("Found {$users->count()} active user(s). Auto-creating settings if missing...");

        // ── Step 2: Ensure every user has a settings + quota row for this FY ──
        // This is the key fix: ensureQuota() creates user_leave_settings with
        // defaults (5 days opening, carry_forward=true, limit=10) if none exists.
        foreach ($users as $u) {
            $this->ensureQuota($u->id, $elLeaveType->id, $fy);
        }

        // ── Step 3: Now fetch settings (guaranteed to exist for all users) ────
        $query = UserLeaveSetting::where('leave_type_id', $elLeaveType->id)
            ->where('financial_year', $fy)
            ->whereNotNull('monthly_accrual')
            ->where('monthly_accrual', '>', 0);

        if ($onlyUserId) {
            $query->where('user_id', $onlyUserId);
        }

        $settings = $query->get();

        if ($settings->isEmpty()) {
            $this->warn("No EL settings with monthly_accrual > 0 found for FY {$fy}. Nothing to process.");
            return;
        }

        $this->info("Processing accrual for {$settings->count()} user(s) in FY {$fy}.");

        $processed = 0;
        $skipped   = 0;

        foreach ($settings as $setting) {
            $userId = $setting->user_id;

            // ── Idempotency check ─────────────────────────────────────────────
            $alreadyDone = UserElAccrualLog::where('user_id', $userId)
                ->where('leave_type_id', $elLeaveType->id)
                ->where('accrual_month', $month)
                ->where('accrual_year', $year)
                ->where('source', 'monthly_cron')
                ->exists();

            if ($alreadyDone) {
                $this->line("  [SKIP] User {$userId} — already processed for {$month}/{$year}");
                $skipped++;
                continue;
            }

            // ── Get or create quota row ───────────────────────────────────────
            $quota = $this->ensureQuota($userId, $elLeaveType->id, $fy);

            $currentBalance = max(0, $quota->total_quota - $quota->used_quota);
            $accrual        = (float) $setting->monthly_accrual;
            $capLimit       = (float) $setting->carry_forward_limit;

            // ── Apply cap ─────────────────────────────────────────────────────
            $actualAdd = $accrual;
            $capApplied = 0;

            if ($capLimit > 0) {
                $room = $capLimit - $currentBalance;
                if ($room <= 0) {
                    // Already at or over cap — skip accrual this month
                    $this->line("  [CAP]  User {$userId} — balance {$currentBalance} already at cap {$capLimit}. No accrual.");
                    $actualAdd  = 0;
                    $capApplied = $accrual; // All of this month's accrual was dropped
                } elseif ($accrual > $room) {
                    $actualAdd  = $room;    // Partial credit up to cap
                    $capApplied = $accrual - $room;
                }
            }

            $balanceBefore = $currentBalance;
            $balanceAfter  = $currentBalance + $actualAdd;

            $this->line(sprintf(
                "  User %d | Accrual: %.2f | Cap: %s | Add: %.2f | Balance: %.2f → %.2f",
                $userId,
                $accrual,
                $capLimit > 0 ? $capLimit : 'none',
                $actualAdd,
                $balanceBefore,
                $balanceAfter
            ));

            if (!$isDryRun) {
                DB::beginTransaction();
                try {
                    // Only increment total_quota — used_quota is unchanged
                    if ($actualAdd > 0) {
                        $quota->increment('total_quota', $actualAdd);
                    }

                    // Always log (even 0-add so we know it ran)
                    UserElAccrualLog::create([
                        'user_id'        => $userId,
                        'leave_type_id'  => $elLeaveType->id,
                        'financial_year' => $fy,
                        'accrual_month'  => $month,
                        'accrual_year'   => $year,
                        'days_added'     => $actualAdd,
                        'balance_before' => $balanceBefore,
                        'balance_after'  => $balanceAfter,
                        'cap_applied'    => $capApplied,
                        'source'         => 'monthly_cron',
                        'notes'          => "Regular monthly accrual for {$month}/{$year}",
                    ]);

                    DB::commit();
                    $processed++;

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("  [ERROR] User {$userId}: " . $e->getMessage());
                }
            } else {
                $processed++;
            }
        }

        $this->info("Monthly Accrual: {$processed} processed, {$skipped} skipped.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FY ROLLOVER (April = new financial year starts)
    // ─────────────────────────────────────────────────────────────────────────
    protected function runFyRollover(
        LeaveType $elLeaveType,
        int $newFyStartYear,
        ?int $onlyUserId,
        bool $isDryRun
    ): void {
        // Previous FY (e.g. if April 2026, previous FY = 2025-26)
        $prevFy   = ($newFyStartYear - 1) . '-' . substr($newFyStartYear, -2);
        $newFy    = $newFyStartYear . '-' . substr($newFyStartYear + 1, -2);

        $this->info("Rollover: {$prevFy} → {$newFy}");

        // Load all user EL settings from the PREVIOUS FY
        $query = UserLeaveSetting::where('leave_type_id', $elLeaveType->id)
            ->where('financial_year', $prevFy)
            ->with('user');

        if ($onlyUserId) {
            $query->where('user_id', $onlyUserId);
        }

        $prevSettings = $query->get();

        if ($prevSettings->isEmpty()) {
            $this->warn("No previous FY ({$prevFy}) EL settings found. Nothing to roll over.");
            return;
        }

        $this->info("Rolling over {$prevSettings->count()} user(s)...");

        foreach ($prevSettings as $prevSetting) {
            $userId = $prevSetting->user_id;

            // ── Check if new FY rollover already done ─────────────────────────
            $alreadyDone = UserElAccrualLog::where('user_id', $userId)
                ->where('leave_type_id', $elLeaveType->id)
                ->where('financial_year', $newFy)
                ->whereIn('source', ['fy_carry_forward', 'fy_reset'])
                ->exists();

            if ($alreadyDone) {
                $this->line("  [SKIP] User {$userId} — FY rollover already done for {$newFy}");
                continue;
            }

            // ── Get previous FY quota ─────────────────────────────────────────
            $prevQuota    = $this->ensureQuota($userId, $elLeaveType->id, $prevFy);
            $leftover     = max(0, $prevQuota->total_quota - $prevQuota->used_quota);
            $capLimit     = (float) $prevSetting->carry_forward_limit;
            $carryForward = (bool)  $prevSetting->carry_forward;

            // ── Determine opening balance for new FY ──────────────────────────
            $newFyOpeningBalance = 0;
            $source              = 'fy_reset';

            if ($carryForward && $leftover > 0) {
                // Cap the carry-forward amount
                $newFyOpeningBalance = ($capLimit > 0)
                    ? min($leftover, $capLimit)
                    : $leftover;

                $source = 'fy_carry_forward';

                $this->line(sprintf(
                    "  [CARRY] User %d | Leftover: %.2f | Cap: %s | Carrying: %.2f",
                    $userId,
                    $leftover,
                    $capLimit > 0 ? $capLimit : 'none',
                    $newFyOpeningBalance
                ));
            } else {
                $this->line(sprintf(
                    "  [RESET] User %d | Leftover: %.2f | carry_forward=false → Reset to 0",
                    $userId,
                    $leftover
                ));
            }

            if (!$isDryRun) {
                DB::beginTransaction();
                try {
                    // 1. Create new FY quota row seeded with opening balance
                    $newQuota = UserLeaveQuota::firstOrCreate(
                        [
                            'user_id'        => $userId,
                            'leave_type_id'  => $elLeaveType->id,
                            'financial_year' => $newFy,
                        ],
                        [
                            'total_quota' => $newFyOpeningBalance,
                            'used_quota'  => 0,
                        ]
                    );

                    // 2. Clone user_leave_settings to new FY
                    //    (so the same accrual rate/carry config continues in new FY)
                    UserLeaveSetting::firstOrCreate(
                        [
                            'user_id'        => $userId,
                            'leave_type_id'  => $elLeaveType->id,
                            'financial_year' => $newFy,
                        ],
                        [
                            'annual_quota'        => $prevSetting->annual_quota,
                            'monthly_accrual'     => $prevSetting->monthly_accrual,
                            'carry_forward'       => $prevSetting->carry_forward,
                            'carry_forward_limit' => $prevSetting->carry_forward_limit,
                        ]
                    );

                    // 3. Log the rollover event
                    UserElAccrualLog::create([
                        'user_id'        => $userId,
                        'leave_type_id'  => $elLeaveType->id,
                        'financial_year' => $newFy,
                        'accrual_month'  => 4,         // April = FY start
                        'accrual_year'   => $newFyStartYear,
                        'days_added'     => $newFyOpeningBalance,
                        'balance_before' => 0,
                        'balance_after'  => $newFyOpeningBalance,
                        'cap_applied'    => max(0, $leftover - $newFyOpeningBalance),
                        'source'         => $source,
                        'notes'          => $source === 'fy_carry_forward'
                            ? "Carry forward from {$prevFy}: {$leftover} days → {$newFyOpeningBalance} days (cap: {$capLimit})"
                            : "FY reset: {$leftover} days forfeited (carry_forward=false)",
                    ]);

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("  [ERROR] User {$userId} FY rollover: " . $e->getMessage());
                }
            }
        }

        $this->info('FY rollover complete.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get financial year string from month + year.
     * April 2026 → "2026-27"
     * January 2026 → "2025-26"
     */
    protected function getFinancialYear(int $month, int $year): string
    {
        if ($month >= 4) {
            return $year . '-' . substr($year + 1, -2);
        }
        return ($year - 1) . '-' . substr($year, -2);
    }

    /**
     * Ensure a quota row exists (create with 0 if missing).
     */
    protected function ensureQuota(int $userId, int $leaveTypeId, string $fy): UserLeaveQuota
    {
        // Check user_leave_settings first — it is the single source of truth.
        // If no setting exists yet, seed one with EL defaults:
        //   opening balance = 5 days, carry_forward = true, limit = 10
        $setting = UserLeaveSetting::firstOrCreate(
            [
                'user_id'        => $userId,
                'leave_type_id'  => $leaveTypeId,
                'financial_year' => $fy,
            ],
            [
                'annual_quota'        => 5.0,   // 5 EL days opening balance
                'monthly_accrual'     => 1.0,   // admin can update per user
                'carry_forward'       => true,  // carry forward ON by default
                'carry_forward_limit' => 10.0,  // max 10 days accumulation
            ]
        );

        return UserLeaveQuota::firstOrCreate(
            [
                'user_id'        => $userId,
                'leave_type_id'  => $leaveTypeId,
                'financial_year' => $fy,
            ],
            [
                'total_quota' => (float) ($setting->annual_quota ?? 5.0),
                'used_quota'  => 0,
            ]
        );
    }
}