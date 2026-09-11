<?php

namespace App\Console\Commands;

use App\MonthlyVisitAnalysisLog;
use App\User;
use App\Services\EmailService;
use App\Services\Report\MonthlyVisitAnalysisService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * ─────────────────────────────────────────────────────────────────────
 *  MONTHLY VISIT ANALYSIS — EMAIL WITH PDF ATTACHMENT
 * ─────────────────────────────────────────────────────────────────────
 *  Runs every few minutes between 06:00–07:30 on the 1ST of every month
 *  (see Kernel). On the 1st of e.g. September it reports on August.
 *
 *  Each run:
 *    1. Deletes tracking rows older than RETENTION_DAYS.
 *    2. Seeds one pending tracking row per eligible user for LAST month.
 *    3. Claims a few pending rows (row-locked, parallel-safe) and for each:
 *       builds the PDF (mPDF) + sends via EmailService.
 *
 *  Report window : previous calendar month (1st → last day).
 *
 *  The eligible-employee list, the data gathering and the PDF rendering all
 *  live in App\Services\Report\MonthlyVisitAnalysisService, shared
 *  with the admin-panel screen (Admin\MonthlyVisitAnalysisController)
 *  so both produce an identical report.
 */
class SendMonthlyVisitAnalysis extends Command
{
    protected $signature = 'report:monthly-visit-analysis
                        {--limit=2 : Records processed per run}
                        {--date=   : Simulate "today" (Y-m-d) — report covers the previous calendar month}
                        {--user=   : Process only this user id (testing)}';

    protected $description = "Email each employee their previous-month Visit Analysis (PDF) — visit counts, customer coverage and trial status";

    const RETENTION_DAYS = 400;

    // ⚠️ TESTING ONLY — every report is sent to this address instead of the
    // employee's real inbox. Set to null to send to real employees.
    const TEST_EMAIL_OVERRIDE = 'mkanum786@gmail.com';

    /** @var MonthlyVisitAnalysisService */
    protected $reportService;

