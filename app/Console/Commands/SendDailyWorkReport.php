<?php

namespace App\Console\Commands;

use App\DailyReportEmailLog;
use App\User;
use App\UserDvr;
use App\UserScheduler;
use App\WorkNote;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

/**
 * ─────────────────────────────────────────────────────────────────────
 *  DAILY WORK REPORT — EMAIL WITH PDF ATTACHMENT
 * ─────────────────────────────────────────────────────────────────────
 *  Runs every few minutes between 09:00–10:00 (see Kernel).
 *  Each run:
 *    1. Deletes tracking rows older than 5 days (no separate command).
 *    2. Seeds one pending tracking row per eligible user for yesterday.
 *    3. Claims 2 pending rows (row-locked, so parallel runs are safe)
 *       and for each: builds the PDF (mPDF) + sends via EmailService.
 *
 *  PDF contents (in order):
 *    1. Yesterday's Scheduled Tasks   (user_schedulers)
 *    2. Customer Visits — card view   (user_dvrs)  → now shows CITY + LAST VISIT
 *    3. Other Developments            (work_notes)
 *    4. Today's Upcoming Tasks        (user_schedulers)
 */
class SendDailyWorkReport extends Command
{
    protected $signature = 'report:daily-work-email
                        {--limit=2 : Records processed per run}
                        {--date=   : Simulate "today" (Y-m-d) — report will be for the previous day}
                        {--user=   : Process only this user id (testing)}';

    protected $description = 'Email each employee their previous-day work report (PDF) with today\'s upcoming tasks';

    const RETENTION_DAYS = 5;
    const MARKETING_DEPARTMENT_ID = 2;

    public function handle()
    {
        // --date simulates the day the command RUNS ("today").
        // The report is always for the PREVIOUS day.
        $runDay = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::today();

        $reportDate = $runDay->copy()->subDay();   // yesterday → report data
        $today      = $runDay->copy();             // upcoming tasks day

        $limit = max(1, (int) $this->option('limit'));

        // ── 1. Housekeeping: purge tracking rows older than 5 days ──
        DB::table('daily_report_email_logs')
            ->where('report_date', '<', Carbon::today()->subDays(self::RETENTION_DAYS)->toDateString())
            ->delete();

        // ── 2. Seed pending rows for this report date (idempotent) ──
        $this->seedTrackingRows($reportDate);

        // ── 3. Claim N pending rows safely ──
        $logs = $this->claimPendingLogs($reportDate, $limit);

        if ($logs->isEmpty()) {
            $this->info('Nothing pending for ' . $reportDate->toDateString() . '. All done.');
            return;
        }

        foreach ($logs as $log) {
            $this->processLog($log, $reportDate, $today);
        }

        $this->info('Processed ' . $logs->count() . ' record(s).');
    }

    /* ═══════════════════════════════════════════════
     *  SEED + CLAIM
     * ═══════════════════════════════════════════════ */

