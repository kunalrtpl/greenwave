<?php

namespace App\Services\Report;

use App\HolidayList;
use App\User;
use App\UserDvr;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;

/**
 * ─────────────────────────────────────────────────────────────────────
 *  MONTHLY VISIT ANALYSIS — SHARED REPORT ENGINE
 * ─────────────────────────────────────────────────────────────────────
 *  Single source of truth for the monthly Visit Analysis report.
 *
 *  Used by BOTH:
 *    - App\Console\Commands\SendMonthlyVisitAnalysis (emails the
 *      PDF to each marketing employee on the 1st of every month), and
 *    - App\Http\Controllers\Admin\MonthlyVisitAnalysisController
 *      (admin panel screen — pick an employee + month, download the PDF).
 *
 *  Keeping the gathering/rendering here guarantees the admin download is
 *  byte-for-byte the same report the employee receives by email.
 *
 *  Report contents:
 *    - Overall totals: visits, met, not met, unique customers, trials
 *    - Days-worked totals: days with a visit vs. working days in the month
 *      (Sundays/holidays excluded from "working days" unless the employee
 *      actually visited on one — then it counts)
 *    - Date-wise analysis table — one row per calendar day; days with no
 *      visit show "HOL"/"SUN" (from the holidays table's is_recurring
 *      holidays) or a dash for a plain missed working day
 *    - Customer-wise analysis table (visits, trials, business linking)
 *    - Trial details table (per-trial status + report-attached flag)
 */
