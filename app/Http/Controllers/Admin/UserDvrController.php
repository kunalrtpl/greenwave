<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\UserDvr;
use App\User;
use App\UserAttendance;
use App\UserScheduler;
use Carbon\Carbon;
use PDF;
/**
 * UserDvrController — Admin DVR Module
 * All display logic computed server-side; blade is pure rendering only.
 */
class UserDvrController extends Controller
{
    const STATUS_FILTERS = [
        'entry_type'   => ['Real Time Entry', 'Post Visit Entry'],
        'site_type'    => ['On Site', 'Off Site'],
        'meeting'      => ['Met Customer', 'No Meeting'],
        'detail'       => ['Visit Detail Added', 'Visit Detail Pending'],
        //'verification' => ['Verified', 'Not Verified'],
        'trial_status' => ['Report Attached', 'Trial Report Pending', 'Trial Not Done', 'No Trials'],
    ];

    /* ═══════════════════════════════════════════════
     *  INDEX
     * ═══════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $users         = $this->getEmployeeList();
        $statusFilters = self::STATUS_FILTERS;
        $title         = 'Daily Visit Reports';

        // ── Default to current month + year ────────────────
        $currentMonth = $request->filled('month') ? (int)$request->month : (int)date('m');
        $currentYear  = $request->filled('year')  ? (int)$request->year  : (int)date('Y');

        if (!$request->filled('user_id')) {
            return view('admin.dvrs.index', compact('users', 'statusFilters', 'title'))
                ->with('groupedDvrs', collect())
                ->with('paginator', null)
                ->with('summaryStats', null)
                ->with('customerStats', collect())
                ->with('attendanceMap', collect())
                ->with('currentMonth', $currentMonth)
                ->with('currentYear', $currentYear);
        }

        // ── Build Eloquent query ────────────────────────────
        $query = UserDvr::with([
            'user:id,name',
            'customer:id,name,activity,category,mobile',
            'customer_register_request:id,name,activity,category,mobile,status',
            'customer_contact_info:id,name,designation,mobile_number',
            'customerContacts.customerContact:id,name,designation,department,mobile_number',
            'products.productinfo:id,product_name',
            'trials.attachments',
            'attachments',
            'user_scheduler:id,scheduler_date,scheduler_time,description,status',
        ])
        ->withCount('trials')
        ->where('user_id', $request->user_id)
        ->whereMonth('dvr_date', $currentMonth)
        ->whereYear('dvr_date', $currentYear)
        ->orderBy('dvr_date', 'desc')
        ->orderBy('id', 'desc');

        $allDvrs  = $query->get();
        $dvrDates = $allDvrs->pluck('dvr_date')->unique()->values();

        // ── Attendance map keyed by date ────────────────────
        $attendanceMap = UserAttendance::where('user_id', $request->user_id)
            ->whereIn('in_date', $dvrDates)
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->in_date)->format('Y-m-d'));

        // ── Build computed rows ─────────────────────────────
        $allDvrs = $allDvrs->map(fn($dvr) => $this->buildDvrRow($dvr, $attendanceMap));

        // ── Customer stats (visit count + time %) ──────────
        $customerStats = $this->buildCustomerStats($allDvrs);

        // ── Status filter ───────────────────────────────────
        if ($request->filled('status_filter')) {
            $sf      = $request->status_filter;
            $allDvrs = $allDvrs->filter(function ($dvr) use ($sf) {
                $labels     = collect($dvr['statuses'])->pluck('label');
                $trialLabel = $dvr['trial_overall']['label'];
                return $labels->contains($sf) || $trialLabel === $sf;
            })->values();
        }

        // ── Customer filter ─────────────────────────────────
        if ($request->filled('customer_filter')) {
            $cf      = $request->customer_filter;
            $allDvrs = $allDvrs->filter(fn($d) => $d['customer_name'] === $cf)->values();
        }

        // ── Visit type filter ───────────────────────────────
        if ($request->filled('visit_type')) {
            $allDvrs = $allDvrs->filter(fn($d) => $d['visit_type'] === $request->visit_type)->values();
        }

        // ── Summary stats ───────────────────────────────────
        $summaryStats = $this->buildSummaryStats($allDvrs, $attendanceMap);

        // ── Paginate ────────────────────────────────────────
        $perPage  = 20;
        $page     = (int)$request->get('page', 1);
        $total    = $allDvrs->count();
        $pageDvrs = $allDvrs->forPage($page, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageDvrs, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $groupedDvrs = $pageDvrs->groupBy('dvr_date_key');

        return view('admin.dvrs.index', compact(
            'groupedDvrs', 'paginator', 'users',
            'summaryStats', 'statusFilters', 'attendanceMap',
            'customerStats', 'title', 'currentMonth', 'currentYear'
        ));
    }

    /* ═══════════════════════════════════════════════
     *  SHOW
     * ═══════════════════════════════════════════════ */
    public function show($id)
    {
        $dvr = UserDvr::with([
            'user', 'customer', 'customer_register_request',
            'customerContacts.customerContact',
            'attachments', 'products.productinfo',
            'trials.attachments', 'trials.products.productinfo',
            'complaint_sample', 'market_sample', 'sample_submission',
            'user_scheduler', 'query_info', 'customer_contact_info',
        ])->findOrFail($id);

        $attendance = UserAttendance::where('user_id', $dvr->user_id)
            ->where('in_date', $dvr->dvr_date)->first();

        $computed  = $this->buildDvrRow($dvr, collect());
        $workHours = $this->calcWorkHours($attendance);

        return view('admin.dvrs.show', compact('dvr', 'attendance', 'computed', 'workHours'));
    }

