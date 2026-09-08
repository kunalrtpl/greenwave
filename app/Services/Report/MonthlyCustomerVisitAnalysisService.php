<?php

namespace App\Services\Report;

use App\User;
use App\UserDvr;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;

/**
 * ─────────────────────────────────────────────────────────────────────
 *  MONTHLY CUSTOMER VISIT ANALYSIS — SHARED REPORT ENGINE
 * ─────────────────────────────────────────────────────────────────────
 *  Single source of truth for the monthly Customer Visit Analysis report.
 *
 *  Used by BOTH:
 *    - App\Console\Commands\SendMonthlyCustomerVisitAnalysis (emails the
 *      PDF to each marketing employee on the 1st of every month), and
 *    - App\Http\Controllers\Admin\MonthlyCustomerVisitAnalysisController
 *      (admin panel screen — pick an employee + month, download the PDF).
 *
 *  Keeping the gathering/rendering here guarantees the admin download is
 *  byte-for-byte the same report the employee receives by email.
 *
 *  Report contents:
 *    - Overall totals: visits, met, not met, unique customers, trials
 *    - Date-wise analysis table (visits/met/not-met, trials)
 *    - Customer-wise analysis table (visits, trials, business linking)
 *    - Trial details table (per-trial status + report-attached flag)
 */
class MonthlyCustomerVisitAnalysisService
{
    const MARKETING_DEPARTMENT_ID = 2;

    /** Employees never included in this report (management / non-field ids). */
    const EXCLUDED_USER_IDS = [16, 17, 9, 25];

    /* ═══════════════════════════════════════════════
     *  ELIGIBLE EMPLOYEES
     * ═══════════════════════════════════════════════ */

    /**
     * Marketing-department employees this report is produced for:
     * active, with an email, minus the excluded ids.
     *
     * @return Collection<User>  id, name, email, designation
     */
    public static function eligibleEmployees(): Collection
    {
        $marketingUserIds = DB::table('user_departments')
            ->where('department_id', self::MARKETING_DEPARTMENT_ID)
            ->pluck('user_id')
            ->toArray();

        if (empty($marketingUserIds)) {
            return collect();
        }

        return User::where('status', 1)
            ->where('email', '!=', '')
            ->whereNotNull('email')
            ->whereIn('id', $marketingUserIds)
            ->whereNotIn('id', self::EXCLUDED_USER_IDS)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'designation']);
    }

    /* ═══════════════════════════════════════════════
     *  DATA GATHERING
     * ═══════════════════════════════════════════════ */

    public function gather(User $user, Carbon $monthStart, Carbon $monthEnd): array
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

    /**
     * Render the PDF and write it to storage — used by the mail command,
     * which attaches the file and deletes it afterwards.
     *
     * @return string  Absolute path of the written PDF
     */
    public function writePdf(User $user, array $data, Carbon $monthStart, Carbon $monthEnd): string
    {
        $dir = storage_path('app/monthly-customer-visit-analysis');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $path = $dir . '/' . $this->filename($user, $monthStart);
        $this->buildMpdf($user, $data, $monthStart, $monthEnd)->Output($path, 'F');

        return $path;
    }

    /**
     * Render the PDF into memory — used by the admin screen, which streams
     * it straight back to the browser as a download.
     */
    public function renderPdfString(User $user, array $data, Carbon $monthStart, Carbon $monthEnd): string
    {
        return $this->buildMpdf($user, $data, $monthStart, $monthEnd)->Output('', 'S');
    }

    public function filename(User $user, Carbon $monthStart): string
    {
        return 'Monthly_Customer_Visit_Analysis_'
            . preg_replace('/[^A-Za-z0-9]+/', '_', $user->name)
            . '_' . $monthStart->format('Y-m') . '.pdf';
    }

    private function buildMpdf(User $user, array $data, Carbon $monthStart, Carbon $monthEnd): Mpdf
    {
        $html = view('employee_reports.monthly_customer_visit_analysis_pdf', [
            'employee'    => $user,
            'monthLabel'  => $monthStart->format('F Y'),
            'monthRange'  => $monthStart->format('d M') . ' – ' . $monthEnd->format('d M Y'),
            'generatedAt' => now()->format('d M Y, h:i A'),
            'data'        => $data,
        ])->render();

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

        return $mpdf;
    }
}
