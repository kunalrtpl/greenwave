<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\AttendanceStatus;
use App\UserAttendance;
use App\UserLeave;
use App\UserLeaveQuota;
use App\UserLeaveSetting;
use App\LeaveType;
use App\HolidayList;
use Carbon\Carbon;
use Session;
use PDF;

/**
 * AdminAttendanceController
 *
 * This controller manages the administrative view of employee attendance.
 * It handles the main attendance grid, quota lookups, manual status updates,
 * and PDF exporting. 
 *
 * ALL status strings are sourced from App\AttendanceStatus to maintain a 
 * single source of truth across the application.
 */
class AdminAttendanceController extends Controller
{
    /**
     * GET admin/attendance
     * Displays the attendance grid for employees.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        Session::put('active', 'attendance');

        // Initialize current date context
        $today = Carbon::today();
        $month = (int)$request->get('month', $today->month);
        $year  = (int)$request->get('year',  $today->year);

        // Determine the specific date filter if provided
        if (!$request->has('date')) {
            $filterDate = $today->toDateString();
        } elseif ($request->get('date') === '') {
            $filterDate = null;
        } else {
            $filterDate = $request->get('date');
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // ── Fetch Employees ──
        $empQuery = DB::table('users')->orderBy('name')
            ->select('id', 'name', 'mobile', 'base_city')
            ->where('type', 'employee')->where('status', 1)
            ->where('app_access', 'Yes')
            ->whereRaw("FIND_IN_SET('attendance', app_roles)");

        if ($request->filled('employee_id')) {
            $empQuery->where('id', $request->employee_id);
        }

        $employees = $empQuery->get();

        // Handle empty employee list gracefully
        if ($employees->isEmpty()) {
            return view('admin.user_attendance.index', [
                'employeeData'  => collect(),
                'employees'     => DB::table('users')->where('status', 1)->where('type', 'employee')->orderBy('name')->get(),
                'years'         => $this->getYears(),
                'month'         => $month, 'year' => $year,
                'filterDate'    => $filterDate,
                'statusOptions' => AttendanceStatus::ADMIN_OPTIONS,
                'today'         => $today->toDateString(),
            ]);
        }

        $employeeIds = $employees->pluck('id')->toArray();

        // ── Attendance records (date-filtered for UI display) ────────
        $baseQuery = DB::table('user_attendances as ua')
            ->leftJoin('customers as cin',               'ua.in_customer_id',                    '=', 'cin.id')
            ->leftJoin('customers as cout',              'ua.out_customer_id',                   '=', 'cout.id')
            ->leftJoin('customer_register_requests as rin',  'ua.in_customer_register_request_id',  '=', 'rin.id')
            ->leftJoin('customer_register_requests as rout', 'ua.out_customer_register_request_id', '=', 'rout.id')
            ->leftJoin('dealers as din',                 'ua.in_dealer_id',                     '=', 'din.id')
            ->leftJoin('dealers as dout',                'ua.out_dealer_id',                    '=', 'dout.id')
            ->leftJoin('users as chgby',                 'ua.status_changed_by',                '=', 'chgby.id')
            ->select(
                'ua.*',
                'cin.name   as in_customer_name',  'cout.name  as out_customer_name',
                'rin.name   as in_crr_name',       'rout.name  as out_crr_name',
                'din.name   as in_dealer_name',    'dout.name  as out_dealer_name',
                'chgby.name as changed_by_name'
            )
            ->whereIn('ua.user_id', $employeeIds)
            ->whereMonth('ua.in_date', $month)->whereYear('ua.in_date', $year)
            ->orderBy('ua.in_date', 'asc')->orderBy('ua.in_time', 'asc');

        if ($filterDate) $baseQuery->whereDate('ua.in_date', $filterDate);
        if ($request->filled('status')) $baseQuery->where('ua.status', $request->status);

        $attRecords = $baseQuery->get();

        // ── Full month records for statistical calculation (always unfiltered) ──────
        $fullMonthRecords = DB::table('user_attendances as ua')
            ->select('ua.user_id', 'ua.in_date', 'ua.status', 'ua.in_time')
            ->whereIn('ua.user_id', $employeeIds)
            ->whereMonth('ua.in_date', $month)->whereYear('ua.in_date', $year)
            ->orderBy('ua.in_date', 'asc')->get();

        // Group records by user and date for O(1) lookup during grid generation
        $fullMonthByUserDate = [];
        foreach ($fullMonthRecords as $rec) {
            $ds = Carbon::parse($rec->in_date)->toDateString();
            $fullMonthByUserDate[$rec->user_id][$ds][] = $rec;
        }

        $attByUserDate = [];
        foreach ($attRecords as $rec) {
            $ds = Carbon::parse($rec->in_date)->toDateString();
            $attByUserDate[$rec->user_id][$ds][] = $rec;
        }

        // ── Leaves linked to displayed attendance ─────────────────
        $attIds       = $attRecords->pluck('id')->toArray();
        $linkedLeaves = [];
        if (!empty($attIds)) {
            $leaves = DB::table('user_leaves as ul')
                ->join('leave_types as lt', 'ul.leave_type_id', '=', 'lt.id')
                ->whereIn('ul.attendance_id', $attIds)->where('ul.status', 'approved')
                ->select('ul.attendance_id', 'ul.quota_deducted', 'lt.name as lt_name', 'lt.code as lt_code', 'lt.color as lt_color')
                ->get();
            foreach ($leaves as $lv) $linkedLeaves[$lv->attendance_id] = $lv;
        }

        // ── Load all holidays (all cities) once, filter per employee ─
        $allHolidays = $this->getAllHolidaysForMonth($month, $year);

        // ── Batch-seed missing quotas for all employees ───────────
        $fy = $this->getFinancialYear(Carbon::create($year, $month, 1)->toDateString());
        $this->batchEnsureDefaultQuotas($employeeIds, $fy);

        // ── Define Date Grid Range ────────────────────────────────
        $limit        = $endOfMonth->lt($today) ? $endOfMonth : $today;
        $allMonthDates = [];
        for ($d = $startOfMonth->copy(); $d->lte($limit); $d->addDay()) {
            $allMonthDates[] = $d->toDateString();
        }

        $datesToShow = [];
        if ($filterDate) {
            $datesToShow[] = Carbon::parse($filterDate)->toDateString();
        } else {
            for ($d = $startOfMonth->copy(); $d->lte($limit); $d->addDay())          $datesToShow[] = $d->toDateString();
            for ($d = $today->copy()->addDay(); $d->lte($endOfMonth); $d->addDay())  $datesToShow[] = $d->toDateString();
        }

        $employeeData = collect();

        foreach ($employees as $emp) {
            $holidays     = $this->filterHolidaysForCity($allHolidays, $emp->base_city, $month, $year);
            $empDates     = [];
            $presentCount = $leaveCount = $lwpCount = $compOffCount = $workingDays = 0;

            // Calculate Stats based on the full month
            foreach ($allMonthDates as $ds) {
                $dc        = Carbon::parse($ds);
                $isSunday  = ($dc->dayOfWeek === 0);
                $isHoliday = isset($holidays[$ds]);

                if (!$isSunday && !$isHoliday) $workingDays++;

                $monthRecs = $fullMonthByUserDate[$emp->id][$ds] ?? [];
                if (!empty($monthRecs)) {
                    $mainRec = end($monthRecs);
                    $status  = $mainRec->status ?? AttendanceStatus::PRESENT;
                } elseif ($isHoliday)      { $status = AttendanceStatus::HOLIDAY; }
                elseif ($isSunday)         { $status = AttendanceStatus::WEEKLY_OFF; }
                elseif ($dc->isToday())    { $status = 'Not Punched Yet'; }
                else                       { $status = AttendanceStatus::LWP_UNINF; }

                // Use AttendanceStatus weight helpers for accurate fractional counting (e.g., 0.5 for half days)
                $presentCount += AttendanceStatus::presentWeight($status);
                $leaveCount   += AttendanceStatus::leaveWeight($status);
                $lwpCount     += AttendanceStatus::lwpWeight($status);
                if ($status === AttendanceStatus::COMP_OFF) $compOffCount++;
            }

            // Build Display rows for the UI grid
            foreach ($datesToShow as $ds) {
                $dc          = Carbon::parse($ds);
                $isSunday    = ($dc->dayOfWeek === 0);
                $isHoliday   = isset($holidays[$ds]);
                $holidayName = isset($holidays[$ds]) ? $holidays[$ds]->name : null;
                $isPast      = $dc->lt($today);
                $isToday     = $dc->isToday();
                $isFuture    = $dc->gt($today);

                $records = $attByUserDate[$emp->id][$ds] ?? [];

                if (!empty($records)) {
                    $mainRecord     = end($records);
                    $computedStatus = $mainRecord->status ?? AttendanceStatus::PRESENT;

                    // FIXED: Replaced PHP 7.4 Arrow Function (fn) with standard anonymous function for compatibility
                    if (AttendanceStatus::isHalfDay($computedStatus)) {
                        $hasPunch = collect($records)->filter(function($r) {
                            return !is_null($r->in_time);
                        })->isNotEmpty();
                        
                        $resynced = AttendanceStatus::resyncHalfDayStatus($computedStatus, $hasPunch, $isFuture);
                        if ($resynced) {
                            DB::table('user_attendances')
                                ->where('user_id', $emp->id)->whereDate('in_date', $ds)
                                ->update(['status' => $resynced]);
                            $computedStatus = $resynced;
                        }
                    }
                } elseif ($isHoliday)  { $computedStatus = AttendanceStatus::HOLIDAY;    $mainRecord = null; }
                elseif ($isSunday)     { $computedStatus = AttendanceStatus::WEEKLY_OFF;  $mainRecord = null; }
                elseif ($isToday)      { $computedStatus = 'Not Punched Yet';             $mainRecord = null; }
                elseif ($isPast)       { $computedStatus = AttendanceStatus::LWP_UNINF;   $mainRecord = null; }
                else                   { $computedStatus = null;                          $mainRecord = null; }

                // Calculate total duration for the day
                $duration = null;
                if (!empty($records)) {
                    $totalMins = 0;
                    foreach ($records as $r) {
                        if ($r->in_time && $r->out_time) {
                            try {
                                $inDt  = Carbon::parse($r->in_date . ' ' . $r->in_time);
                                $outDt = Carbon::parse(($r->out_date ?? $r->in_date) . ' ' . $r->out_time);
                                $totalMins += $inDt->diffInMinutes($outDt);
                            } catch (\Exception $e) {}
                        }
                    }
                    if ($totalMins > 0) $duration = floor($totalMins/60).'h '.($totalMins%60).'m';
                }

                $leaveInfo = null;
                if ($mainRecord && isset($linkedLeaves[$mainRecord->id])) {
                    $leaveInfo = $linkedLeaves[$mainRecord->id];
                }

                $empDates[] = [
                    'date'         => $ds,
                    'carbon'       => $dc,
                    'is_sunday'    => $isSunday,
                    'is_holiday'   => $isHoliday,
                    'holiday_name' => $holidayName,
                    'is_past'      => $isPast,
                    'is_today'     => $isToday,
                    'is_future'    => $isFuture,
                    'records'      => $records,
                    'main_record'  => $mainRecord,
                    'status'       => $computedStatus,
                    'duration'     => $duration,
                    'has_record'   => !empty($records),
                    'is_open'      => $mainRecord && is_null($mainRecord->out_time) && !$mainRecord->missed,
                    'leave_info'   => $leaveInfo,
                    'badge_css'    => AttendanceStatus::badgeCss($computedStatus),
                    'bar_css'      => AttendanceStatus::barCss($computedStatus),
                ];
            }

            $employeeData->push([
                'employee'       => $emp,
                'dates'          => $empDates,
                'present_count'  => round($presentCount, 1),
                'leave_count'    => round($leaveCount, 1),
                'lwp_count'      => round($lwpCount, 1),
                'comp_off_count' => $compOffCount,
                'working_days'   => $workingDays,
            ]);
        }

        $allEmployees = DB::table('users')->select('id', 'name', 'mobile')
            ->where('status', 1)->where('type', 'employee')->orderBy('name')->get();

        return view('admin.user_attendance.index', compact(
            'employeeData', 'allEmployees', 'month', 'year', 'filterDate'
        ) + [
            'employees'     => $allEmployees,
            'years'         => $this->getYears(),
            'statusOptions' => AttendanceStatus::ADMIN_OPTIONS,
            'today'         => $today->toDateString(),
            'title'         => 'Attendance',
        ]);
    }

    /**
     * GET admin/attendance/quota-info
     * Fetches leave quota information for the status-change modal.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuotaInfo(Request $request)
    {
        $userId       = $request->user_id;
        $date         = $request->date;
        $attendanceId = $request->attendance_id;

        if (!$userId || !$date) {
            return response()->json(['success' => false, 'message' => 'Missing parameters.']);
        }

        $fy         = $this->getFinancialYear($date);
        $leaveTypes = LeaveType::where('is_active', true)->where('has_quota', true)->orderBy('sort_order')->get();

        $quotaData = $leaveTypes->map(function ($lt) use ($userId, $fy) {
            $quota = UserLeaveQuota::where('user_id', $userId)
                ->where('leave_type_id', $lt->id)->where('financial_year', $fy)->first();
            return [
                'id'        => $lt->id,
                'name'      => $lt->name,
                'code'      => $lt->code,
                'color'     => $lt->color,
                'total'     => $quota ? (float)$quota->total_quota : 0,
                'used'      => $quota ? (float)$quota->used_quota  : 0,
                'remaining' => $quota ? max(0, $quota->total_quota - $quota->used_quota) : 0,
            ];
        });

        // Add ML (no-quota / unlimited) manually to the list
        $mlType = LeaveType::where('code', 'ML')->where('is_active', true)->first();
        if ($mlType) {
            $quotaData->push([
                'id' => $mlType->id, 'name' => $mlType->name, 'code' => 'ML',
                'color' => $mlType->color, 'total' => null, 'used' => null, 'remaining' => null,
            ]);
        }

        $existingLeave = null;
        if ($attendanceId) {
            $existingLeave = UserLeave::where('attendance_id', $attendanceId)
                ->where('status', 'approved')->with('leaveType')->first();
        }

        return response()->json([
            'success'        => true,
            'quota'          => $quotaData,
            'financial_year' => $fy,
            'existing_leave' => $existingLeave,
        ]);
    }

    /**
     * POST admin/attendance/{id}/update-status
     * Manually overrides the attendance status of a record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $attendance = UserAttendance::find($id);
        if (!$attendance) return response()->json(['success' => false, 'message' => 'Not found.'], 404);

        $request->validate([
            'new_status'    => 'required|string',
            'leave_type_id' => 'nullable|integer|exists:leave_types,id',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $adminId    = auth()->id();
        $newStatus  = $request->new_status;
        $userId     = $attendance->user_id;
        $date       = $attendance->in_date;
        $fy         = $this->getFinancialYear($date);
        $isHalfDay  = in_array($newStatus, [AttendanceStatus::HALF_LEAVE, AttendanceStatus::HALF_DAY_LEAVE, AttendanceStatus::HALF_LEAVE_LWP]);
        $deduct     = $isHalfDay ? 0.5 : 1.0;
        $isNewLeave = AttendanceStatus::requiresQuotaPicker($newStatus);

        // ── Get old status from the FIRST record for this date to track state change ───
        $firstRecord = UserAttendance::where('user_id', $userId)
            ->whereDate('in_date', $date)->orderBy('id')->first();
        $oldStatus = $firstRecord ? $firstRecord->status : $attendance->status;

        DB::beginTransaction();
        try {
            // Restore old quota if previous status was a leave status
            if (AttendanceStatus::requiresQuotaPicker($oldStatus)) {
                $oldLeave = UserLeave::where('attendance_id', $id)
                    ->where('status', 'approved')->first();
                if (!$oldLeave) {
                    // Try any leave linked to any attendance on that date (defensive check)
                    $anyAttIds = UserAttendance::where('user_id', $userId)
                        ->whereDate('in_date', $date)->pluck('id');
                    $oldLeave  = UserLeave::whereIn('attendance_id', $anyAttIds)
                        ->where('status', 'approved')->first();
                }
                if ($oldLeave) {
                    $oldLT = LeaveType::find($oldLeave->leave_type_id);
                    if ($oldLT && $oldLT->has_quota) {
                        UserLeaveQuota::where('user_id', $userId)
                            ->where('leave_type_id', $oldLeave->leave_type_id)
                            ->where('financial_year', $fy)
                            ->decrement('used_quota', $oldLeave->quota_deducted);
                    }
                    // Cancel all existing leaves for this specific date
                    UserLeave::where('user_id', $userId)
                        ->whereDate('date', $date)->where('status', 'approved')
                        ->update(['status' => 'cancelled']);
                }
            }

            // Deduct new quota if the new status is a leave status
            if ($isNewLeave) {
                if (!$request->leave_type_id) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Please select a leave type.'], 422);
                }
                $newLT = LeaveType::find($request->leave_type_id);
                if ($newLT && $newLT->has_quota) {
                    $quota = UserLeaveQuota::where('user_id', $userId)
                        ->where('leave_type_id', $request->leave_type_id)
                        ->where('financial_year', $fy)->first();
                    if ($quota) {
                        $quota->increment('used_quota', $deduct);
                    } else {
                        UserLeaveQuota::create([
                            'user_id' => $userId, 'leave_type_id' => $request->leave_type_id,
                            'financial_year' => $fy, 'total_quota' => 0, 'used_quota' => $deduct,
                        ]);
                    }
                }

                // Create a new Leave entry linked to the primary attendance record
                UserLeave::create([
                    'user_id'        => $userId,
                    'leave_type_id'  => $request->leave_type_id,
                    'date'           => $date,
                    'leave_duration' => $isHalfDay ? 'half_day' : 'full_day',
                    'half_day_type'  => null,
                    'remarks'        => $request->admin_remarks ?? 'Updated by admin',
                    'status'         => 'approved',
                    'attendance_id'  => $id,
                    'quota_deducted' => $deduct,
                ]);
            }

            $adminName  = DB::table('users')->where('id', $adminId)->value('name') ?? 'Admin';
            $changeNote = "Status changed from '{$oldStatus}' to '{$newStatus}' by {$adminName}";
            if ($request->admin_remarks) $changeNote .= " — {$request->admin_remarks}";

            // ── Update ALL attendance records for this user+date to keep UI consistent ──────────
            UserAttendance::where('user_id', $userId)
                ->whereDate('in_date', $date)
                ->update([
                    'status'             => $newStatus,
                    'previous_status'    => $oldStatus,
                    'status_changed_by'  => $adminId,
                    'status_change_note' => $changeNote,
                    'status_changed_at'  => now(),
                ]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Status updated.',
                'new_status'  => $newStatus,
                'old_status'  => $oldStatus,
                'change_note' => $changeNote,
                'badge_css'   => AttendanceStatus::badgeCss($newStatus),
                'bar_css'     => AttendanceStatus::barCss($newStatus),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST admin/attendance/create-record
     * Creates a manual attendance record (e.g., for marking leave where no punch exists).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRecord(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'date'          => 'required|date',
            'new_status'    => 'required|string',
            'leave_type_id' => 'nullable|integer|exists:leave_types,id',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $adminId    = auth()->id();
        $userId     = $request->user_id;
        $date       = $request->date;
        $newStatus  = $request->new_status;
        $fy         = $this->getFinancialYear($date);
        $isHalfDay  = in_array($newStatus, [AttendanceStatus::HALF_LEAVE, AttendanceStatus::HALF_DAY_LEAVE, AttendanceStatus::HALF_LEAVE_LWP]);
        $isNewLeave = AttendanceStatus::requiresQuotaPicker($newStatus);
        $deduct     = $isHalfDay ? 0.5 : 1.0;
        $adminName  = DB::table('users')->where('id', $adminId)->value('name') ?? 'Admin';

        DB::beginTransaction();
        try {
            $attendance = UserAttendance::create([
                'user_id'             => $userId,
                'in_date'             => $date,
                'in_time'             => null,
                'status'              => $newStatus,
                'previous_status'     => null,
                'status_changed_by'   => $adminId,
                'status_change_note'  => "Status set to '{$newStatus}' by {$adminName}",
                'status_changed_at'   => now(),
            ]);

            if ($isNewLeave && $request->leave_type_id) {
                $newLT = LeaveType::find($request->leave_type_id);
                if ($newLT && $newLT->has_quota) {
                    $quota = UserLeaveQuota::where('user_id', $userId)
                        ->where('leave_type_id', $request->leave_type_id)
                        ->where('financial_year', $fy)->first();
                    if ($quota) {
                        $quota->increment('used_quota', $deduct);
                    } else {
                        UserLeaveQuota::create([
                            'user_id' => $userId, 'leave_type_id' => $request->leave_type_id,
                            'financial_year' => $fy, 'total_quota' => 0, 'used_quota' => $deduct,
                        ]);
                    }
                }
                UserLeave::create([
                    'user_id'        => $userId,
                    'leave_type_id'  => $request->leave_type_id,
                    'date'           => $date,
                    'leave_duration' => $isHalfDay ? 'half_day' : 'full_day',
                    'half_day_type'  => null,
                    'remarks'        => $request->admin_remarks ?? 'Set by admin',
                    'status'         => 'approved',
                    'attendance_id'  => $attendance->id,
                    'quota_deducted' => $deduct,
                ]);
            }

            DB::commit();
            return response()->json([
                'success'       => true,
                'message'       => 'Record created.',
                'new_status'    => $newStatus,
                'attendance_id' => $attendance->id,
                'badge_css'     => AttendanceStatus::badgeCss($newStatus),
                'bar_css'       => AttendanceStatus::barCss($newStatus),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET admin/attendance/export-pdf/{employeeId?}
     * Generates a monthly landscape PDF report for one or all employees.
     *
     * @param Request $request
     * @param int|null $employeeId
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request, $employeeId = null)
    {
        $today = Carbon::today();
        $month = (int)$request->get('month', $today->month);
        $year  = (int)$request->get('year',  $today->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $empQuery = DB::table('users')->select('id', 'name', 'mobile', 'base_city')
            ->where('status', 1)->where('type', 'employee')->orderBy('name');
        if ($employeeId)                      $empQuery->where('id', $employeeId);
        elseif ($request->filled('employee_id')) $empQuery->where('id', $request->employee_id);

        $employees   = $empQuery->get();
        $employeeIds = $employees->pluck('id')->toArray();
        if ($employees->isEmpty()) abort(404, 'Employee not found.');

        $leaveTypes  = LeaveType::where('is_active', true)->orderBy('sort_order')->get();
        $allHolidays = $this->getAllHolidaysForMonth($month, $year);
        $fy          = $this->getFinancialYear(Carbon::create($year, $month, 1)->toDateString());

        $attRecords = DB::table('user_attendances as ua')
            ->leftJoin('customers as cin',  'ua.in_customer_id',  '=', 'cin.id')
            ->leftJoin('customers as cout', 'ua.out_customer_id', '=', 'cout.id')
            ->leftJoin('users as chgby',    'ua.status_changed_by', '=', 'chgby.id')
            ->select('ua.*', 'cin.name as in_customer_name', 'cout.name as out_customer_name', 'chgby.name as changed_by_name')
            ->whereIn('ua.user_id', $employeeIds)
            ->whereMonth('ua.in_date', $month)->whereYear('ua.in_date', $year)
            ->orderBy('ua.in_date', 'asc')->orderBy('ua.in_time', 'asc')->get();

        $attByUserDate = [];
        foreach ($attRecords as $rec) {
            $ds = Carbon::parse($rec->in_date)->toDateString();
            $attByUserDate[$rec->user_id][$ds][] = $rec;
        }

        $datesToShow = [];
        $limit = $endOfMonth->lt($today) ? $endOfMonth : $today;
        for ($d = $startOfMonth->copy(); $d->lte($limit); $d->addDay())         $datesToShow[] = $d->toDateString();
        for ($d = $today->copy()->addDay(); $d->lte($endOfMonth); $d->addDay()) $datesToShow[] = $d->toDateString();

        $employeeData = collect();

        foreach ($employees as $emp) {
            $holidays     = $this->filterHolidaysForCity($allHolidays, $emp->base_city, $month, $year);
            $empDates     = [];
            $presentCount = $leaveCount = $lwpCount = $compOffCount = $workingDays = 0;

            foreach ($datesToShow as $ds) {
                $dc       = Carbon::parse($ds);
                $isSunday  = ($dc->dayOfWeek === 0);
                $isHoliday = isset($holidays[$ds]);
                $isPast    = $dc->lt($today);
                $isToday   = $dc->isToday();
                $isFuture  = $dc->gt($today);

                if (!$isSunday && !$isHoliday && !$isFuture) $workingDays++;

                $records = $attByUserDate[$emp->id][$ds] ?? [];

                if (!empty($records)) {
                    $mainRecord     = end($records);
                    $computedStatus = $mainRecord->status ?? AttendanceStatus::PRESENT;
                } elseif ($isHoliday) { $computedStatus = AttendanceStatus::HOLIDAY;    $mainRecord = null; }
                elseif ($isSunday)    { $computedStatus = AttendanceStatus::WEEKLY_OFF; $mainRecord = null; }
                elseif ($isToday)     { $computedStatus = 'Not Punched Yet';            $mainRecord = null; }
                elseif ($isFuture)    { $computedStatus = null;                         $mainRecord = null; }
                else                  { $computedStatus = AttendanceStatus::LWP_UNINF;  $mainRecord = null; }

                $totalMins = 0;
                foreach ($records as $r) {
                    if ($r->in_time && $r->out_time) {
                        try {
                            $inDt  = Carbon::parse($r->in_date.' '.$r->in_time);
                            $outDt = Carbon::parse(($r->out_date ?? $r->in_date).' '.$r->out_time);
                            $totalMins += $inDt->diffInMinutes($outDt);
                        } catch (\Exception $e) {}
                    }
                }
                $duration = $totalMins > 0 ? floor($totalMins/60).'h '.($totalMins%60).'m' : null;

                $presentCount += AttendanceStatus::presentWeight($computedStatus ?? '');
                $leaveCount   += AttendanceStatus::leaveWeight($computedStatus ?? '');
                $lwpCount     += AttendanceStatus::lwpWeight($computedStatus ?? '');
                if ($computedStatus === AttendanceStatus::COMP_OFF) $compOffCount++;

                $empDates[] = [
                    'ds'             => $ds,
                    'dc'             => $dc,
                    'records'        => $records,
                    'mainRecord'     => $mainRecord,
                    'computedStatus' => $computedStatus,
                    'duration'       => $duration,
                    'isSunday'       => $isSunday,
                    'isHoliday'      => $isHoliday,
                    'isFuture'       => $isFuture,
                    'isToday'        => $isToday,
                    'holiday_name'   => $holidays[$ds]->name ?? null,
                ];
            }

            // Get per-employee quota details for the PDF summary
            $quotaDetails = [];
            foreach ($leaveTypes as $lt) {
                if ($lt->has_quota) {
                    $quota = UserLeaveQuota::where('user_id', $emp->id)
                        ->where('leave_type_id', $lt->id)->where('financial_year', $fy)->first();
                    $quotaDetails[] = [
                        'code'      => $lt->code,
                        'name'      => $lt->name,
                        'total'     => $quota ? (float)$quota->total_quota : 0.0,
                        'used'      => $quota ? (float)$quota->used_quota  : 0.0,
                        'remaining' => $quota ? max(0, (float)$quota->total_quota - (float)$quota->used_quota) : 0.0,
                        'unlimited' => false,
                    ];
                } else {
                    $quotaDetails[] = [
                        'code' => $lt->code, 'name' => $lt->name,
                        'total' => null, 'used' => null, 'remaining' => null, 'unlimited' => true,
                    ];
                }
            }

            $employeeData->push([
                'employee'       => $emp,
                'dates'          => $empDates,
                'present_count'  => round($presentCount, 1),
                'leave_count'    => round($leaveCount, 1),
                'lwp_count'      => round($lwpCount, 1),
                'comp_off_count' => $compOffCount,
                'working_days'   => $workingDays,
                'financial_year' => $fy,
                'quota_details'  => $quotaDetails,
            ]);
        }

        $monthName   = Carbon::create($year, $month, 1)->format('F Y');
        $filterLabel = "Full Month — {$monthName}";
        $generatedAt = Carbon::now()->format('d M Y, h:i A');

        $view = view('admin.user_attendance.pdf', compact(
            'employeeData', 'monthName', 'filterLabel', 'generatedAt', 'month', 'year'
        ));

        $pdf = PDF::loadHTML($view->render())
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'          => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'dpi'                  => 120,
            ]);

        $empName  = $employees->count() === 1
            ? preg_replace('/[^A-Za-z0-9_]/', '_', $employees->first()->name)
            : 'All_Employees';

        return $pdf->download("Attendance_{$empName}_{$monthName}.pdf");
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Determines the Financial Year string (e.g. 2024-25) for a given date.
     * Assuming FY starts in April.
     */
    protected function getFinancialYear($date): string
    {
        $d = Carbon::parse($date);
        return $d->month >= 4
            ? $d->year . '-' . substr($d->year + 1, -2)
            : ($d->year - 1) . '-' . substr($d->year, -2);
    }

