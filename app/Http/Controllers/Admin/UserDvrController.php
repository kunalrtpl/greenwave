<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\UserDvr;
use App\User;
use App\UserAttendance;
use Carbon\Carbon;

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

        if (!$request->filled('user_id')) {
            return view('admin.dvrs.index', compact('users', 'statusFilters', 'title'))
                ->with('groupedDvrs', collect())
                ->with('paginator', null)
                ->with('summaryStats', null)
                ->with('attendanceMap', collect());
        }

        $query = UserDvr::with([
            'user:id,name',
            'customer:id,name,activity,category,mobile',
            'customer_register_request:id,name,activity,category,mobile,status',
            'customer_contact_info:id,name,designation,mobile_number',
            'customerContacts.customerContact:id,name,designation,department,mobile_number',
            'products.productinfo:id,product_name',
            'trials.attachments',
            'attachments',
        ])
        ->withCount('trials')
        ->where('user_id', $request->user_id)
        ->orderBy('dvr_date', 'desc')
        ->orderBy('id', 'desc');

        if ($request->filled('month')) $query->whereMonth('dvr_date', $request->month);
        if ($request->filled('year'))  $query->whereYear('dvr_date', $request->year);

        $allDvrs       = $query->get();
        $dvrDates      = $allDvrs->pluck('dvr_date')->unique()->values();
        $attendanceMap = UserAttendance::where('user_id', $request->user_id)
            ->whereIn('in_date', $dvrDates)
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->in_date)->format('Y-m-d'));

        $allDvrs = $allDvrs->map(fn($dvr) => $this->buildDvrRow($dvr, $attendanceMap));

        if ($request->filled('status_filter')) {
            $sf      = $request->status_filter;
            $allDvrs = $allDvrs->filter(function ($dvr) use ($sf) {
                $labels = collect($dvr['statuses'])->pluck('label');
                $trialLabel = $dvr['trial_overall']['label'];
                return $labels->contains($sf) || $trialLabel === $sf;
            })->values();
        }

        if ($request->filled('visit_type')) {
            $allDvrs = $allDvrs->filter(fn($d) => $d['visit_type'] === $request->visit_type)->values();
        }

        $summaryStats = $this->buildSummaryStats($allDvrs, $attendanceMap);

        $perPage  = 20;
        $page     = (int) $request->get('page', 1);
        $total    = $allDvrs->count();
        $pageDvrs = $allDvrs->forPage($page, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageDvrs, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $groupedDvrs = $pageDvrs->groupBy('dvr_date_key');

        return view('admin.dvrs.index', compact(
            'groupedDvrs', 'paginator', 'users',
            'summaryStats', 'statusFilters', 'attendanceMap', 'title'
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
     *  PRIVATE HELPERS
     * ═══════════════════════════════════════════════ */

    private function getEmployeeList()
    {
        return User::whereIn('id', UserDvr::select('user_id')->distinct())
            ->orderBy('name')->get(['id', 'name']);
    }

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

        $att        = $attendanceMap->get($dateKey);
        $attDisplay = $this->buildAttendanceDisplay($att);

        $contacts = $dvr->customerContacts
            ->filter(fn($cc) => $cc->customerContact !== null)
            ->map(fn($cc) => [
                'name'        => $cc->customerContact->name,
                'designation' => $cc->customerContact->designation,
                'department'  => $cc->customerContact->department ?? null,
                'mobile'      => $cc->customerContact->mobile_number,
            ])->values()->toArray();

        if (empty($contacts) && $dvr->customer_contact_info) {
            $c = $dvr->customer_contact_info;
            $contacts = [[
                'name'        => $c->name,
                'designation' => $c->designation,
                'department'  => null,
                'mobile'      => $c->mobile_number,
            ]];
        }

        $purposes = $dvr->purpose_of_visit
            ? array_values(array_filter(array_map('trim', explode(',,,', $dvr->purpose_of_visit))))
            : [];

        $products = $dvr->products->map(fn($p) => optional($p->productinfo)->product_name)
            ->filter()->values()->toArray();

        $trialRows    = $this->buildTrialStatuses($dvr);
        $trialOverall = $this->overallTrialStatus($trialRows);

        // ── STATUS BADGES ─────────────────────────────────
        $statuses = [];

        $statuses[] = ['label' => $dvr->visit_type ?? 'Official',
            'color' => ($dvr->visit_type === 'Official') ? '#3b82f6' : '#6b7280',
            'group' => 'visit_type'];

        $isReal = ($dvr->visit_recorded === 'On Site');
        $statuses[] = ['label' => $isReal ? 'Real Time Entry' : 'Post Visit Entry',
            'color' => $isReal ? '#10b981' : '#f59e0b', 'group' => 'entry_type'];

        if ($dvr->site_type) {
            $statuses[] = ['label' => $dvr->site_type,
                'color' => ($dvr->site_type === 'On Site') ? '#06b6d4' : '#6b7280',
                'group' => 'site_type'];
        }

        $met = (bool) $dvr->have_you_met;
        $statuses[] = ['label' => $met ? 'Met Customer' : 'No Meeting',
            'color' => $met ? '#10b981' : '#ef4444', 'group' => 'meeting'];

        $submitted = (bool) $dvr->is_submitted;
        $statuses[] = ['label' => $submitted ? 'Visit Detail Added' : 'Visit Detail Pending',
            'color' => $submitted ? '#10b981' : '#f59e0b', 'group' => 'detail'];

        $verified = !is_null($dvr->dvr_verified_date_time);
        /*$statuses[] = ['label' => $verified ? 'Verified' : 'Not Verified',
            'color' => $verified ? '#10b981' : '#f59e0b', 'group' => 'verification']*/;

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
            'attendance'       => $attDisplay,
            'contacts'         => $contacts,
            'purposes'         => $purposes,
            'products'         => $products,
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
                return ['label' => 'Report Attached', 'color' => '#10b981', 'type' => $trial->trial_type];
            }
            return ['label' => 'Trial Report Pending', 'color' => '#f59e0b', 'type' => $trial->trial_type];
        })->toArray();
    }

    private function overallTrialStatus(array $rows): array
    {
        if (empty($rows)) return ['label' => 'No Trials', 'color' => '#9ca3af'];
        $labels = array_column($rows, 'label');
        if (in_array('Trial Not Done', $labels))        return ['label' => 'Trial Not Done',       'color' => '#ef4444'];
        if (in_array('Trial Report Pending', $labels))  return ['label' => 'Trial Report Pending', 'color' => '#f59e0b'];
        return ['label' => 'Report Attached', 'color' => '#10b981'];
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
            'status_color' => ($att->status === 'Present') ? '#10b981' : '#ef4444',
        ];
    }

    private function calcWorkHours($att): string
    {
        if (!$att || !$att->in_time || !$att->out_time) return '0h 0m';
        $d = Carbon::parse($att->in_time)->diff(Carbon::parse($att->out_time));
        return $d->h . 'h ' . $d->i . 'm';
    }

    private function diffHumanShort($start, $end): string
    {
        $d = Carbon::parse($start)->diff(Carbon::parse($end));
        return $d->h . 'h ' . $d->i . 'm';
    }

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