class MonthlyVisitAnalysisService
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

    /**
     * Holidays (from holiday_lists) that apply to this employee for the
     * given month — national holidays, plus city-specific ones matching the
     * employee's base_city. Recurring holidays (is_recurring, stored with a
     * fixed month/day such as 15 Aug / 2 Oct) are re-anchored onto the
     * report's year. Same matching rules as AdminAttendanceController's
     * holiday lookup, kept in sync so a day is never "HOL" here but not
     * there, or vice versa.
     *
     * @return array<string, string>  dateString => holiday name
     */
    private function holidaysForMonth(User $user, Carbon $monthStart, Carbon $monthEnd): array
    {
        $month    = $monthStart->month;
        $year     = $monthStart->year;
        $monthPad = sprintf('%02d', $month);

        $holidays = HolidayList::where('is_active', true)
            ->where(function ($q) use ($month, $year, $monthPad) {
                $q->where(function ($i) use ($month, $year) {
                    $i->whereMonth('date', $month)->whereYear('date', $year);
                })->orWhere(function ($i) use ($monthPad) {
                    $i->where('is_recurring', true)->whereRaw("DATE_FORMAT(date,'%m')=?", [$monthPad]);
                });
            })
            ->select('id', 'name', 'date', 'city', 'is_national', 'is_recurring')
            ->get();

        $map = [];
        foreach ($holidays as $h) {
            if (!$h->is_national && strtolower((string) $h->city) !== strtolower((string) $user->base_city)) {
                continue;
            }

            $ds = $h->is_recurring
                ? ($year . '-' . $monthPad . '-' . Carbon::parse($h->date)->format('d'))
                : Carbon::parse($h->date)->toDateString();

            $map[$ds] = $h->name;
        }

        return $map;
    }

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

        // ── Visit Entry Analysis — how the visit was logged, not what
        //    happened on it: Official/Unofficial (visit_type), On Site/Off
        //    Site (site_type), and whether it was captured live or filled in
        //    afterwards (visit_recorded — displayed as "Real Time"/"Post
        //    Visit" in the report). Location match/mismatch is derived from
        //    location_message: empty means the GPS check passed, any message
        //    means it flagged a mismatch. ──
        $visitTypeOfficial   = $dvrs->where('visit_type', 'Official')->count();
        $visitTypeUnofficial = $dvrs->where('visit_type', 'Unofficial')->count();

        $siteTypeOnSite  = $dvrs->where('site_type', 'On Site')->count();
        $siteTypeOffSite = $dvrs->where('site_type', 'Off Site')->count();

        $recordedRealTime  = $dvrs->where('visit_recorded', 'On Site')->count();
        $recordedPostVisit = $dvrs->where('visit_recorded', 'Off Site')->count();

        $locationMatch    = $dvrs->filter(fn($d) => empty($d->location_message))->count();
        $locationMismatch = $totalVisits - $locationMatch;

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

        // ── Date-wise analysis — one row per CALENDAR day in the month, so a
        //    day with no visit still shows up (as a holiday/Sunday label, or
        //    a dash for a plain missed working day) instead of just vanishing
        //    from the table. ──
        $holidayMap = $this->holidaysForMonth($user, $monthStart, $monthEnd);

        $dvrsByDate       = $dvrs->groupBy(fn($d) => Carbon::parse($d->dvr_date)->toDateString());
        $dateWise         = [];
        $daysWorked       = 0;
        $workingDaysTotal = 0;

        for ($cursor = $monthStart->copy(); $cursor->lte($monthEnd); $cursor->addDay()) {
            $ds        = $cursor->toDateString();
            $isSunday  = $cursor->isSunday();
            $isHoliday = isset($holidayMap[$ds]);
            $dayDvrs   = $dvrsByDate->get($ds);
            $hasVisit  = $dayDvrs && $dayDvrs->count() > 0;

            // Working days exclude Sunday/holiday — UNLESS the employee
            // actually worked (visited) that day, which then still counts.
            if ($hasVisit || (!$isSunday && !$isHoliday)) {
                $workingDaysTotal++;
            }

            if (!$hasVisit) {
                $dateWise[] = [
                    'date'                 => $cursor->format('d M Y'),
                    'day'                  => $cursor->format('D'),
                    'visits'               => 0,
                    'met'                  => 0,
                    'not_met'              => 0,
                    'visit_detail_pending' => 0,
                    'trials'               => 0,
                    'trials_attached'      => 0,
                    'trials_not_attached'  => 0,
                    'day_type'             => $isHoliday ? 'HOL' : ($isSunday ? 'SUN' : null),
                    'holiday_name'         => $isHoliday ? $holidayMap[$ds] : null,
                ];
                continue;
            }

            $daysWorked++;

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

            $dateWise[] = [
                'date'           => $cursor->format('d M Y'),
                'day'            => $cursor->format('D'),
                'visits'         => $dayVisits,
                'met'            => $dayMet,
                'not_met'        => $dayVisits - $dayMet,
                'visit_detail_pending' => $dayVisitDetailPending,
                'trials'         => $dayTrialsCount,
                'trials_attached'     => $dayAttached,
                'trials_not_attached' => $dayTrialsCount - $dayAttached,
                'day_type'       => null,
                'holiday_name'   => null,
            ];
        }

        $daysWorkedPercent = $workingDaysTotal > 0 ? (int) round(($daysWorked / $workingDaysTotal) * 100) : 0;

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
                'days_worked'         => $daysWorked,
                'working_days_total'  => $workingDaysTotal,
                'days_worked_percent' => $daysWorkedPercent,
                'visit_type_official'   => $visitTypeOfficial,
                'visit_type_unofficial' => $visitTypeUnofficial,
                'site_type_onsite'      => $siteTypeOnSite,
                'site_type_offsite'     => $siteTypeOffSite,
                'recorded_realtime'     => $recordedRealTime,
                'recorded_postvisit'    => $recordedPostVisit,
                'location_match'        => $locationMatch,
                'location_mismatch'     => $locationMismatch,
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
        $dir = storage_path('app/monthly-visit-analysis');
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
        return 'Monthly_Visit_Analysis_'
            . preg_replace('/[^A-Za-z0-9]+/', '_', $user->name)
            . '_' . $monthStart->format('Y-m') . '.pdf';
    }

    private function buildMpdf(User $user, array $data, Carbon $monthStart, Carbon $monthEnd): Mpdf
    {
        $html = view('employee_reports.monthly_visit_analysis_pdf', [
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

        $mpdf->SetTitle('Monthly Visit Analysis — ' . $user->name);
        $mpdf->SetAuthor('Greenwave');

        $mpdf->SetHTMLFooter(
            '<table width="100%" style="border-top:1px solid #cbd5e1; font-size:7px; color:#64748b;">
                <tr>
                    <td style="font-weight:bold; color:#334155;">Greenwave &bull; Monthly Visit Analysis &mdash; ' . e($user->name) . '</td>
                    <td align="center">Confidential &mdash; Internal Use Only</td>
                    <td align="right">Page {PAGENO} of {nbpg} &nbsp;&bull;&nbsp; ' . now()->format('d M Y') . '</td>
                </tr>
            </table>'
        );

        $mpdf->WriteHTML($html);

        return $mpdf;
    }
}