    /**
     * Generates an array of the current year and the previous 3 years.
     */
    protected function getYears(): array
    {
        $y = [];
        for ($i = date('Y'); $i >= date('Y') - 3; $i--) $y[] = $i;
        return $y;
    }

    /**
     * Load ALL active holidays for the month regardless of city.
     * Returns a collection of holiday objects.
     */
    protected function getAllHolidaysForMonth(int $month, int $year): \Illuminate\Support\Collection
    {
        $monthPad = sprintf('%02d', $month);
        return HolidayList::where('is_active', true)
            ->where(function ($q) use ($month, $year, $monthPad) {
                $q->where(function ($i) use ($month, $year) {
                    $i->whereMonth('date', $month)->whereYear('date', $year);
                })->orWhere(function ($i) use ($monthPad) {
                    $i->where('is_recurring', true)->whereRaw("DATE_FORMAT(date,'%m')=?", [$monthPad]);
                });
            })
            ->select('id', 'name', 'date', 'city', 'is_national', 'is_recurring')
            ->get();
    }

    /**
     * Filter the full holiday collection to only those relevant for a specific city.
     */
    protected function filterHolidaysForCity(\Illuminate\Support\Collection $all, ?string $city, int $month, int $year): array
    {
        $map = [];
        foreach ($all as $h) {
            // Include if national OR matches employee's base city
            if (!$h->is_national && strtolower($h->city) !== strtolower($city)) continue;

            $ds = $h->is_recurring
                ? ($year . '-' . sprintf('%02d', $month) . '-' . Carbon::parse($h->date)->format('d'))
                : Carbon::parse($h->date)->toDateString();
            $map[$ds] = $h;
        }
        return $map;
    }

