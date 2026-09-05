<?php

namespace App\Console\Commands;

use App\MonthlyCustomerVisitAnalysisLog;
use App\User;
use App\UserDvr;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

/**
 * ─────────────────────────────────────────────────────────────────────
 *  MONTHLY CUSTOMER VISIT ANALYSIS — EMAIL WITH PDF ATTACHMENT
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
 *  PDF contents (numbers-first, per client's spec):
 *    - Overall totals: visits, met, not met, unique customers, trials
 *    - Date-wise analysis table (visits/met/not-met, trials)
 *    - Customer-wise analysis table (visits, trials, business linking)
 *    - Trial details table (per-trial status + report-attached flag)
 */
class SendMonthlyCustomerVisitAnalysis extends Command
{
    protected $signature = 'report:monthly-customer-visit-analysis
                        {--limit=2 : Records processed per run}
                        {--date=   : Simulate "today" (Y-m-d) — report covers the previous calendar month}
                        {--user=   : Process only this user id (testing)}';

    protected $description = "Email each employee their previous-month Customer Visit Analysis (PDF) — visit counts, customer coverage and trial status";

    const RETENTION_DAYS = 400;
    const MARKETING_DEPARTMENT_ID = 2;

    // ⚠️ TESTING ONLY — every report is sent to this address instead of the
    // employee's real inbox. Set to null to send to real employees.
    const TEST_EMAIL_OVERRIDE = 'mkanum786@gmail.com';