    public function __construct(MonthlyVisitAnalysisService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    public function handle()
    {
        // --date simulates the day the command RUNS.
        $runDay = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::today();

        // Previous calendar month: 1st .. last day
        $monthStart = $runDay->copy()->subMonthNoOverflow()->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $limit = max(1, (int) $this->option('limit'));

        // ── 1. Housekeeping: purge old tracking rows ──
        DB::table('monthly_visit_analysis_logs')
            ->where('report_date', '<', Carbon::today()->subDays(self::RETENTION_DAYS)->toDateString())
            ->delete();

        // ── 2. Seed pending rows for this month (idempotent; report_date = month start) ──
        $this->seedTrackingRows($monthStart);

        // ── 3. Claim N pending rows safely ──
        $logs = $this->claimPendingLogs($monthStart, $limit);

        if ($logs->isEmpty()) {
            $this->info('Nothing pending for month of ' . $monthStart->format('F Y') . '. All done.');
            return;
        }

        foreach ($logs as $log) {
            $this->processLog($log, $monthStart, $monthEnd);
        }

        $this->info('Processed ' . $logs->count() . ' record(s).');
    }

    /* ═══════════════════════════════════════════════
     *  SEED + CLAIM  (same pattern as the daily/weekly report)
     * ═══════════════════════════════════════════════ */

    private function seedTrackingRows(Carbon $monthStart)
    {
        // --user targets one specific id for testing; otherwise the shared
        // eligible list (marketing, active, minus excluded ids) is used.
        if ($this->option('user')) {
            $userIds = User::where('status', 1)
                ->where('email', '!=', '')
                ->whereNotNull('email')
                ->where('id', $this->option('user'))
                ->pluck('id');
        } else {
            $userIds = MonthlyVisitAnalysisService::eligibleEmployees()->pluck('id');
        }

        $now  = now();
        $rows = $userIds->map(function ($id) use ($monthStart, $now) {
            return [
                'user_id'     => $id,
                'report_date' => $monthStart->toDateString(),   // month-start = identity of the month
                'status'      => 'pending',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        })->toArray();

        if (!empty($rows)) {
            foreach (array_chunk($rows, 100) as $chunk) {
                $values   = [];
                $bindings = [];
                foreach ($chunk as $r) {
                    $values[]   = '(?,?,?,?,?)';
                    $bindings[] = $r['user_id'];
                    $bindings[] = $r['report_date'];
                    $bindings[] = $r['status'];
                    $bindings[] = $r['created_at'];
                    $bindings[] = $r['updated_at'];
                }
                DB::insert(
                    'INSERT IGNORE INTO monthly_visit_analysis_logs
                     (user_id, report_date, status, created_at, updated_at)
                     VALUES ' . implode(',', $values),
                    $bindings
                );
            }
        }
    }

    private function claimPendingLogs(Carbon $monthStart, int $limit)
    {
        $onlyUser = $this->option('user');

        return DB::transaction(function () use ($monthStart, $limit, $onlyUser) {
            $logs = MonthlyVisitAnalysisLog::where('report_date', $monthStart->toDateString())
                ->where(function ($q) {
                    $q->where('status', 'pending')
                      ->orWhere(function ($q2) {
                          $q2->where('status', 'failed')->where('attempts', '<', 3);
                      })
                      ->orWhere(function ($q3) {
                          $q3->where('status', 'processing')
                             ->where('updated_at', '<', now()->subHour());
                      });
                })
                ->when($onlyUser, fn($q) => $q->where('user_id', $onlyUser))
                ->orderBy('id')
                ->limit($limit)
                ->lockForUpdate()
                ->get();

            foreach ($logs as $log) {
                $log->update([
                    'status'   => 'processing',
                    'attempts' => $log->attempts + 1,
                ]);
            }

            return $logs;
        });
    }

    /* ═══════════════════════════════════════════════
     *  PROCESS ONE USER
     * ═══════════════════════════════════════════════ */

    private function processLog(MonthlyVisitAnalysisLog $log, Carbon $monthStart, Carbon $monthEnd)
    {
        $pdfPath = null;

        try {
            $user = User::find($log->user_id);
            if (!$user || empty($user->email)) {
                $log->update(['status' => 'skipped', 'error_message' => 'User missing or has no email']);
                return;
            }

            $data = $this->reportService->gather($user, $monthStart, $monthEnd);

            $hasActivity = $data['overall']['total_visits'] > 0;

            // ── Build PDF ──
            $pdfPath = $this->reportService->writePdf($user, $data, $monthStart, $monthEnd);

            // ── CC → reporting managers (from DB hierarchy) ──
            $ccEmails = [];
            $sendTo   = $user->email;

            if (self::TEST_EMAIL_OVERRIDE) {
                // ⚠️ TESTING ONLY — remove this branch to send to real employees
                $sendTo   = self::TEST_EMAIL_OVERRIDE;
                $ccEmails = [];
            } else {
                $reporting = User::getReportingUsers($user->id);
                $reporting = json_decode(json_encode($reporting), true);
                if (!empty($reporting['report_to_users'])) {
                    foreach ($reporting['report_to_users'] as $mgr) {
                        $email = is_array($mgr) ? ($mgr['email'] ?? null) : ($mgr->email ?? null);
                        if (!empty($email)) {
                            $ccEmails[] = $email;
                        }
                    }
                }
            }

            $monthLabel = $monthStart->format('F Y');

            EmailService::send('monthly_visit_analysis', [
                'employee'      => ['name' => $user->name, 'email' => $user->email],
                'employee_name' => $user->name,
                'monthLabel'    => $monthLabel,
                'monthStartDisplay' => $monthStart->format('d M Y'),
                'monthEndDisplay'   => $monthEnd->format('d M Y'),
                'overall'       => $data['overall'],
                'hasActivity'   => $hasActivity,
                '_cc'           => $ccEmails,
                '_attachments'  => [$pdfPath],
            ], $sendTo);

            $log->update([
                'status'   => 'sent',
                'pdf_file' => basename($pdfPath),
                'sent_at'  => now(),
                'error_message' => null,
            ]);

            $this->info("  ✓ {$user->name}: monthly visit analysis sent ({$data['overall']['total_visits']} visits)");

        } catch (\Exception $e) {
            Log::error('MonthlyVisitAnalysis failed for user ' . $log->user_id, ['error' => $e->getMessage()]);
            $log->update([
                'status'        => 'failed',
                'error_message' => substr($e->getMessage(), 0, 2000),
            ]);
            $this->error("  ✗ user #{$log->user_id}: " . $e->getMessage());
        } finally {
            if ($pdfPath && File::exists($pdfPath)) {
                File::delete($pdfPath);
            }
        }
    }
}