    /* ═══════════════════════════════════════════════
     *  PDF EXPORT
     * ═══════════════════════════════════════════════ */
    public function exportPdf(Request $request)
    {
        if (!$request->filled('user_id')) {
            return redirect()->back()->with('error', 'Please select an employee.');
        }

        $currentMonth = $request->filled('month') ? (int)$request->month : (int)date('m');
        $currentYear  = $request->filled('year')  ? (int)$request->year  : (int)date('Y');

        $query = UserDvr::with([
            'user:id,name',
            'customer:id,name',
            'customer_register_request:id,name',
            'customerContacts.customerContact:id,name,designation,mobile_number',
            'customer_contact_info:id,name,designation,mobile_number',
            'products.productinfo:id,product_name',
            'trials.attachments',
            'user_scheduler:id,scheduler_date,scheduler_time,description,status',
        ])
        ->withCount('trials')
        ->where('user_id', $request->user_id)
        ->whereMonth('dvr_date', $currentMonth)
        ->whereYear('dvr_date', $currentYear)
        ->orderBy('dvr_date', 'desc')
        ->orderBy('id', 'desc');

        $allDvrs       = $query->get();
        $dvrDates      = $allDvrs->pluck('dvr_date')->unique()->values();
        $attendanceMap = UserAttendance::where('user_id', $request->user_id)
            ->whereIn('in_date', $dvrDates)->get()
            ->keyBy(fn($a) => Carbon::parse($a->in_date)->format('Y-m-d'));

        // Build all rows for the full month
        $allRows     = $allDvrs->map(fn($dvr) => $this->buildDvrRow($dvr, $attendanceMap));
        $totalAllDvrs = $allRows->count(); // full month count for context

        // ── Apply filters — PDF matches exactly what's on screen ──
        $filteredRows = $allRows;

        if ($request->filled('status_filter')) {
            $sf           = $request->status_filter;
            $filteredRows = $filteredRows->filter(function ($dvr) use ($sf) {
                $labels     = collect($dvr['statuses'])->pluck('label');
                $trialLabel = $dvr['trial_overall']['label'];
                return $labels->contains($sf) || $trialLabel === $sf;
            })->values();
        }

        if ($request->filled('customer_filter')) {
            $cf           = $request->customer_filter;
            $filteredRows = $filteredRows->filter(fn($d) => $d['customer_name'] === $cf)->values();
        }

        if ($request->filled('visit_type')) {
            $filteredRows = $filteredRows->filter(fn($d) => $d['visit_type'] === $request->visit_type)->values();
        }

        // Stats + customer summary = filtered rows (matches screen)
        $summaryStats  = $this->buildSummaryStats($filteredRows, $attendanceMap);
        $customerStats = $this->buildCustomerStats($filteredRows);

        // For customer summary % context — if customer filter active, use full month as denominator
        // so "12/29 41%" shows correctly instead of "12/12 100%"
        $hasFilter = $request->filled('customer_filter')
                  || $request->filled('status_filter')
                  || $request->filled('visit_type');

        if ($hasFilter) {
            // Recompute customer stats with full-month total as denominator
            $customerStats = $this->buildCustomerStatsWithTotal($filteredRows, $totalAllDvrs, $allRows);
        }

        $groupedDvrs = $filteredRows->groupBy('dvr_date_key');

        // Build filter label and filename suffix
        $filterParts  = [];
        $filterSuffix = '';
        if ($request->filled('customer_filter')) {
            $filterParts[]  = $request->customer_filter;
            $filterSuffix  .= '_' . str_replace(' ', '_', substr($request->customer_filter, 0, 20));
        }
        if ($request->filled('status_filter')) {
            $filterParts[]  = $request->status_filter;
            $filterSuffix  .= '_' . str_replace(' ', '_', $request->status_filter);
        }
        if ($request->filled('visit_type')) {
            $filterParts[]  = $request->visit_type;
        }
        $filterLabel = !empty($filterParts) ? implode(' | ', $filterParts) : null;

        $selectedUser = User::find($request->user_id);
        $monthName    = date('F', mktime(0, 0, 0, $currentMonth, 1));

        $html = view('admin.dvrs.pdf', compact(
            'groupedDvrs', 'summaryStats', 'customerStats',
            'selectedUser', 'monthName', 'currentYear', 'attendanceMap',
            'filterLabel', 'totalAllDvrs'
        ))->render();

        // barryvdh/laravel-dompdf ^0.8.7 usage
        $pdf = PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 96,
                'isPhpEnabled'         => false,
                'debugKeepTemp'        => false,
            ]);

        $filename = 'DVR_' . ($selectedUser->name ?? 'export') . '_' . $monthName . '_' . $currentYear . $filterSuffix . '.pdf';
        return $pdf->download($filename);
    }

    /* ═══════════════════════════════════════════════
     *  PRIVATE HELPERS
     * ═══════════════════════════════════════════════ */

    private function getEmployeeList()
    {
        return User::whereIn('id', UserDvr::select('user_id')->distinct())
            ->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Build full display array for one DVR row — NO logic in blade.
     */
    private function buildDvrRow(UserDvr $dvr, $attendanceMap): array
    {
        $dateKey  = Carbon::parse($dvr->dvr_date)->format('Y-m-d');
        $dateDisp = Carbon::parse($dvr->dvr_date)->format('d M Y');
        $dayName  = Carbon::parse($dvr->dvr_date)->format('D');

        $customerName = $dvr->customer
            ? $dvr->customer->name
            : optional($dvr->customer_register_request)->name;
        $customerType = $dvr->customer ? 'customer' : ($dvr->customer_register_request ? 'request' : 'none');

        $checkIn  = $dvr->start_time ? Carbon::parse($dvr->start_time)->format('H:i') : null;
        $checkOut = $dvr->end_time   ? Carbon::parse($dvr->end_time)->format('H:i')   : null;
        $meetDur  = ($dvr->start_time && $dvr->end_time)
            ? $this->diffHumanShort($dvr->start_time, $dvr->end_time) : null;

        // meeting minutes for % calc
        $meetMinutes = 0;
        if ($dvr->start_time && $dvr->end_time) {
            $meetMinutes = max(0, Carbon::parse($dvr->start_time)->diffInMinutes(Carbon::parse($dvr->end_time)));
        }

        $att        = $attendanceMap->get($dateKey);
        $attDisplay = $this->buildAttendanceDisplay($att);

        // Contacts met
        $contacts = $dvr->customerContacts
            ->filter(fn($cc) => $cc->customerContact !== null)
            ->map(fn($cc) => [
                'name'        => $cc->customerContact->name,
                'designation' => $cc->customerContact->designation,
                'department'  => $cc->customerContact->department ?? null,
                'mobile'      => $cc->customerContact->mobile_number,
            ])->values()->toArray();

        if (empty($contacts) && $dvr->customer_contact_info) {
            $c        = $dvr->customer_contact_info;
            $contacts = [[
                'name'        => $c->name,
                'designation' => $c->designation,
                'department'  => null,
                'mobile'      => $c->mobile_number,
            ]];
        }

        // Purposes
        $purposes = $dvr->purpose_of_visit
            ? array_values(array_filter(array_map('trim', explode(',,,', $dvr->purpose_of_visit))))
            : [];

        // Products
        $products = $dvr->products->map(fn($p) => optional($p->productinfo)->product_name)
            ->filter()->values()->toArray();

        // Trials
        $trialRows    = $this->buildTrialStatuses($dvr);
        $trialOverall = $this->overallTrialStatus($trialRows);

        // Visit detail (the longtext column)
        $visitDetail = $dvr->visit_detail ?? null;

        // Next plan & scheduler
        $schedulerInfo = null;
        if ($dvr->user_scheduler) {
            $s = $dvr->user_scheduler;
            $schedulerInfo = [
                'date'        => $s->scheduler_date ? Carbon::parse($s->scheduler_date)->format('d M Y') : null,
                'time'        => $s->scheduler_time ? Carbon::parse($s->scheduler_time)->format('H:i')   : null,
                'description' => $s->description,
                'status'      => $s->status,
            ];
        } elseif ($dvr->next_plan) {
            $schedulerInfo = [
                'date'        => null,
                'time'        => null,
                'description' => $dvr->next_plan,
                'status'      => null,
            ];
        }

        // ── STATUS BADGES (only blue & red per client request) ──
        $statuses = [];

        $statuses[] = [
            'label' => $dvr->visit_type ?? 'Official',
            'color' => '#22c55e',
            'group' => 'visit_type',
        ];

        $isReal = ($dvr->visit_recorded === 'On Site');
        $statuses[] = [
            'label' => $isReal ? 'Real Time Entry' : 'Post Visit Entry',
            'color' => $isReal ? '#22c55e' : '#ef4444',
            'group' => 'entry_type',
        ];

        if ($dvr->site_type) {
            $statuses[] = [
                'label' => $dvr->site_type,
                'color' => ($dvr->site_type === 'On Site') ? '#22c55e' : '#ef4444',
                'group' => 'site_type',
            ];
        }

        $met = (bool)$dvr->have_you_met;
        $statuses[] = [
            'label' => $met ? 'Met Customer' : 'No Meeting',
            'color' => $met ? '#22c55e' : '#ef4444',
            'group' => 'meeting',
        ];

        $submitted = (bool)$dvr->is_submitted;
        $statuses[] = [
            'label' => $submitted ? 'Visit Detail Added' : 'Visit Detail Pending',
            'color' => $submitted ? '#22c55e' : '#ef4444',
            'group' => 'detail',
        ];

        $verified = !is_null($dvr->dvr_verified_date_time);
        /*$statuses[] = [
            'label' => $verified ? 'Verified' : 'Not Verified',
            'color' => $verified ? '#22c55e' : '#ef4444',
            'group' => 'verification',
        ];*/

        return [
            'id'               => $dvr->id,
            'dvr_date_key'     => $dateKey,
            'dvr_date_display' => $dateDisp,
            'day_name'         => $dayName,
            'customer_name'    => $customerName ?? 'N/A',
            'customer_type'    => $customerType,
            'check_in'         => $checkIn,
            'check_out'        => $checkOut,
            'meeting_duration' => $meetDur,
            'meet_minutes'     => $meetMinutes,
            'attendance'       => $attDisplay,
            'contacts'         => $contacts,
            'purposes'         => $purposes,
            'products'         => $products,
            'visit_detail'     => $visitDetail,
            'scheduler'        => $schedulerInfo,
            'trials_count'     => $dvr->trials_count,
            'trial_rows'       => $trialRows,
            'trial_overall'    => $trialOverall,
            'attachments_count'=> $dvr->attachments->count(),
            'statuses'         => $statuses,
            'visit_type'       => $dvr->visit_type ?? 'Official',
            'verified'         => $verified,
            'remarks'          => $dvr->remarks,
            'next_plan'        => $dvr->next_plan,
            'other_purpose'    => $dvr->other_purpose,
        ];
    }

    private function buildTrialStatuses(UserDvr $dvr): array
    {
        if ($dvr->trials->isEmpty()) return [];
        return $dvr->trials->map(function ($trial) {
            if (!$trial->trial_done) {
                return ['label' => 'Trial Not Done', 'color' => '#ef4444', 'type' => $trial->trial_type];
            }
            $hasAtt = $trial->attachments->where('type', 'trial_report')->count() > 0;
            $hasId  = !is_null($trial->trial_report_id ?? null);
            if ($hasAtt || $hasId) {
                return ['label' => 'Report Attached', 'color' => '#22c55e', 'type' => $trial->trial_type];
            }
            return ['label' => 'Trial Report Pending', 'color' => '#ef4444', 'type' => $trial->trial_type];
        })->toArray();
    }

    private function overallTrialStatus(array $rows): array
    {
        if (empty($rows)) return ['label' => 'No Trials', 'color' => '#94a3b8'];
        $labels = array_column($rows, 'label');
        if (in_array('Trial Not Done', $labels))       return ['label' => 'Trial Not Done',       'color' => '#ef4444'];
        if (in_array('Trial Report Pending', $labels)) return ['label' => 'Trial Report Pending', 'color' => '#ef4444'];
        return ['label' => 'Report Attached', 'color' => '#22c55e'];
    }

    private function buildAttendanceDisplay($att): array
    {
        if (!$att) return ['exists' => false];
        return [
            'exists'       => true,
            'in_time'      => $att->in_time  ? Carbon::parse($att->in_time)->format('H:i:s')  : null,
            'out_time'     => $att->out_time ? Carbon::parse($att->out_time)->format('H:i:s') : null,
            'work_hours'   => $this->calcWorkHours($att),
            'status'       => $att->status,
            'status_color' => ($att->status === 'Present') ? '#22c55e' : '#ef4444',
        ];
    }

    private function calcWorkHours($att): string
    {
        if (!$att || !$att->in_time || !$att->out_time) return '0h 0m';
        $d = Carbon::parse($att->in_time)->diff(Carbon::parse($att->out_time));
        return $d->h . 'h ' . $d->i . 'm';
    }

    private function calcWorkMinutes($att): int
    {
        if (!$att || !$att->in_time || !$att->out_time) return 0;
        return max(0, Carbon::parse($att->in_time)->diffInMinutes(Carbon::parse($att->out_time)));
    }

    private function diffHumanShort($start, $end): string
    {
        $d = Carbon::parse($start)->diff(Carbon::parse($end));
        return $d->h . 'h ' . $d->i . 'm';
    }

    /**
     * Build per-customer stats: visit count %, total meeting time %.
     */
    private function buildCustomerStats($allDvrs): \Illuminate\Support\Collection
    {
        $total        = $allDvrs->count();
        $totalMinutes = $allDvrs->sum('meet_minutes');

        if ($total === 0) return collect();

        return $allDvrs
            ->groupBy('customer_name')
            ->map(function ($group, $name) use ($total, $totalMinutes) {
                $count   = $group->count();
                $minutes = $group->sum('meet_minutes');
                $h       = intdiv($minutes, 60);
                $m       = $minutes % 60;
                $th = intdiv($totalMinutes, 60);
                $tm = $totalMinutes % 60;
                return [
                    'name'               => $name,
                    'count'              => $count,
                    'visit_pct'          => $total > 0 ? round($count / $total * 100) : 0,
                    'time_minutes'       => $minutes,
                    'time_display'       => $h . 'h ' . $m . 'm',
                    'time_pct'           => $totalMinutes > 0 ? round($minutes / $totalMinutes * 100) : 0,
                    'total_dvrs'         => $total,
                    'total_minutes'      => $totalMinutes,
                    'total_time_display' => $th . 'h ' . $tm . 'm',
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Customer stats for filtered view — uses full-month totals as denominator
     * so "12/29 41%" shows correctly when only one customer is filtered
     */
    private function buildCustomerStatsWithTotal($filteredRows, int $totalAllDvrs, $allRows): \Illuminate\Support\Collection
    {
        if ($filteredRows->isEmpty()) return collect();

        $totalAllMinutes = $allRows->sum('meet_minutes');

        return $filteredRows
            ->groupBy('customer_name')
            ->map(function ($group, $name) use ($totalAllDvrs, $totalAllMinutes) {
                $count   = $group->count();
                $minutes = $group->sum('meet_minutes');
                $h       = intdiv($minutes, 60);
                $m       = $minutes % 60;
                $th      = intdiv($totalAllMinutes, 60);
                $tm      = $totalAllMinutes % 60;
                return [
                    'name'               => $name,
                    'count'              => $count,
                    'visit_pct'          => $totalAllDvrs > 0 ? round($count / $totalAllDvrs * 100) : 0,
                    'time_minutes'       => $minutes,
                    'time_display'       => $h . 'h ' . $m . 'm',
                    'time_pct'           => $totalAllMinutes > 0 ? round($minutes / $totalAllMinutes * 100) : 0,
                    'total_dvrs'         => $totalAllDvrs,
                    'total_minutes'      => $totalAllMinutes,
                    'total_time_display' => $th . 'h ' . $tm . 'm',
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Summary stat cards.
     */
    private function buildSummaryStats($allDvrs, $attendanceMap): array
    {
        $total       = $allDvrs->count();
        $verified    = $allDvrs->filter(fn($d) => $d['verified'])->count();
        $pending     = $total - $verified;
        $metCount    = $allDvrs->filter(fn($d) =>
            collect($d['statuses'])->pluck('label')->contains('Met Customer')
        )->count();
        $trialsSum   = $allDvrs->sum('trials_count');
        $presentDays = $attendanceMap->filter(fn($a) => $a->status === 'Present')->count();

        return compact('total', 'verified', 'pending', 'metCount', 'trialsSum', 'presentDays');
    }
}