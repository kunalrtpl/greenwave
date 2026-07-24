<?php

namespace App\Console\Commands;

use App\WeeklyReportEmailLog;
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
 *  WEEKLY WORK REPORT — EMAIL WITH PDF ATTACHMENT
 * ─────────────────────────────────────────────────────────────────────
 *  Runs every few minutes between 20:30–21:30 on MONDAYS (see Kernel).
 *  Each run:
 *    1. Deletes tracking rows older than 35 days.
 *    2. Seeds one pending tracking row per eligible user for LAST week.
 *    3. Claims 2 pending rows (row-locked, parallel-safe) and for each:
 *       builds the PDF (mPDF) + sends via EmailService.
 *
 *  Report window : previous week, Monday → Sunday.
 *  Upcoming      : current week, Monday → Sunday.
 *
 *  PDF contents:
 *    - Weekly KPI totals
 *    - Day-by-day breakdown (Mon..Sun): tasks, visit sheets, work notes
 *    - This week's upcoming tasks
 */
class SendWeeklyWorkReport extends Command
{
    protected $signature = 'report:weekly-work-email
                        {--limit=2 : Records processed per run}
                        {--date=   : Simulate "today" (Y-m-d) — report covers the previous Mon–Sun week}
                        {--user=   : Process only this user id (testing)}';

    protected $description = 'Email each employee their previous-week work report (PDF) with this week\'s upcoming tasks';

    const RETENTION_DAYS = 35;
    const MARKETING_DEPARTMENT_ID = 2;

    public function handle()
    {
        // --date simulates the day the command RUNS.
        $runDay = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::today();

        // Previous week: Monday..Sunday
        $weekStart = $runDay->copy()->startOfWeek(Carbon::MONDAY)->subWeek();
        $weekEnd   = $weekStart->copy()->addDays(6);

        // Current (upcoming) week: Monday..Sunday
        $upWeekStart = $runDay->copy()->startOfWeek(Carbon::MONDAY);
        $upWeekEnd   = $upWeekStart->copy()->addDays(6);

        $limit = max(1, (int) $this->option('limit'));

        // ── 1. Housekeeping: purge tracking rows older than 35 days ──
        DB::table('weekly_report_email_logs')
            ->where('report_date', '<', Carbon::today()->subDays(self::RETENTION_DAYS)->toDateString())
            ->delete();

        // ── 2. Seed pending rows for this week (idempotent; report_date = week start) ──
        $this->seedTrackingRows($weekStart);

        // ── 3. Claim N pending rows safely ──
        $logs = $this->claimPendingLogs($weekStart, $limit);

        if ($logs->isEmpty()) {
            $this->info('Nothing pending for week of ' . $weekStart->toDateString() . '. All done.');
            return;
        }

        foreach ($logs as $log) {
            $this->processLog($log, $weekStart, $weekEnd, $upWeekStart, $upWeekEnd);
        }

        $this->info('Processed ' . $logs->count() . ' record(s).');
    }

    /* ═══════════════════════════════════════════════
     *  SEED + CLAIM  (same pattern as the daily report)
     * ═══════════════════════════════════════════════ */

    private function seedTrackingRows(Carbon $weekStart)
    {
        $marketingUserIds = \DB::table('user_departments')
            ->where('department_id', self::MARKETING_DEPARTMENT_ID)
            ->pluck('user_id')
            ->toArray();

        if (empty($marketingUserIds)) {
            return;
        }

        $userQuery = User::where('status', 1)
            ->where('email', '!=', '')
            ->whereNotNull('email')
            ->where(function ($query) use ($marketingUserIds) {
                if ($this->option('user')) {
                    $query->where('id', $this->option('user'));
                } else {
                    $query->whereNotIn('id', [16, 17, 9, 25])
                          ->whereIn('id', $marketingUserIds);
                }
            });

        $now = now();
        $rows = $userQuery->pluck('id')->map(function ($id) use ($weekStart, $now) {
            return [
                'user_id'     => $id,
                'report_date' => $weekStart->toDateString(),   // week-start = identity of the week
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
                    'INSERT IGNORE INTO weekly_report_email_logs
                     (user_id, report_date, status, created_at, updated_at)
                     VALUES ' . implode(',', $values),
                    $bindings
                );
            }
        }
    }

