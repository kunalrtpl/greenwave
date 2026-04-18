<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UserAttendance;
use App\UserLeave;
use App\UserLeaveQuota;
use App\UserLeaveSetting;
use App\LeaveType;
use App\HolidayList;
use Carbon\Carbon;
use Session;
use PDF; // barryvdh/laravel-dompdf

class AdminAttendanceController extends Controller
{
    const ST_PRESENT     = 'Full Day Present';
    const ST_HALF_LWP    = '1/2 Present + 1/2 LWP';
    const ST_HALF_LEAVE  = '1/2 Present + 1/2 Leave';
    const ST_FULL_LEAVE  = 'Allowed Full Day Leave';
    const ST_LWP_UNINF   = 'LWP (Uninformed Absence)';
    const ST_LWP_UNAPP   = 'LWP (Unapproved Leave)';
    const ST_LWP_EXCESS  = 'LWP (Leave in excess of quota)';
    const ST_HOLIDAY     = 'Allowed Holiday';
    const ST_COMP_OFF    = 'Compensatory Weekly Off';
    const ST_WEEKLY_OFF  = 'Weekly Off';

    const LEAVE_STATUSES = [self::ST_HALF_LEAVE, self::ST_FULL_LEAVE];
    const LWP_STATUSES   = [self::ST_LWP_UNINF, self::ST_LWP_UNAPP, self::ST_LWP_EXCESS, self::ST_HALF_LWP];

    // ─────────────────────────────────────────────────────────────
    // GET  admin/attendance
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        Session::put('active', 'attendance');

        $today = Carbon::today();
        $month = (int) $request->get('month', $today->month);
        $year  = (int) $request->get('year',  $today->year);

        if (!$request->has('date')) {
            $filterDate = $today->toDateString();
        } elseif ($request->get('date') === '') {
            $filterDate = null;
        } else {
            $filterDate = $request->get('date');
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $empQuery = DB::table('users')->orderBy('name')
            ->select('id','name', 'mobile', 'base_city')
            ->where('type','employee')
            ->where('status', 1)
            ->where('app_access', 'Yes')
            ->whereRaw("FIND_IN_SET('attendance', app_roles)");

        if ($request->filled('employee_id')) {
            $empQuery->where('id', $request->employee_id);
        }

        $employees = $empQuery->get();

        if ($employees->isEmpty()) {
            return view('admin.user_attendance.index', [
                'employeeData'  => collect(),
                'employees'     => DB::table('users')->where('status',1)->where('type','employee')->orderBy('name')->get(),
                'years'         => $this->getYears(),
                'month'         => $month,
                'year'          => $year,
                'filterDate'    => $filterDate,
                'statusOptions' => $this->getStatusOptions(),
            ]);
        }

        $employeeIds = $employees->pluck('id')->toArray();

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
                'cin.name   as in_customer_name',
                'cout.name  as out_customer_name',
                'rin.name   as in_crr_name',
                'rout.name  as out_crr_name',
                'din.name   as in_dealer_name',
                'dout.name  as out_dealer_name',
                'chgby.name as changed_by_name'
            )
            ->whereIn('ua.user_id', $employeeIds)
            ->whereMonth('ua.in_date', $month)
            ->whereYear('ua.in_date',  $year)
            ->orderBy('ua.in_date', 'asc')
            ->orderBy('ua.in_time', 'asc');

        if ($filterDate) {
            $baseQuery->whereDate('ua.in_date', $filterDate);
        }

        if ($request->filled('status')) {
            $baseQuery->where('ua.status', $request->status);
        }

        $attRecords = $baseQuery->get();

        $fullMonthRecords = DB::table('user_attendances as ua')
            ->select('ua.user_id', 'ua.in_date', 'ua.status')
            ->whereIn('ua.user_id', $employeeIds)
            ->whereMonth('ua.in_date', $month)
            ->whereYear('ua.in_date',  $year)
            ->orderBy('ua.in_date', 'asc')
            ->get();

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

        $attIds = $attRecords->pluck('id')->toArray();
        $linkedLeaves = [];
        if (!empty($attIds)) {
            $leaves = DB::table('user_leaves as ul')
                ->join('leave_types as lt', 'ul.leave_type_id', '=', 'lt.id')
                ->whereIn('ul.attendance_id', $attIds)
                ->where('ul.status', 'approved')
                ->select('ul.attendance_id','ul.quota_deducted','lt.name as lt_name','lt.code as lt_code','lt.color as lt_color')
                ->get();
            foreach ($leaves as $lv) {
                $linkedLeaves[$lv->attendance_id] = $lv;
            }
        }