    const EXCLUDED_USER_IDS = [16, 17, 9, 25];

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
        DB::table('monthly_customer_visit_analysis_logs')
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
        $marketingUserIds = DB::table('user_departments')
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
                    $query->whereNotIn('id', self::EXCLUDED_USER_IDS)
                          ->whereIn('id', $marketingUserIds);
                }
            });

        $now = now();
        $rows = $userQuery->pluck('id')->map(function ($id) use ($monthStart, $now) {
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
                    'INSERT IGNORE INTO monthly_customer_visit_analysis_logs
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
            $logs = MonthlyCustomerVisitAnalysisLog::where('report_date', $monthStart->toDateString())
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

    private function processLog(MonthlyCustomerVisitAnalysisLog $log, Carbon $monthStart, Carbon $monthEnd)
    {
        $pdfPath = null;

        try {
            $user = User::find($log->user_id);
            if (!$user || empty($user->email)) {
                $log->update(['status' => 'skipped', 'error_message' => 'User missing or has no email']);
                return;
            }

            $data = $this->gatherMonthlyReportData($user, $monthStart, $monthEnd);

            $hasActivity = $data['overall']['total_visits'] > 0;

            // ── Build PDF ──
            $pdfPath = $this->generatePdf($user, $data, $monthStart, $monthEnd);

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

            EmailService::send('monthly_customer_visit_analysis', [
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

            $this->info("  ✓ {$user->name}: monthly customer visit analysis sent ({$data['overall']['total_visits']} visits)");

        } catch (\Exception $e) {
            Log::error('MonthlyCustomerVisitAnalysis failed for user ' . $log->user_id, ['error' => $e->getMessage()]);
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
     *  DATA GATHERING
     * ═══════════════════════════════════════════════ */

    private function gatherMonthlyReportData(User $user, Carbon $monthStart, Carbon $monthEnd): array
    {
        $ms = $monthStart->toDateString();
        $me = $monthEnd->toDateString();

        $dvrs = UserDvr::with([
                'customer:id,name,business_model,dealer_id',
                'customer.link_dealer:id,business_name',
                'customer_register_request:id,name,business_model,dealer_id',
                'customer_register_request.dealer:id,business_name',
                'trials.attachments',
            ])
            ->where('user_id', $user->id)
            ->whereBetween('dvr_date', [$ms, $me])
            ->orderBy('dvr_date')
            ->orderBy('id')
            ->get();

        $customerKey = function (UserDvr $dvr) {
            if ($dvr->customer_id) return 'c_' . $dvr->customer_id;
            if ($dvr->customer_register_request_id) return 'r_' . $dvr->customer_register_request_id;
            return 'n_' . ($dvr->customer ? $dvr->customer->name : optional($dvr->customer_register_request)->name);
        };

        $customerName = function (UserDvr $dvr) {
            return $dvr->customer
                ? $dvr->customer->name
                : (optional($dvr->customer_register_request)->name ?: 'N/A');
        };

        // ── Business linking: customers.business_model is Dealer / Open /
        //    Direct Customer; when Dealer, dealer_id names which dealer. ──
        $businessInfo = function (UserDvr $dvr) {
            $source = $dvr->customer ?: $dvr->customer_register_request;
            if (!$source) {
                return ['type' => '—', 'dealer_name' => null];
            }

            $dealerName = null;
            if ($source->business_model === 'Dealer') {
                $dealer     = $dvr->customer ? $source->link_dealer : $source->dealer;
                $dealerName = optional($dealer)->business_name;
            }

            return ['type' => $source->business_model ?: '—', 'dealer_name' => $dealerName];
        };

        // ── Trial "report attached" rule for this report: a trial only
        //    counts as attached if it has a PDF file in user_dvr_attachments. ──
        $isReportAttached = function ($trial) {
            return $trial->attachments->contains(function ($att) {
                return $att->file && strtolower(pathinfo($att->file, PATHINFO_EXTENSION)) === 'pdf';
            });
        };

        // ── Overall totals ──
        $totalVisits = $dvrs->count();
        $totalMet    = $dvrs->filter(fn($d) => (bool) $d->have_you_met)->count();
        $totalNotMet = $totalVisits - $totalMet;
        $uniqueCustomers = $dvrs->map($customerKey)->unique()->count();
        $visitDetailPending = $dvrs->filter(fn($d) => !$d->is_submitted)->count();

        // ── Unique customers, broken down by business linking ──
        //    Dealer-linked customers are broken down BY DEALER NAME (not just
        //    a single "Dealer" bucket), plus separate Direct/Open counts.
        $uniqueCustomerBiz = $dvrs->groupBy($customerKey)->map(fn($group) => $businessInfo($group->first()));

        $dealerWise = $uniqueCustomerBiz
            ->filter(fn($b) => $b['type'] === 'Dealer')
            ->groupBy(fn($b) => $b['dealer_name'] ?: 'Unlinked Dealer')
            ->map(fn($group, $name) => ['dealer_name' => $name, 'count' => $group->count()])
            ->sortByDesc('count')
            ->values()
            ->all();

        $directCustomerCount = $uniqueCustomerBiz->where('type', 'Direct Customer')->count();
        $openCustomerCount   = $uniqueCustomerBiz->where('type', 'Open')->count();

        // ── Trial details (deduped by trial id) + overall trial totals ──
        //    Only trials actually done (trial_done = 1) are counted/listed —
        //    trials not yet run aren't meaningful for a "report attached" view.
        $trialDetails = [];
        foreach ($dvrs as $dvr) {
            foreach ($dvr->trials as $trial) {
                if (isset($trialDetails[$trial->id])) continue;
                if (!$trial->trial_done) continue;

                $attached = $isReportAttached($trial);

                $trialDetails[$trial->id] = [
                    'date'          => Carbon::parse($dvr->dvr_date)->format('d M Y'),
                    'date_sort'     => $dvr->dvr_date,
                    'customer_name' => $customerName($dvr),
                    'trial_type'    => $trial->trial_type ?: '—',
                    'attached'      => $attached,
                    'status'        => $attached ? 'Report Attached' : 'Report Pending',
                    'status_color'  => $attached ? '#16a34a' : '#dc2626',
                ];
            }
        }
        $trialDetails = collect($trialDetails)->sortBy('date_sort')->values()->all();

        $totalTrials    = count($trialDetails);
        $trialsAttached = collect($trialDetails)->where('attached', true)->count();
        $trialsPending  = $totalTrials - $trialsAttached;

        // ── Date-wise analysis ──
        $dvrsByDate = $dvrs->groupBy(fn($d) => Carbon::parse($d->dvr_date)->toDateString());
        $dateWise   = [];

        foreach ($dvrsByDate as $ds => $dayDvrs) {
            $dayMet    = $dayDvrs->filter(fn($d) => (bool) $d->have_you_met)->count();
            $dayVisits = $dayDvrs->count();

            $dayTrialIds = [];
            $dayAttached = 0;
            foreach ($dayDvrs as $dvr) {
                foreach ($dvr->trials as $trial) {
                    if (isset($dayTrialIds[$trial->id])) continue;
                    if (!$trial->trial_done) continue;
                    $dayTrialIds[$trial->id] = true;
                    if ($isReportAttached($trial)) {
                        $dayAttached++;
                    }
                }
            }
            $dayTrialsCount = count($dayTrialIds);

            $dayVisitDetailPending = $dayDvrs->filter(fn($d) => !$d->is_submitted)->count();

            $date = Carbon::parse($ds);
            $dateWise[] = [
                'date'           => $date->format('d M Y'),
                'day'            => $date->format('D'),
                'visits'         => $dayVisits,
                'met'            => $dayMet,
                'not_met'        => $dayVisits - $dayMet,
                'visit_detail_pending' => $dayVisitDetailPending,
                'trials'         => $dayTrialsCount,
                'trials_attached'     => $dayAttached,
                'trials_not_attached' => $dayTrialsCount - $dayAttached,
            ];
        }

        // ── Customer-wise analysis ──
        $customerWise = $dvrs
            ->groupBy($customerKey)
            ->map(function ($group) use ($customerName, $businessInfo, $isReportAttached) {
                $visits = $group->count();
                $met    = $group->filter(fn($d) => (bool) $d->have_you_met)->count();

                $trialIds      = [];
                $trialsAttached = 0;
                foreach ($group as $dvr) {
                    foreach ($dvr->trials as $trial) {
                        if (isset($trialIds[$trial->id])) continue;
                        if (!$trial->trial_done) continue;
                        $trialIds[$trial->id] = true;
                        if ($isReportAttached($trial)) {
                            $trialsAttached++;
                        }
                    }
                }
                $trialsCount = count($trialIds);

                $biz = $businessInfo($group->first());

                return [
                    'customer_name'  => $customerName($group->first()),
                    'business_type'  => $biz['type'],
                    'dealer_name'    => $biz['dealer_name'],
                    'total_visits'   => $visits,
                    'not_met'        => $visits - $met,
                    'visit_detail_pending' => $group->filter(fn($d) => !$d->is_submitted)->count(),
                    'trials'         => $trialsCount,
                    'trials_pending' => $trialsCount - $trialsAttached,
                ];
            })
            ->sortByDesc('total_visits')
            ->values()
            ->all();

        return [
            'overall' => [
                'total_visits'     => $totalVisits,
                'met'              => $totalMet,
                'not_met'          => $totalNotMet,
                'unique_customers' => $uniqueCustomers,
                'total_trials'     => $totalTrials,
                'trials_attached'  => $trialsAttached,
                'trials_pending'   => $trialsPending,
                'visit_detail_pending' => $visitDetailPending,
                'dealer_wise'      => $dealerWise,
                'direct_customers' => $directCustomerCount,
                'open_customers'   => $openCustomerCount,
            ],
            'dateWise'     => $dateWise,
            'customerWise' => $customerWise,
            'trialDetails' => $trialDetails,
        ];
    }

    /* ═══════════════════════════════════════════════
     *  PDF GENERATION
     * ═══════════════════════════════════════════════ */

    private function generatePdf(User $user, array $data, Carbon $monthStart, Carbon $monthEnd): string
    {
        $html = view('employee_reports.monthly_customer_visit_analysis_pdf', [
            'employee'    => $user,
            'monthLabel'  => $monthStart->format('F Y'),
            'monthRange'  => $monthStart->format('d M') . ' – ' . $monthEnd->format('d M Y'),
            'generatedAt' => now()->format('d M Y, h:i A'),
            'data'        => $data,
        ])->render();

        $dir = storage_path('app/monthly-customer-visit-analysis');
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

        $mpdf->SetTitle('Monthly Customer Visit Analysis — ' . $user->name);
        $mpdf->SetAuthor('Greenwave');

        $mpdf->SetHTMLFooter(
            '<table width="100%" style="border-top:1px solid #cbd5e1; font-size:7px; color:#64748b;">
                <tr>
                    <td style="font-weight:bold; color:#334155;">Greenwave &bull; Monthly Customer Visit Analysis &mdash; ' . e($user->name) . '</td>
                    <td align="center">Confidential &mdash; Internal Use Only</td>
                    <td align="right">Page {PAGENO} of {nbpg} &nbsp;&bull;&nbsp; ' . now()->format('d M Y') . '</td>
                </tr>
            </table>'
        );

        $mpdf->WriteHTML($html);

        $filename = 'Monthly_Customer_Visit_Analysis_'
            . preg_replace('/[^A-Za-z0-9]+/', '_', $user->name)
            . '_' . $monthStart->format('Y-m') . '.pdf';

        $path = $dir . '/' . $filename;
        $mpdf->Output($path, 'F');

        return $path;
    }
}