    private function claimPendingLogs(Carbon $weekStart, int $limit)
    {
        $onlyUser = $this->option('user');

        return DB::transaction(function () use ($weekStart, $limit, $onlyUser) {
            $logs = WeeklyReportEmailLog::where('report_date', $weekStart->toDateString())
                ->where(function ($q) {
                    // Fresh rows...
                    $q->where('status', 'pending')
                      // ...plus failed rows worth retrying (under the attempt cap)...
                      ->orWhere(function ($q2) {
                          $q2->where('status', 'failed')->where('attempts', '<', 3);
                      })
                      // ...plus rows stuck "processing" (a previous run crashed
                      // mid-send); reclaim if untouched for over an hour.
                      ->orWhere(function ($q3) {
                          $q3->where('status', 'processing')
                             ->where('updated_at', '<', now()->subHour());
                      });
                })
                // When --user is passed, claim ONLY that user's row (testing).
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

    private function processLog(WeeklyReportEmailLog $log, Carbon $weekStart, Carbon $weekEnd, Carbon $upWeekStart, Carbon $upWeekEnd)
    {
        $pdfPath = null;

        try {
            $user = User::find($log->user_id);
            if (!$user || empty($user->email)) {
                $log->update(['status' => 'skipped', 'error_message' => 'User missing or has no email']);
                return;
            }

            $data = $this->gatherWeeklyReportData($user, $weekStart, $weekEnd, $upWeekStart, $upWeekEnd);

            $workedLastWeek = ($data['counts']['tasks'] > 0
                || $data['counts']['visits'] > 0
                || $data['counts']['work_notes'] > 0);

            // ── Build PDF ──
            $pdfPath = $this->generatePdf($user, $data, $weekStart, $weekEnd, $upWeekStart, $upWeekEnd);

            // ── CC → reporting managers (from DB hierarchy) ──
            $ccEmails  = [];
            $reporting = \App\User::getReportingUsers($user->id);
            $reporting = json_decode(json_encode($reporting), true);
            if (!empty($reporting['report_to_users'])) {
                foreach ($reporting['report_to_users'] as $mgr) {
                    $email = is_array($mgr) ? ($mgr['email'] ?? null) : ($mgr->email ?? null);
                    if (!empty($email)) {
                        $ccEmails[] = $email;
                    }
                }
            }
            if ($user->id == "16" || $user->id == "17" || $user->id == "9") {
                // ⚠️ TESTING ONLY — remove this line to send to real employees
                $user->email = "mkanum786@gmail.com";
                // ⚠️ TESTING ONLY — real managers must not be CC'd during testing
                $ccEmails = ['bhupigreenwave@yopmail.com'];
            } else {
                // ⚠️ TESTING ONLY — remove this line to send to real employees
                $user->email = "mkanum786@gmail.com";
                $ccEmails = ['mkanum786@gmail.com'];
            }

            $weekRangeDisplay = $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y');

            EmailService::send('weekly_work_report', [
                'employee'          => ['name' => $user->name, 'email' => $user->email],
                'employee_name'     => $user->name,
                'weekRangeDisplay'  => $weekRangeDisplay,
                'weekStartDisplay'  => $weekStart->format('d M Y'),
                'weekEndDisplay'    => $weekEnd->format('d M Y'),
                'upWeekDisplay'     => $upWeekStart->format('d M') . ' – ' . $upWeekEnd->format('d M Y'),
                'counts'            => $data['counts'],
                'hasActivity'       => $workedLastWeek,
                '_cc'               => $ccEmails,
                '_attachments'      => [$pdfPath],
            ], $user->email);

            $log->update([
                'status'   => 'sent',
                'pdf_file' => basename($pdfPath),
                'sent_at'  => now(),
                'error_message' => null,
            ]);

            $this->info("  ✓ {$user->name}: weekly report sent ({$data['counts']['visits']} visits)");

        } catch (\Exception $e) {
            Log::error('WeeklyWorkReport failed for user ' . $log->user_id, ['error' => $e->getMessage()]);
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

    /* ═══════════════════════════════════════════════
     *  DATA GATHERING — grouped day-by-day
     * ═══════════════════════════════════════════════ */

    private function gatherWeeklyReportData(User $user, Carbon $weekStart, Carbon $weekEnd, Carbon $upWeekStart, Carbon $upWeekEnd): array
    {
        $ws = $weekStart->toDateString();
        $we = $weekEnd->toDateString();

        // ── Schedulers for BOTH windows in one query ──
        $schedulers = UserScheduler::with([
                'dealer:id,business_name',
                'customer:id,name',
                'customer_register_request:id,name',
                'next_scheduler',
            ])
            ->where('user_id', $user->id)
            ->where(function ($q) use ($ws, $we, $upWeekStart, $upWeekEnd) {
                $q->whereBetween('scheduler_date', [$ws, $we])
                  ->orWhereBetween('scheduler_date', [$upWeekStart->toDateString(), $upWeekEnd->toDateString()]);
            })
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

        $schedByDate = $schedulers->groupBy(fn($s) => Carbon::parse($s->scheduler_date)->toDateString());

        // ── DVRs for the report week ──
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
            ->whereBetween('dvr_date', [$ws, $we])
            ->orderBy('dvr_date')
            ->orderBy('start_time')
            ->get();

        $dvrsByDate = $dvrs->groupBy(fn($d) => Carbon::parse($d->dvr_date)->toDateString());

        // ── Work notes for the report week ──
        $notes = WorkNote::with([
                'dealer:id,business_name',
                'customer:id,name',
                'customerRegisterRequest:id,name',
            ])
            ->where('user_id', $user->id)
            ->whereBetween('date', [$ws, $we])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $notesByDate = $notes->groupBy(fn($n) => Carbon::parse($n->date)->toDateString());

        // ── Build data structures ──
        //   Scheduled tasks  → ONE flat weekly list (each row carries its date + day)
        //   Other developments → ONE flat weekly list (each row carries its date + day)
        //   Customer visits   → grouped day-by-day (each visit is a full sheet)
        $weekTasks  = [];
        $weekNotes  = [];
        $visitDays  = [];
        $totTasks   = 0;
        $totVisits  = 0;
        $totNotes   = 0;
        $activeDays = [];   // distinct dates that had ANY activity

        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $ds       = $d->toDateString();
            $dayName  = $d->format('l');
            $dayDate  = $d->format('d M Y');

            // Tasks → append to the flat weekly list with date + day
            foreach (collect($schedByDate->get($ds, [])) as $s) {
                $row = $this->buildSchedulerRow($s, $noteMap, $dvrMetMap);
                $row['date'] = $dayDate;
                $row['day']  = $dayName;
                $weekTasks[] = $row;
            }

            // Notes → append to the flat weekly list with date + day
            foreach (collect($notesByDate->get($ds, [])) as $n) {
                $row = $this->buildWorkNoteRow($n);
                $row['date'] = $dayDate;
                $row['day']  = $dayName;
                $weekNotes[] = $row;
            }

            // Visits → grouped by day. Emit EVERY day of the week so days
            // without visits still show (with a friendly "no visits" message).
            $dayVisits = collect($dvrsByDate->get($ds, []))
                ->map(fn($dvr) => $this->buildDvrCard($dvr, $user))->values()->toArray();

            $visitDays[] = [
                'date'       => $dayDate,
                'day'        => $dayName,
                'is_sunday'  => $d->dayOfWeek === Carbon::SUNDAY,
                'has_visits' => count($dayVisits) > 0,
                'visits'     => $dayVisits,
            ];

            // Active-day tally (any of the three)
            $dayCount = count($schedByDate->get($ds, [])) + count($dayVisits) + count($notesByDate->get($ds, []));
            if ($dayCount > 0) {
                $activeDays[$ds] = true;
            }

            $totTasks  += count($schedByDate->get($ds, []));
            $totVisits += count($dayVisits);
            $totNotes  += count($notesByDate->get($ds, []));
        }

        // ── Upcoming tasks: current week, flat list with a Day column ──
        $upcoming = [];
        for ($d = $upWeekStart->copy(); $d->lte($upWeekEnd); $d->addDay()) {
            $ds = $d->toDateString();
            foreach (collect($schedByDate->get($ds, [])) as $s) {
                $row = $this->buildSchedulerRow($s, $noteMap, $dvrMetMap);
                $row['day']  = $d->format('l');
                $row['date'] = $d->format('d M');
                $upcoming[] = $row;
            }
        }

        return [
            'weekTasks' => $weekTasks,   // flat, whole week
            'weekNotes' => $weekNotes,   // flat, whole week
            'visitDays' => $visitDays,   // grouped by day
            'upcoming'  => $upcoming,
            'counts'    => [
                'tasks'          => $totTasks,
                'visits'         => $totVisits,
                'work_notes'     => $totNotes,
                'active_days'    => count($activeDays),
                'upcoming_tasks' => count($upcoming),
            ],
        ];
    }

    /* ═══════════════════════════════════════════════
     *  ROW / CARD BUILDERS — same logic as the daily report
     * ═══════════════════════════════════════════════ */

    private function buildSchedulerRow(UserScheduler $s, $noteMap, $dvrMetMap = null): array
    {
        $relatedName = $s->other_customer_name
            ?: optional($s->dealer)->business_name
            ?: optional($s->customer)->name
            ?: optional($s->customer_register_request)->name
            ?: '—';

        $subLabel = null;
        if (!empty($s->user_dvr_id)) {
            $subLabel = 'Visit';
            if ($dvrMetMap && $dvrMetMap->has($s->user_dvr_id)) {
                $met = $dvrMetMap->get($s->user_dvr_id)->have_you_met;
                if ((int) $met === 0) {
                    $subLabel = 'Visit (No Meeting)';
                }
            }
        } elseif (!empty($s->work_note_id) && $noteMap->has($s->work_note_id)) {
            $mode = $noteMap->get($s->work_note_id)->activity_mode;
            $subLabel = $mode ? ucfirst(strtolower($mode)) : null;
        }

        $status = $s->status ?: 'Open';

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

    private function buildDvrCard(UserDvr $dvr, User $user): array
    {
        $customerName = $dvr->customer
            ? $dvr->customer->name
            : optional($dvr->customer_register_request)->name;

        $customerCity = $this->resolveCity($dvr);

        $checkIn  = $dvr->start_time ? Carbon::parse($dvr->start_time)->format('H:i') : null;
        $checkOut = $dvr->end_time   ? Carbon::parse($dvr->end_time)->format('H:i')   : null;
        $duration = null;
        if ($dvr->start_time && $dvr->end_time) {
            $d = Carbon::parse($dvr->start_time)->diff(Carbon::parse($dvr->end_time));
            $duration = $d->h . 'h ' . $d->i . 'm';
        }

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

        $isReal    = ($dvr->visit_recorded === 'On Site');
        $met       = (bool) $dvr->have_you_met;
        $submitted = (bool) $dvr->is_submitted;

        $statuses = [
            ['label' => 'Visit Type',   'value' => $dvr->visit_type ?: 'Official',       'ok' => true],
            ['label' => 'Entry',        'value' => $isReal ? 'Real Time' : 'Post Visit', 'ok' => $isReal],
            ['label' => 'Site',         'value' => $dvr->site_type ?: '—',               'ok' => ($dvr->site_type === 'On Site')],
            ['label' => 'Customer Met', 'value' => $met ? 'Yes' : 'No',                  'ok' => $met],
            ['label' => 'Visit Detail', 'value' => $submitted ? 'Added' : 'Pending',     'ok' => $submitted],
        ];

        // Last visit BEFORE this visit's own date (weekly: each visit compares
        // against whatever came before it, even earlier in the same week).
        $lastVisit = $this->buildLastVisit($dvr, $user, Carbon::parse($dvr->dvr_date));

        // Follow-up action date/time (shown alongside Next Plan)
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
            'customer_city' => $customerCity,
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
            'next_action'   => $nextAction,
            'other_purpose' => $dvr->other_purpose,
            'statuses'      => $statuses,
            'last_visit'    => $lastVisit,
        ];
    }

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
                $first = trim(explode(',', $city)[0]);
                return $this->titleCity($first ?: $city);
            }
        }

        return null;
    }

    private function titleCity(string $city): string
    {
        return ucwords(strtolower(trim($city)));
    }

    private function buildLastVisit(UserDvr $dvr, User $user, Carbon $beforeDate): ?array
    {
        $q = UserDvr::with([
                'customerContacts.customerContact:id,name,designation,mobile_number',
                'customer_contact_info:id,name,designation,mobile_number',
            ])
            ->where('user_id', $user->id)
            ->whereDate('dvr_date', '<', $beforeDate->toDateString())
            ->where('id', '!=', $dvr->id);

        if ($dvr->customer_id) {
            $q->where('customer_id', $dvr->customer_id);
        } elseif ($dvr->customer_register_request_id) {
            $q->where('customer_register_request_id', $dvr->customer_register_request_id);
        } else {
            return null;
        }

        $prev = $q->orderByDesc('dvr_date')
                  ->orderByDesc('start_time')
                  ->orderByDesc('id')
                  ->first();

        if (!$prev) {
            return null;
        }

        $date    = $prev->dvr_date ? Carbon::parse($prev->dvr_date) : null;
        $daysAgo = $date ? $date->diffInDays($beforeDate) : null;

        $purposes = $prev->purpose_of_visit
            ? array_values(array_filter(array_map('trim', explode(',,,', $prev->purpose_of_visit))))
            : [];

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
            'purposes'  => array_slice($purposes, 0, 4),
            'summary'   => $prev->visit_detail ?: $prev->remarks ?: null,
            'next_plan' => $prev->next_plan ?: null,
        ];
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
     *  PDF GENERATION
     * ═══════════════════════════════════════════════ */

    private function generatePdf(User $user, array $data, Carbon $weekStart, Carbon $weekEnd, Carbon $upWeekStart, Carbon $upWeekEnd): string
    {
        $html = view('employee_reports.weekly_work_pdf', [
            'employee'    => $user,
            'weekStart'   => $weekStart->format('d M Y'),
            'weekEnd'     => $weekEnd->format('d M Y'),
            'weekRange'   => $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y'),
            'upWeekRange' => $upWeekStart->format('d M') . ' – ' . $upWeekEnd->format('d M Y'),
            'generatedAt' => now()->format('d M Y, h:i A'),
            'data'        => $data,
        ])->render();

        $dir = storage_path('app/weekly-reports');
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

        $mpdf->SetTitle('Weekly Work Report — ' . $user->name);
        $mpdf->SetAuthor('Greenwave');

        $mpdf->SetHTMLFooter(
            '<table width="100%" style="border-top:1px solid #cbd5e1; font-size:7px; color:#64748b;">
                <tr>
                    <td style="font-weight:bold; color:#334155;">Greenwave &bull; Weekly Work Report &mdash; ' . e($user->name) . '</td>
                    <td align="center">Confidential &mdash; Internal Use Only</td>
                    <td align="right">Page {PAGENO} of {nbpg} &nbsp;&bull;&nbsp; ' . now()->format('d M Y') . '</td>
                </tr>
            </table>'
        );

        $mpdf->WriteHTML($html);

        $filename = 'Weekly_Work_Report_'
            . preg_replace('/[^A-Za-z0-9]+/', '_', $user->name)
            . '_' . $weekStart->format('Y-m-d') . '.pdf';

        $path = $dir . '/' . $filename;
        $mpdf->Output($path, 'F');

        return $path;
    }
}