        $holidays = $this->getHolidaysForMonth($month, $year);

        $allMonthDates = [];
        $limit = $endOfMonth->lt($today) ? $endOfMonth : $today;
        for ($d = $startOfMonth->copy(); $d->lte($limit); $d->addDay()) {
            $allMonthDates[] = $d->toDateString();
        }

        $employeeData = collect();

        $datesToShow = [];
        if ($filterDate) {
            $datesToShow[] = Carbon::parse($filterDate)->toDateString();
        } else {
            for ($d = $startOfMonth->copy(); $d->lte($limit); $d->addDay()) {
                $datesToShow[] = $d->toDateString();
            }
            for ($d = $today->copy()->addDay(); $d->lte($endOfMonth); $d->addDay()) {
                $datesToShow[] = $d->toDateString();
            }
        }

        foreach ($employees as $emp) {
            $empDates = [];
            $presentCount = 0;
            $leaveCount   = 0;
            $lwpCount     = 0;
            $compOffCount = 0;
            $workingDays  = 0;

            foreach ($allMonthDates as $ds) {
                $dateCarbon = Carbon::parse($ds);
                $isSunday   = ($dateCarbon->dayOfWeek === 0);
                $isHoliday  = isset($holidays[$ds]);
                $isFuture   = $dateCarbon->gt($today);
                $isToday    = $dateCarbon->isToday();

                if (!$isSunday && !$isHoliday && !$isFuture) $workingDays++;

                $monthRecs = $fullMonthByUserDate[$emp->id][$ds] ?? [];
                if (!empty($monthRecs)) {
                    $mainRec        = end($monthRecs);
                    $computedStatus = $mainRec->status ?? self::ST_PRESENT;
                } elseif ($isHoliday)   { $computedStatus = self::ST_HOLIDAY; }
                elseif ($isSunday)      { $computedStatus = self::ST_WEEKLY_OFF; }
                elseif ($isToday)       { $computedStatus = 'Not Punched Yet'; }
                elseif (!$isFuture)     { $computedStatus = self::ST_LWP_UNINF; }
                else                    { $computedStatus = null; }

                if ($computedStatus === self::ST_PRESENT)                $presentCount++;
                elseif (in_array($computedStatus, self::LEAVE_STATUSES)) $leaveCount++;
                elseif (in_array($computedStatus, self::LWP_STATUSES))   $lwpCount++;
                elseif ($computedStatus === self::ST_COMP_OFF)           $compOffCount++;
            }

            foreach ($datesToShow as $ds) {
                $dateCarbon = Carbon::parse($ds);
                $isSunday   = ($dateCarbon->dayOfWeek === 0);
                $isHoliday  = isset($holidays[$ds]);
                $holidayName= $holidays[$ds] ?? null;
                $isPast     = $dateCarbon->lt($today);
                $isToday    = $dateCarbon->isToday();
                $isFuture   = $dateCarbon->gt($today);

                $records = $attByUserDate[$emp->id][$ds] ?? [];

                if (!empty($records)) {
                    $mainRecord     = end($records);
                    $computedStatus = $mainRecord->status ?? self::ST_PRESENT;
                } elseif ($isHoliday) { $computedStatus = self::ST_HOLIDAY;    $mainRecord = null; }
                elseif ($isSunday)    { $computedStatus = self::ST_WEEKLY_OFF; $mainRecord = null; }
                elseif ($isToday)     { $computedStatus = 'Not Punched Yet';   $mainRecord = null; }
                elseif ($isPast)      { $computedStatus = self::ST_LWP_UNINF;  $mainRecord = null; }
                else                  { $computedStatus = null;                $mainRecord = null; }

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
                    if ($totalMins > 0) {
                        $duration = floor($totalMins/60).'h '.($totalMins%60).'m';
                    }
                }

                $leaveInfo = null;
                if ($mainRecord && isset($linkedLeaves[$mainRecord->id])) {
                    $leaveInfo = $linkedLeaves[$mainRecord->id];
                }

                $empDates[] = [
                    'date'         => $ds,
                    'carbon'       => $dateCarbon,
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
                ];
            }