    /**
     * Batch-ensure default quota rows for multiple users.
     * Uses a bulk INSERT to avoid N+1 issues when viewing the index.
     */
    protected function batchEnsureDefaultQuotas(array $userIds, string $fy): void
    {
        $leaveTypes = LeaveType::where('is_active', true)->where('has_quota', true)->get();
        if ($leaveTypes->isEmpty() || empty($userIds)) return;

        // Load existing quota rows to avoid duplicates
        $existing = UserLeaveQuota::whereIn('user_id', $userIds)
            ->where('financial_year', $fy)
            ->get(['user_id', 'leave_type_id'])
            ->groupBy('user_id');

        $toCreate = [];
        $now      = now()->toDateTimeString();

        foreach ($userIds as $uid) {
            $userExisting = $existing->get($uid);
            $existingTypes = $userExisting ? $userExisting->pluck('leave_type_id')->toArray() : [];
            
            foreach ($leaveTypes as $lt) {
                if (in_array($lt->id, $existingTypes)) continue;

                // Determine default quota from user-specific settings or type global default
                $isEL    = $lt->code === 'EL';
                $setting = UserLeaveSetting::where('user_id', $uid)
                    ->where('leave_type_id', $lt->id)->where('financial_year', $fy)->first();

                $total = $setting
                    ? (float)($setting->annual_quota ?? 0)
                    : ($isEL ? 5.0 : (float)($lt->default_quota ?? 0));

                $toCreate[] = [
                    'user_id'        => $uid,
                    'leave_type_id'  => $lt->id,
                    'financial_year' => $fy,
                    'total_quota'    => $total,
                    'used_quota'     => 0,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        if ($toCreate) {
            // Batch insert missing rows
            DB::table('user_leave_quotas')->insertOrIgnore($toCreate);
        }
    }
}