    private function seedTrackingRows(Carbon $reportDate)
    {
        // Marketing department users only (user_departments.department_id = 2)
        $marketingUserIds = \DB::table('user_departments')
            ->where('department_id', self::MARKETING_DEPARTMENT_ID)
            ->pluck('user_id')
            ->toArray();

        if (empty($marketingUserIds)) {
            return; // nobody in the department — nothing to seed
        }

        $userQuery = User::where('status', 1)
            ->where('email', '!=', '')
            ->whereNotNull('email')
            ->where(function ($query) use ($marketingUserIds) {
                // If specific user option is passed, run ONLY for that user
                if ($this->option('user')) {
                    $query->where('id', $this->option('user'));
                } else {
                    // Otherwise, exclude test users and restrict to marketing users
                    $query->whereNotIn('id', [16, 17, 9, 25])
                          ->whereIn('id', $marketingUserIds);
                }
            });

        $now = now();
        $rows = $userQuery->pluck('id')->map(function ($id) use ($reportDate, $now) {
            return [
                'user_id'     => $id,
                'report_date' => $reportDate->toDateString(),
                'status'      => 'pending',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        })->toArray();

        if (!empty($rows)) {
            // insertOrIgnore is not in 5.8 — unique key + insert ignore via raw
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
                    'INSERT IGNORE INTO daily_report_email_logs
                     (user_id, report_date, status, created_at, updated_at)
                     VALUES ' . implode(',', $values),
                    $bindings
                );
            }
        }
    }

    /**
     * Claim rows inside a transaction with a row lock so two overlapping
     * cron runs can never grab the same user.
     */
    private function claimPendingLogs(Carbon $reportDate, int $limit)
    {
        return DB::transaction(function () use ($reportDate, $limit) {
            $logs = DailyReportEmailLog::where('report_date', $reportDate->toDateString())
                ->where('status', 'pending')
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

    private function processLog(DailyReportEmailLog $log, Carbon $reportDate, Carbon $today)
    {
        $pdfPath = null;

        try {
            $user = User::find($log->user_id);
            if (!$user || empty($user->email)) {
                $log->update(['status' => 'skipped', 'error_message' => 'User missing or has no email']);
                return;
            }

            $data = $this->gatherReportData($user, $reportDate, $today);

            // Did the employee actually WORK on the report date?
            // Used only for the email wording (hasActivity) — the report is
            // ALWAYS sent for every day, including Sundays/blank days.
            $workedYesterday = ($data['counts']['yesterday_tasks'] > 0
                || $data['counts']['visits'] > 0
                || $data['counts']['work_notes'] > 0);

            // ── Build PDF ──
            $pdfPath = $this->generatePdf($user, $data, $reportDate);

            // ── CC → reporting managers (from DB hierarchy) ──
            $ccEmails  = [];
            $reporting = \App\User::getReportingUsers($user->id);
            $reporting =  json_decode(json_encode($reporting),true);
            if (!empty($reporting['report_to_users'])) {
                foreach ($reporting['report_to_users'] as $mgr) {
                    $email = is_array($mgr) ? ($mgr['email'] ?? null) : ($mgr->email ?? null);
                    if (!empty($email)) {
                        $ccEmails[] = $email;
                    }
                }
            }
            if($user->id == "16" || $user->id == "17" || $user->id == "9"){
                // ⚠️ TESTING ONLY — remove this line to send to real employees
                $user->email = "mkanum786@gmail.com";
                // ⚠️ TESTING ONLY — real managers must not be CC'd during testing
                $ccEmails = ['bhupigreenwave@yopmail.com'];   // or ['mkanum786@gmail.com'] to test the CC path itself 
            }else{
                /*// ⚠️ TESTING ONLY — remove this line to send to real employees
                $user->email = "mkanum786@gmail.com";
                // ⚠️ TESTING ONLY — real managers must not be CC'd during testing
                $ccEmails = ['mkanum786@gmail.com'];   // or ['mkanum786@gmail.com'] to test the CC path itself*/
            }

            EmailService::send('daily_work_report', [
                'employee'          => ['name' => $user->name, 'email' => $user->email],
                'reportDateDisplay' => $reportDate->format('d M Y'),
                'reportDayName'     => $reportDate->format('l'),
                'todayDisplay'      => $today->format('d M Y'),
                'counts'            => $data['counts'],
                'hasActivity'       => $workedYesterday,
                '_cc'               => $ccEmails,
                '_attachments'      => [$pdfPath],
            ], $user->email);

            $log->update([
                'status'   => 'sent',
                'pdf_file' => basename($pdfPath),
                'sent_at'  => now(),
                'error_message' => null,
            ]);

            $this->info("  ✓ {$user->name}: report sent ({$data['counts']['visits']} visits)");

        } catch (\Exception $e) {
            Log::error('DailyWorkReport failed for user ' . $log->user_id, ['error' => $e->getMessage()]);
            $log->update([
                'status'        => 'failed',
                'error_message' => substr($e->getMessage(), 0, 2000),
            ]);
            $this->error("  ✗ user #{$log->user_id}: " . $e->getMessage());
        } finally {
            // temp PDF no longer needed once mail is queued/sent
            if ($pdfPath && File::exists($pdfPath)) {
                File::delete($pdfPath);
            }
        }
    }

    /* ═══════════════════════════════════════════════
     *  DATA GATHERING (blade stays render-only)
     * ═══════════════════════════════════════════════ */

    private function gatherReportData(User $user, Carbon $reportDate, Carbon $today): array
    {
        $yDate = $reportDate->toDateString();
        $tDate = $today->toDateString();

        // 1 + 4. Schedulers (yesterday + today)
        $schedulers = UserScheduler::with([
                'dealer:id,business_name',
                'customer:id,name',
                'customer_register_request:id,name',
                'next_scheduler',
            ])
            ->where('user_id', $user->id)
            ->whereIn('scheduler_date', [$yDate, $tDate])
            ->orderBy('scheduler_date')
            ->orderBy('scheduler_time')
            ->get();

        // work_notes referenced by schedulers → activity_mode sub-label
        $noteIds  = $schedulers->pluck('work_note_id')->filter()->unique()->values();
        $noteMap  = $noteIds->isEmpty()
            ? collect()
            : WorkNote::whereIn('id', $noteIds)->get(['id', 'activity_mode'])->keyBy('id');

        // user_dvrs referenced by schedulers → have_you_met (for "No Meeting" tag)
        $dvrIds   = $schedulers->pluck('user_dvr_id')->filter()->unique()->values();
        $dvrMetMap = $dvrIds->isEmpty()
            ? collect()
            : UserDvr::whereIn('id', $dvrIds)->get(['id', 'have_you_met'])->keyBy('id');

        $byDate = $schedulers->groupBy(fn($s) => Carbon::parse($s->scheduler_date)->toDateString());

        $yesterdayTasks = collect($byDate->get($yDate, []))
            ->map(fn($s) => $this->buildSchedulerRow($s, $noteMap, $dvrMetMap))->values()->toArray();

        $todayTasks = collect($byDate->get($tDate, []))
            ->map(fn($s) => $this->buildSchedulerRow($s, $noteMap, $dvrMetMap))->values()->toArray();

        // 2. Customer visits (DVRs) — card view
        //    NOTE: register-request eager load now includes `cities` for the
        //    city label; customer city comes from customer_cities (see resolveCity).
        $dvrs = UserDvr::with([
                'customer:id,name',
                'customer_register_request:id,name,cities',
                'customer_contact_info:id,name,designation,mobile_number',
                'customerContacts.customerContact:id,name,designation,mobile_number',
                'products.productinfo:id,product_name',
                'trials.attachments',
                'trials.products.productinfo:id,product_name',
            ])
            ->where('user_id', $user->id)
            ->whereDate('dvr_date', $yDate)
            ->orderBy('start_time')
            ->get();

        $visits = $dvrs->map(fn($d) => $this->buildDvrCard($d, $user, $reportDate))->toArray();

        // 3. Other developments (work notes)
        $workNotes = WorkNote::with([
                'dealer:id,business_name',
                'customer:id,name',
                'customerRegisterRequest:id,name',
            ])
            ->where('user_id', $user->id)
            ->whereDate('date', $yDate)
            ->orderBy('id')
            ->get()
            ->map(fn($n) => $this->buildWorkNoteRow($n))
            ->toArray();

        return [
            'yesterdayTasks' => $yesterdayTasks,
            'visits'         => $visits,
            'workNotes'      => $workNotes,
            'todayTasks'     => $todayTasks,
            'counts'         => [
                'yesterday_tasks' => count($yesterdayTasks),
                'visits'          => count($visits),
                'work_notes'      => count($workNotes),
                'today_tasks'     => count($todayTasks),
            ],
        ];
    }

    private function buildSchedulerRow(UserScheduler $s, $noteMap, $dvrMetMap = null): array
    {
        // Related-to display name
        $relatedName = $s->other_customer_name
            ?: optional($s->dealer)->business_name
            ?: optional($s->customer)->name
            ?: optional($s->customer_register_request)->name
            ?: '—';

        // Status sub-label per client spec:
        //   user_dvr_id present   → "Visit"  (+ "(No Meeting)" when have_you_met = 0)
        //   work_note_id present  → work_notes.activity_mode
        $subLabel = null;
        if (!empty($s->user_dvr_id)) {
            $subLabel = 'Visit';
            // If the linked DVR recorded no meeting (have_you_met = 0), flag it.
            if ($dvrMetMap && $dvrMetMap->has($s->user_dvr_id)) {
                $met = $dvrMetMap->get($s->user_dvr_id)->have_you_met;
                if ((int) $met === 0) {
                    $subLabel = 'Visit (No Meeting)';
                }
            }
        } elseif (!empty($s->work_note_id) && $noteMap->has($s->work_note_id)) {
            $mode = $noteMap->get($s->work_note_id)->activity_mode;
            // stored uppercase (e.g. "INFORMATION") → "Information"
            $subLabel = $mode ? ucfirst(strtolower($mode)) : null;
        }

        $status = $s->status ?: 'Open';

        // Rescheduled → show the NEW date & time from the next scheduler
        $rescheduledTo = null;
        if ($status === 'Rescheduled' && !empty($s->next_scheduler_id) && $s->next_scheduler) {
            $ns    = $s->next_scheduler;
            $parts = [];
            if ($ns->scheduler_date) $parts[] = Carbon::parse($ns->scheduler_date)->format('d M Y');
            if ($ns->scheduler_time) $parts[] = Carbon::parse($ns->scheduler_time)->format('h:i A');
            $rescheduledTo = implode(', ', $parts) ?: null;
        }

        $statusColors = [
            'Done'        => '#16a34a',
            'Completed'   => '#16a34a',
            'Pending'     => '#2563eb',
            'Open'        => '#2563eb',
            'Rescheduled' => '#d97706',
            'Closed'      => '#334155',
            'Cancelled'   => '#dc2626',
        ];

        return [
            'time'         => $s->scheduler_time ? Carbon::parse($s->scheduler_time)->format('h:i A') : '—',
            'related_to'   => ucfirst(str_replace('_', ' ', (string) $s->related_to)),
            'name'         => $relatedName,
            'subject'      => $s->subject ?: '—',
            'description'  => $s->description,
            'status'       => $status,
            'status_color' => $statusColors[$status] ?? '#475569',
            'sub_label'    => $subLabel,
            'rescheduled_to' => $rescheduledTo,
        ];
    }

    /**
     * Called from gatherReportData like:
     *   $visits = $dvrs->map(fn($d) => $this->buildDvrCard($d, $user, $reportDate))->toArray();
     */
    private function buildDvrCard(UserDvr $dvr, User $user, Carbon $reportDate): array
    {
        $customerName = $dvr->customer
            ? $dvr->customer->name
            : optional($dvr->customer_register_request)->name;

        // ── CITY instead of the old "customer_type" label ──
        //   Registered customer  → latest row in customer_cities (by id)
        //   Register request     → cities column on the request itself
        $customerCity = $this->resolveCity($dvr);

        $checkIn  = $dvr->start_time ? Carbon::parse($dvr->start_time)->format('H:i') : null;
        $checkOut = $dvr->end_time   ? Carbon::parse($dvr->end_time)->format('H:i')   : null;
        $duration = null;
        if ($dvr->start_time && $dvr->end_time) {
            $d = Carbon::parse($dvr->start_time)->diff(Carbon::parse($dvr->end_time));
            $duration = $d->h . 'h ' . $d->i . 'm';
        }

        // Contacts met
        $contacts = $dvr->customerContacts
            ->filter(fn($cc) => $cc->customerContact !== null)
            ->map(fn($cc) => [
                'name'        => $cc->customerContact->name,
                'designation' => $cc->customerContact->designation,
                'mobile'      => $cc->customerContact->mobile_number,
            ])->values()->toArray();

        if (empty($contacts) && $dvr->customer_contact_info) {
            $c = $dvr->customer_contact_info;
            $contacts = [[
                'name'        => $c->name,
                'designation' => $c->designation,
                'mobile'      => $c->mobile_number,
            ]];
        }

        $purposes = $dvr->purpose_of_visit
            ? array_values(array_filter(array_map('trim', explode(',,,', $dvr->purpose_of_visit))))
            : [];

        $products = $dvr->products->map(fn($p) => optional($p->productinfo)->product_name)
            ->filter()->values()->toArray();

        // Trials — same status logic as the admin DVR listing
        $trials = $dvr->trials->map(function ($trial) {
            if (!$trial->trial_done) {
                $status = 'Trial Not Done';
                $color  = '#dc2626';
            } else {
                $hasRpt = $trial->attachments->where('type', 'trial_report')->count() > 0
                       || !is_null($trial->trial_report_id ?? null);
                $status = $hasRpt ? 'Report Attached' : 'Trial Report Pending';
                $color  = $hasRpt ? '#16a34a' : '#dc2626';
            }
            return [
                'status'       => $status,
                'status_color' => $color,
                'done'         => (bool) $trial->trial_done,
                'type'         => $trial->trial_type,
                'objective'    => $trial->objective,
                'remarks'      => $trial->remarks,
                'jointly'      => (bool) $trial->is_jointly,
                'team_member'  => $trial->other_team_member_name,
                'products'     => $trial->products
                    ->map(fn($tp) => optional($tp->productinfo)->product_name)
                    ->filter()->values()->toArray(),
            ];
        })->values()->toArray();

        // Status attributes — rendered as a tick/cross strip (product-pricing style)
        $isReal    = ($dvr->visit_recorded === 'On Site');
        $met       = (bool) $dvr->have_you_met;
        $submitted = (bool) $dvr->is_submitted;

        $statuses = [
            ['label' => 'Visit Type',   'value' => $dvr->visit_type ?: 'Official',                    'ok' => true],
            ['label' => 'Entry',        'value' => $isReal ? 'Real Time' : 'Post Visit',              'ok' => $isReal],
            ['label' => 'Site',         'value' => $dvr->site_type ?: '—',                            'ok' => ($dvr->site_type === 'On Site')],
            ['label' => 'Customer Met', 'value' => $met ? 'Yes' : 'No',                               'ok' => $met],
            ['label' => 'Visit Detail', 'value' => $submitted ? 'Added' : 'Pending',                  'ok' => $submitted],
        ];

        // ── LAST VISIT — most recent earlier DVR to the SAME party ──
        $lastVisit = $this->buildLastVisit($dvr, $user, $reportDate);

        // ── Follow-up action date/time (shown alongside Next Plan) ──
        //    Only when the DVR flags a further action AND has a date.
        $nextAction = null;
        if (!empty($dvr->further_action_required)) {
            $parts = [];
            if (!empty($dvr->action_date)) {
                $parts[] = Carbon::parse($dvr->action_date)->format('d M Y');
            }
            if (!empty($dvr->action_time)) {
                $parts[] = Carbon::parse($dvr->action_time)->format('h:i A');
            }
            $nextAction = !empty($parts) ? implode(', ', $parts) : null;
        }

        return [
            'customer_name' => $customerName ?: 'N/A',
            'customer_city' => $customerCity,          // replaces customer_type
            'check_in'      => $checkIn,
            'check_out'     => $checkOut,
            'duration'      => $duration,
            'contacts'      => $contacts,
            'purposes'      => $purposes,
            'products'      => $products,
            'trials'        => $trials,
            'visit_detail'  => $dvr->visit_detail,
            'remarks'       => $dvr->remarks,
            'next_plan'     => $dvr->next_plan,
            'next_action'   => $nextAction,            // null when no follow-up date
            'other_purpose' => $dvr->other_purpose,
            'statuses'      => $statuses,
            'last_visit'    => $lastVisit,             // null when none
        ];
    }

    /**
     * Resolve the display city for a DVR's party.
     *  - Registered customer → newest customer_cities.city_name (by id desc)
     *  - Register request    → customer_register_requests.cities column
     */
    private function resolveCity(UserDvr $dvr): ?string
    {
        if ($dvr->customer_id) {
            $city = DB::table('customer_cities')
                ->where('customer_id', $dvr->customer_id)
                ->orderByDesc('id')
                ->value('city_name');
            if (!empty($city)) {
                return $this->titleCity($city);
            }
        }

        if ($dvr->customer_register_request) {
            $city = $dvr->customer_register_request->cities;
            if (!empty($city)) {
                // "cities" may be a comma list — show the first
                $first = trim(explode(',', $city)[0]);
                return $this->titleCity($first ?: $city);
            }
        }

        return null;
    }

    private function titleCity(string $city): string
    {
        // stored uppercase (e.g. "LUDHIANA") → nicer "Ludhiana"
        return ucwords(strtolower(trim($city)));
    }

    /**
     * Find the last visit (before the report date) to the same customer or
     * register-request, by the same user, and build a compact summary.
     */
    private function buildLastVisit(UserDvr $dvr, User $user, Carbon $reportDate): ?array
    {
        $q = UserDvr::with([
                'customerContacts.customerContact:id,name,designation,mobile_number',
                'customer_contact_info:id,name,designation,mobile_number',
            ])
            ->where('user_id', $user->id)
            ->whereDate('dvr_date', '<', $reportDate->toDateString())
            ->where('id', '!=', $dvr->id);

        if ($dvr->customer_id) {
            $q->where('customer_id', $dvr->customer_id);
        } elseif ($dvr->customer_register_request_id) {
            $q->where('customer_register_request_id', $dvr->customer_register_request_id);
        } else {
            return null; // nothing to match on
        }

        $prev = $q->orderByDesc('dvr_date')
                  ->orderByDesc('start_time')
                  ->orderByDesc('id')
                  ->first();

        if (!$prev) {
            return null;
        }

        $date    = $prev->dvr_date ? Carbon::parse($prev->dvr_date) : null;
        $daysAgo = $date ? $date->diffInDays($reportDate) : null;

        $purposes = $prev->purpose_of_visit
            ? array_values(array_filter(array_map('trim', explode(',,,', $prev->purpose_of_visit))))
            : [];

        // Who was met last time (first contact is enough for a summary line)
        $metName = null;
        $firstContact = $prev->customerContacts
            ->first(fn($cc) => $cc->customerContact !== null);
        if ($firstContact) {
            $metName = $firstContact->customerContact->name;
        } elseif ($prev->customer_contact_info) {
            $metName = $prev->customer_contact_info->name;
        }

        return [
            'date'      => $date ? $date->format('d M Y') : '—',
            'day'       => $date ? $date->format('l') : null,
            'days_ago'  => $daysAgo,
            'met'       => (bool) $prev->have_you_met,
            'met_name'  => $metName,
            'purposes'  => array_slice($purposes, 0, 4),   // keep the line short
            'summary'   => $prev->visit_detail ?: $prev->remarks ?: null,
            'next_plan' => $prev->next_plan ?: null,
        ];
    }

    private function buildDvrCardLegacyType(UserDvr $dvr): string
    {
        // (kept intentionally unused — old customer_type logic removed)
        return $dvr->customer
            ? 'Registered Customer'
            : ($dvr->customer_register_request ? 'Registration Request' : 'Other');
    }

    private function buildWorkNoteRow(WorkNote $note): array
    {
        $relatedName = optional($note->dealer)->business_name
            ?: optional($note->customer)->name
            ?: optional($note->customerRegisterRequest)->name
            ?: '—';

        $action = null;
        if ($note->further_action_required) {
            $action = trim(implode(' ', array_filter([
                $note->action_date ? Carbon::parse($note->action_date)->format('d M Y') : null,
                $note->action_time ? Carbon::parse($note->action_time)->format('h:i A') : null,
            ])));
            if ($note->action_remarks) {
                $action = ($action ? $action . ' — ' : '') . $note->action_remarks;
            }
        }

        return [
            'subject'       => $note->subject ?: '—',
            'activity_mode' => $note->activity_mode,
            'related_to'    => ucfirst(str_replace('_', ' ', (string) $note->related_to)),
            'name'          => $relatedName,
            'description'   => $note->description,
            'key_take_away' => $note->key_take_away,
            'action'        => $action,
        ];
    }

    /* ═══════════════════════════════════════════════
     *  PDF GENERATION (mPDF — product-pricing theme)
     * ═══════════════════════════════════════════════ */

    private function generatePdf(User $user, array $data, Carbon $reportDate): string
    {
        $html = view('employee_reports.daily_work_pdf', [
            'employee'    => $user,
            'reportDate'  => $reportDate->format('d M Y'),
            'reportDay'   => $reportDate->format('l'),
            'todayDate'   => $reportDate->copy()->addDay()->format('d M Y'),
            'generatedAt' => now()->format('d M Y, h:i A'),
            'data'        => $data,
        ])->render();

        $dir = storage_path('app/daily-reports');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_top'        => 12,
            'margin_bottom'     => 14,
            'margin_left'       => 10,
            'margin_right'      => 10,
            'default_font'      => 'dejavusans',
            'default_font_size' => 9,
            'tempDir'           => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->SetTitle('Daily Work Report — ' . $user->name);
        $mpdf->SetAuthor('Greenwave');

        // Repeating footer on every page — page numbers included
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="border-top:1px solid #cbd5e1; font-size:7px; color:#64748b;">
                <tr>
                    <td style="font-weight:bold; color:#334155;">Greenwave &bull; Daily Work Report &mdash; ' . e($user->name) . '</td>
                    <td align="center">Confidential &mdash; Internal Use Only</td>
                    <td align="right">Page {PAGENO} of {nbpg} &nbsp;&bull;&nbsp; ' . now()->format('d M Y') . '</td>
                </tr>
            </table>'
        );

        $mpdf->WriteHTML($html);

        $filename = 'Daily_Work_Report_'
            . preg_replace('/[^A-Za-z0-9]+/', '_', $user->name)
            . '_' . $reportDate->format('Y-m-d') . '.pdf';

        $path = $dir . '/' . $filename;
        $mpdf->Output($path, 'F');

        return $path;
    }
}