            $employeeData->push([
                'employee'      => $emp,
                'dates'         => $empDates,
                'present_count' => $presentCount,
                'leave_count'   => $leaveCount,
                'lwp_count'     => $lwpCount,
                'comp_off_count'=> $compOffCount,
                'working_days'  => $workingDays,
            ]);
        }

        $allEmployees = DB::table('users')
            ->select('id','name','mobile')
            ->where('status',1)->where('type','employee')
            ->orderBy('name')->get();

        $title = "Attendance";

        return view('admin.user_attendance.index', compact(
            'employeeData','allEmployees','month','year','filterDate','title'
        ) + [
            'employees'     => $allEmployees,
            'years'         => $this->getYears(),
            'statusOptions' => $this->getStatusOptions(),
            'today'         => $today->toDateString(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET  admin/attendance/quota-info
    // ─────────────────────────────────────────────────────────────
    public function getQuotaInfo(Request $request)
    {
        $userId       = $request->user_id;
        $date         = $request->date;
        $attendanceId = $request->attendance_id;

        if (!$userId || !$date) {
            return response()->json(['success'=>false,'message'=>'Missing parameters.']);
        }

        $fy = $this->getFinancialYear($date);

        $leaveTypes = LeaveType::where('is_active', true)
            ->where('has_quota', true)
            ->orderBy('sort_order')->get();

        $quotaData = $leaveTypes->map(function ($lt) use ($userId, $fy) {
            $quota = UserLeaveQuota::where('user_id', $userId)
                ->where('leave_type_id', $lt->id)
                ->where('financial_year', $fy)->first();
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

        $mlType = LeaveType::where('code','ML')->where('is_active',true)->first();
        if ($mlType) {
            $quotaData->push([
                'id'        => $mlType->id,
                'name'      => $mlType->name,
                'code'      => 'ML',
                'color'     => $mlType->color,
                'total'     => null,
                'used'      => null,
                'remaining' => null,
            ]);
        }

        $existingLeave = null;
        if ($attendanceId) {
            $existingLeave = UserLeave::where('attendance_id', $attendanceId)
                ->where('status', 'approved')
                ->with('leaveType')->first();
        }

        return response()->json([
            'success'        => true,
            'quota'          => $quotaData,
            'financial_year' => $fy,
            'existing_leave' => $existingLeave,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // POST admin/attendance/{id}/update-status
    // ─────────────────────────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $attendance = UserAttendance::find($id);
        if (!$attendance) {
            return response()->json(['success'=>false,'message'=>'Record not found.'], 404);
        }

        $request->validate([
            'new_status'    => 'required|string',
            'leave_type_id' => 'nullable|integer|exists:leave_types,id',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $adminId    = auth()->id();
        $oldStatus  = $attendance->status;
        $newStatus  = $request->new_status;
        $userId     = $attendance->user_id;
        $date       = $attendance->in_date;
        $fy         = $this->getFinancialYear($date);
        $isOldLeave = in_array($oldStatus, self::LEAVE_STATUSES);
        $isNewLeave = in_array($newStatus, self::LEAVE_STATUSES);
        $isHalfDay  = ($newStatus === self::ST_HALF_LEAVE);
        $deduct     = $isHalfDay ? 0.5 : 1.0;

        DB::beginTransaction();
        try {
            if ($isOldLeave) {
                $oldLeave = UserLeave::where('attendance_id', $id)
                    ->where('status', 'approved')->first();
                if ($oldLeave) {
                    $oldLT = LeaveType::find($oldLeave->leave_type_id);
                    if ($oldLT && $oldLT->has_quota) {
                        UserLeaveQuota::where('user_id', $userId)
                            ->where('leave_type_id', $oldLeave->leave_type_id)
                            ->where('financial_year', $fy)
                            ->decrement('used_quota', $oldLeave->quota_deducted);
                    }
                    $oldLeave->update(['status' => 'cancelled']);
                }
            }

            if ($isNewLeave) {
                if (!$request->leave_type_id) {
                    DB::rollBack();
                    return response()->json(['success'=>false,'message'=>'Please select a leave type.'], 422);
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
                            'user_id'        => $userId,
                            'leave_type_id'  => $request->leave_type_id,
                            'financial_year' => $fy,
                            'total_quota'    => 0,
                            'used_quota'     => $deduct,
                        ]);
                    }
                }
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
            if ($request->admin_remarks) {
                $changeNote .= " — {$request->admin_remarks}";
            }

            $attendance->update([
                'status'              => $newStatus,
                'previous_status'     => $oldStatus,
                'status_changed_by'   => $adminId,
                'status_change_note'  => $changeNote,
                'status_changed_at'   => now(),
            ]);

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Status updated.',
                'new_status' => $newStatus,
                'old_status' => $oldStatus,
                'change_note'=> $changeNote,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // POST admin/attendance/create-record
    // ─────────────────────────────────────────────────────────────
    public function createRecord(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'date'          => 'required|date',
            'new_status'    => 'required|string',
            'leave_type_id' => 'nullable|integer|exists:leave_types,id',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $adminId   = auth()->id();
        $userId    = $request->user_id;
        $date      = $request->date;
        $newStatus = $request->new_status;
        $fy        = $this->getFinancialYear($date);
        $isNewLeave= in_array($newStatus, self::LEAVE_STATUSES);
        $isHalfDay = ($newStatus === self::ST_HALF_LEAVE);
        $deduct    = $isHalfDay ? 0.5 : 1.0;
        $adminName = DB::table('users')->where('id', $adminId)->value('name') ?? 'Admin';

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
                            'user_id'        => $userId,
                            'leave_type_id'  => $request->leave_type_id,
                            'financial_year' => $fy,
                            'total_quota'    => 0,
                            'used_quota'     => $deduct,
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
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>$e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GET  admin/attendance/export-pdf / export-pdf/{employeeId}
    // ─────────────────────────────────────────────────────────────
    public function exportPdf(Request $request, $employeeId = null)
    {
        $today = Carbon::today();
        $month = (int) $request->get('month', $today->month);
        $year  = (int) $request->get('year',  $today->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $empQuery = DB::table('users')
            ->select('id','name','mobile','base_city')
            ->where('status', 1)->where('type', 'employee')->orderBy('name');

        if ($employeeId) {
            $empQuery->where('id', $employeeId);
        } elseif ($request->filled('employee_id')) {
            $empQuery->where('id', $request->employee_id);
        }

        $employees   = $empQuery->get();
        $employeeIds = $employees->pluck('id')->toArray();

        if ($employees->isEmpty()) {
            abort(404, 'Employee not found.');
        }

        // ── All leave types (for quota section) ──────────────────
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('sort_order')->get();

        $attRecords = DB::table('user_attendances as ua')
            ->leftJoin('customers as cin',  'ua.in_customer_id',  '=', 'cin.id')
            ->leftJoin('customers as cout', 'ua.out_customer_id', '=', 'cout.id')
            ->leftJoin('users as chgby',    'ua.status_changed_by','=','chgby.id')
            ->select('ua.*','cin.name as in_customer_name','cout.name as out_customer_name','chgby.name as changed_by_name')
            ->whereIn('ua.user_id', $employeeIds)
            ->whereMonth('ua.in_date', $month)->whereYear('ua.in_date', $year)
            ->orderBy('ua.in_date','asc')->orderBy('ua.in_time','asc')
            ->get();

        $attByUserDate = [];
        foreach ($attRecords as $rec) {
            $ds = Carbon::parse($rec->in_date)->toDateString();
            $attByUserDate[$rec->user_id][$ds][] = $rec;
        }

        $holidays = $this->getHolidaysForMonth($month, $year);

        // Full month date range
        $datesToShow = [];
        $limit = $endOfMonth->lt($today) ? $endOfMonth : $today;
        for ($d = $startOfMonth->copy(); $d->lte($limit); $d->addDay()) {
            $datesToShow[] = $d->toDateString();
        }
        for ($d = $today->copy()->addDay(); $d->lte($endOfMonth); $d->addDay()) {
            $datesToShow[] = $d->toDateString();
        }

        $employeeData = collect();

        foreach ($employees as $emp) {
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
                    $computedStatus = $mainRecord->status ?? self::ST_PRESENT;
                } elseif ($isHoliday) { $computedStatus = self::ST_HOLIDAY;    $mainRecord = null; }
                elseif ($isSunday)    { $computedStatus = self::ST_WEEKLY_OFF; $mainRecord = null; }
                elseif ($isToday)     { $computedStatus = 'Not Punched Yet';   $mainRecord = null; }
                elseif ($isFuture)    { $computedStatus = null;                $mainRecord = null; }
                else                  { $computedStatus = self::ST_LWP_UNINF;  $mainRecord = null; }

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

                if ($computedStatus === self::ST_PRESENT)                $presentCount++;
                elseif (in_array($computedStatus, self::LEAVE_STATUSES)) $leaveCount++;
                elseif (in_array($computedStatus, self::LWP_STATUSES))   $lwpCount++;
                elseif ($computedStatus === self::ST_COMP_OFF)           $compOffCount++;

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
                    'holiday_name'   => $holidays[$ds] ?? null,
                ];
            }

            // ── Load quota for this employee / FY ─────────────────
            $fy           = $this->getFinancialYear(Carbon::create($year, $month, 1)->toDateString());
            $quotaDetails = [];
            foreach ($leaveTypes as $lt) {
                if ($lt->has_quota) {
                    $quota = UserLeaveQuota::where('user_id', $emp->id)
                        ->where('leave_type_id', $lt->id)
                        ->where('financial_year', $fy)->first();
                    $quotaDetails[] = [
                        'code'      => $lt->code,
                        'name'      => $lt->name,
                        'total'     => $quota ? (float) $quota->total_quota : 0.0,
                        'used'      => $quota ? (float) $quota->used_quota  : 0.0,
                        'remaining' => $quota ? max(0, (float)$quota->total_quota - (float)$quota->used_quota) : 0.0,
                        'unlimited' => false,
                    ];
                } else {
                    // ML and no-quota types — unlimited
                    $quotaDetails[] = [
                        'code'      => $lt->code,
                        'name'      => $lt->name,
                        'total'     => null,
                        'used'      => null,
                        'remaining' => null,
                        'unlimited' => true,
                    ];
                }
            }

            $employeeData->push([
                'employee'       => $emp,
                'dates'          => $empDates,
                'present_count'  => $presentCount,
                'leave_count'    => $leaveCount,
                'lwp_count'      => $lwpCount,
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
                'defaultMediaType'     => 'print',
            ]);

        $empName  = $employees->count() === 1
            ? preg_replace('/[^A-Za-z0-9_]/', '_', $employees->first()->name)
            : 'All_Employees';
        $filename = "Attendance_{$empName}_{$monthName}.pdf";

        return $pdf->download($filename);
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────
    protected function getFinancialYear($date): string
    {
        $d = Carbon::parse($date);
        return $d->month >= 4
            ? $d->year.'-'.substr($d->year+1,-2)
            : ($d->year-1).'-'.substr($d->year,-2);
    }

    protected function getYears(): array
    {
        $y = [];
        for ($i = date('Y'); $i >= date('Y')-3; $i--) $y[] = $i;
        return $y;
    }

    protected function getStatusOptions(): array
    {
        return [
            self::ST_PRESENT,
            self::ST_HALF_LWP,
            self::ST_HALF_LEAVE,
            self::ST_FULL_LEAVE,
            self::ST_LWP_UNINF,
            self::ST_LWP_UNAPP,
            self::ST_LWP_EXCESS,
            self::ST_HOLIDAY,
            self::ST_COMP_OFF,
            self::ST_WEEKLY_OFF,
        ];
    }

    protected function getHolidaysForMonth(int $month, int $year): array
    {
        $monthPad = sprintf('%02d', $month);
        $rows = DB::table('holiday_lists')
            ->where('is_active', true)
            ->where(function ($q) { $q->where('is_national',true)->orWhereNull('city'); })
            ->where(function ($q) use ($month, $year, $monthPad) {
                $q->where(function ($i) use ($month, $year) {
                    $i->whereMonth('date', $month)->whereYear('date', $year);
                })->orWhere(function ($i) use ($monthPad) {
                    $i->where('is_recurring', true)->whereRaw("DATE_FORMAT(date,'%m')=?", [$monthPad]);
                });
            })
            ->select('name','date','is_recurring')->get();

        $map = [];
        foreach ($rows as $h) {
            $ds = $h->is_recurring
                ? ($year.'-'.sprintf('%02d',$month).'-'.Carbon::parse($h->date)->format('d'))
                : Carbon::parse($h->date)->toDateString();
            $map[$ds] = $h->name;
        }
        return $map;
    }
}