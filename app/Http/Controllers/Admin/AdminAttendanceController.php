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

/**
 * AdminAttendanceController
 *
 * Key design: For each employee, generate EVERY date in the selected
 * month and map real attendance records onto it. Missing dates get a
 * computed default status:
 *   - Today (no punch)  → "Not Punched Yet"
 *   - Past date (no punch, not Sunday, not holiday) → "Absent"
 *   - Sunday (no punch) → "Weekly Off"
 *   - Holiday           → "Holiday"
 *   - Future date       → null (shown as "-")
 *
 * Routes:
 *   GET  admin/attendance                         → index()
 *   GET  admin/attendance/quota-info              → getQuotaInfo()
 *   POST admin/attendance/{id}/update-status      → updateStatus()
 *   POST admin/attendance/create-record           → createRecord()
 */
class AdminAttendanceController extends Controller
{
    const LEAVE_STATUSES = ['Allowed Full Day Leave', '1/2 Present + 1/2 Leave'];
    const LWP_STATUSES   = ['LWP (Uninformed Absence)', 'LWP (Unapproved Leave)', 'LWP (Leave in excess quota)'];

    // ─────────────────────────────────────────────────────────────
    // GET  admin/attendance
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        Session::put('active', 'attendance');

        $today = Carbon::today();
        $month = (int) $request->get('month', $today->month);
        $year  = (int) $request->get('year',  $today->year);

        // ── filterDate logic ─────────────────────────────────────
        // No 'date' key in URL at all  → default to TODAY (initial page load)
        // 'date=' empty string in URL  → Full Month (user clicked Full Month pill)
        // 'date=2026-04-07'            → specific date
        if (!$request->has('date')) {
            $filterDate = $today->toDateString(); // default: today
        } elseif ($request->get('date') === '') {
            $filterDate = null; // full month
        } else {
            $filterDate = $request->get('date');
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // ── Employees list ──────────────────────────────────────
        $empQuery = DB::table('users')
            ->select('id', 'name', 'mobile', 'base_city')
            ->where('status', 1)
            ->where('type', 'employee')
            ->orderBy('name');

        if ($request->filled('employee_id')) {
            $empQuery->where('id', $request->employee_id);
        }

        $employees = $empQuery->get();

        if ($employees->isEmpty()) {
            return view('admin.attendance.index', [
                'employeeData' => collect(),
                'employees'    => DB::table('users')->where('status', 1)->orderBy('name')->get(),
                'years'        => $this->getYears(),
                'month'        => $month,
                'year'         => $year,
                'filterDate'   => $filterDate,
                'today'        => $today->toDateString(),
                'statusOptions'=> $this->getStatusOptions(),
            ]);
        }

        $employeeIds = $employees->pluck('id')->toArray();

        // ── All attendance records for these employees this month ─
        $attRecords = DB::table('user_attendances as ua')
            ->leftJoin('customers as cin',               'ua.in_customer_id',                   '=', 'cin.id')
            ->leftJoin('customers as cout',              'ua.out_customer_id',                  '=', 'cout.id')
            ->leftJoin('customer_register_requests as rin', 'ua.in_customer_register_request_id', '=', 'rin.id')
            ->leftJoin('customer_register_requests as rout','ua.out_customer_register_request_id','=', 'rout.id')
            ->leftJoin('dealers as din',                 'ua.in_dealer_id',                     '=', 'din.id')
            ->leftJoin('dealers as dout',                'ua.out_dealer_id',                    '=', 'dout.id')
            ->select(
                'ua.*',
                'cin.name  as in_customer_name',
                'cout.name as out_customer_name',
                'rin.name  as in_crr_name',
                'rout.name as out_crr_name',
                'din.name  as in_dealer_name',
                'dout.name as out_dealer_name'
            )
            ->whereIn('ua.user_id', $employeeIds)
            ->whereMonth('ua.in_date', $month)
            ->whereYear('ua.in_date',  $year)
            ->orderBy('ua.in_date', 'asc')
            ->orderBy('ua.in_time', 'asc')
            ->get();

        // Group by user_id → date → [records]
        // A single date can have multiple IN/OUT pairs (e.g. went out and came back)
        $attByUserDate = [];
        foreach ($attRecords as $rec) {
            $ds = Carbon::parse($rec->in_date)->toDateString();
            $attByUserDate[$rec->user_id][$ds][] = $rec;
        }

        // ── Holidays for this month ──────────────────────────────
        // (national + city-specific, handles is_recurring)
        $holidays = $this->getHolidaysForMonth($month, $year);
        // $holidays = ['2026-04-14' => 'Dr Ambedkar Jayanti', ...]

        // ── Generate per-employee date grid ─────────────────────
        $employeeData = collect();

        // Determine which dates to show
        $datesToShow = [];
        if ($filterDate) {
            $datesToShow[] = Carbon::parse($filterDate)->toDateString();
        } else {
            // All dates in month up to today (or end of month)
            $limit = $endOfMonth->lt($today) ? $endOfMonth : $today;
            for ($d = $startOfMonth->copy(); $d->lte($limit); $d->addDay()) {
                $datesToShow[] = $d->toDateString();
            }
            // Also future dates up to end of month (for visibility)
            $futureStart = $today->copy()->addDay();
            for ($d = $futureStart; $d->lte($endOfMonth); $d->addDay()) {
                $datesToShow[] = $d->toDateString();
            }
        }

        foreach ($employees as $emp) {
            $empDates    = [];
            $presentCount = 0;
            $leaveCount   = 0;
            $absentCount  = 0;
            $lwpCount     = 0;

            foreach ($datesToShow as $ds) {
                $dateCarbon = Carbon::parse($ds);
                $isSunday   = ($dateCarbon->dayOfWeek === 0);
                $isHoliday  = isset($holidays[$ds]);
                $holidayName= $holidays[$ds] ?? null;
                $isPast     = $dateCarbon->lt($today);
                $isToday    = $dateCarbon->isToday();
                $isFuture   = $dateCarbon->gt($today);

                // Get all attendance records for this employee on this date
                $records = $attByUserDate[$emp->id][$ds] ?? [];

                // Determine computed status for this date
                if (!empty($records)) {
                    // Use the status from the last/most-recent record
                    $mainRecord    = end($records);
                    $computedStatus = $mainRecord->status ?? 'Full Day Present';
                } elseif ($isHoliday) {
                    $computedStatus = 'Holiday';
                    $mainRecord     = null;
                } elseif ($isSunday) {
                    $computedStatus = 'Weekly Off';
                    $mainRecord     = null;
                } elseif ($isToday) {
                    $computedStatus = 'Not Punched Yet';
                    $mainRecord     = null;
                } elseif ($isPast) {
                    $computedStatus = 'Absent';
                    $mainRecord     = null;
                } else {
                    // Future
                    $computedStatus = null;
                    $mainRecord     = null;
                }

                // Stats
                if ($computedStatus === 'Full Day Present')    $presentCount++;
                elseif (in_array($computedStatus, self::LEAVE_STATUSES)) $leaveCount++;
                elseif ($computedStatus === 'Absent' || $computedStatus === 'Not Punched Yet') $absentCount++;
                elseif (str_starts_with($computedStatus ?? '', 'LWP'))  $lwpCount++;

                // Duration calc (across all records for the day)
                $duration = null;
                if (!empty($records)) {
                    $totalMins = 0;
                    foreach ($records as $r) {
                        if ($r->in_time && $r->out_time) {
                            try {
                                $inDt   = Carbon::parse($r->in_date  . ' ' . $r->in_time);
                                $outDt  = Carbon::parse(($r->out_date ?? $r->in_date) . ' ' . $r->out_time);
                                $totalMins += $inDt->diffInMinutes($outDt);
                            } catch (\Exception $e) {}
                        }
                    }
                    if ($totalMins > 0) {
                        $duration = floor($totalMins/60).'h '.($totalMins%60).'m';
                    }
                }

                $empDates[] = [
                    'date'          => $ds,
                    'carbon'        => $dateCarbon,
                    'is_sunday'     => $isSunday,
                    'is_holiday'    => $isHoliday,
                    'holiday_name'  => $holidayName,
                    'is_past'       => $isPast,
                    'is_today'      => $isToday,
                    'is_future'     => $isFuture,
                    'records'       => $records,
                    'main_record'   => $mainRecord,
                    'status'        => $computedStatus,
                    'duration'      => $duration,
                    'has_record'    => !empty($records),
                    'is_open'       => $mainRecord && is_null($mainRecord->out_time) && !$mainRecord->missed,
                ];
            }

            $employeeData->push([
                'employee'      => $emp,
                'dates'         => $empDates,
                'present_count' => $presentCount,
                'leave_count'   => $leaveCount,
                'absent_count'  => $absentCount,
                'lwp_count'     => $lwpCount,
                'total_days'    => count(array_filter($datesToShow, fn($d) => !Carbon::parse($d)->gt($today))),
            ]);
        }

        // All employees for filter dropdown
        $allEmployees = DB::table('users')
            ->select('id', 'name', 'mobile')
            ->where('status', 1)
            ->orderBy('name')
            ->where('type', 'employee')
            ->get();

        return view('admin.user_attendance.index', compact(
            'employeeData', 'allEmployees', 'month', 'year',
            'filterDate', 'today', 'startOfMonth', 'endOfMonth'
        ) + [
            'employees'     => $allEmployees,
            'years'         => $this->getYears(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET admin/attendance/quota-info
    // ─────────────────────────────────────────────────────────────
    public function getQuotaInfo(Request $request)
    {
        $userId       = $request->user_id;
        $date         = $request->date;
        $attendanceId = $request->attendance_id;

        if (!$userId || !$date) {
            return response()->json(['success' => false, 'message' => 'Missing parameters.']);
        }

        $fy = $this->getFinancialYear($date);

        $leaveTypes = LeaveType::where('is_active', true)
            ->where('has_quota', true)
            ->orderBy('sort_order')
            ->get();

        $quotaData = $leaveTypes->map(function ($lt) use ($userId, $fy) {
            $quota = UserLeaveQuota::where('user_id', $userId)
                ->where('leave_type_id', $lt->id)
                ->where('financial_year', $fy)
                ->first();

            return [
                'id'        => $lt->id,
                'name'      => $lt->name,
                'code'      => $lt->code,
                'color'     => $lt->color,
                'total'     => $quota ? (float) $quota->total_quota : 0,
                'used'      => $quota ? (float) $quota->used_quota  : 0,
                'remaining' => $quota ? max(0, $quota->total_quota - $quota->used_quota) : 0,
            ];
        });

        $existingLeave = null;
        if ($attendanceId) {
            $existingLeave = UserLeave::where('attendance_id', $attendanceId)
                ->where('status', 'approved')
                ->with('leaveType')
                ->first();
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
    // For dates that HAVE an existing user_attendances record
    // ─────────────────────────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $attendance = UserAttendance::find($id);
        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $request->validate([
            'new_status'    => 'required|string',
            'leave_type_id' => 'nullable|integer|exists:leave_types,id',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $oldStatus   = $attendance->status;
        $newStatus   = $request->new_status;
        $userId      = $attendance->user_id;
        $date        = $attendance->in_date;
        $fy          = $this->getFinancialYear($date);
        $isOldLeave  = in_array($oldStatus, self::LEAVE_STATUSES);
        $isNewLeave  = in_array($newStatus, self::LEAVE_STATUSES);
        $isHalfDay   = ($newStatus === '1/2 Present + 1/2 Leave');
        $deduct      = $isHalfDay ? 0.5 : 1.0;

        DB::beginTransaction();
        try {
            // Restore old quota if was leave
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

            // Deduct new quota if leave
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

            $attendance->update(['status' => $newStatus]);
            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Status updated.',
                'new_status' => $newStatus,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // POST admin/attendance/create-record
    // For dates with NO existing attendance record (Absent / Not Punched Yet)
    // Admin sets status without requiring IN/OUT punch data
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

        $userId    = $request->user_id;
        $date      = $request->date;
        $newStatus = $request->new_status;
        $fy        = $this->getFinancialYear($date);
        $isNewLeave= in_array($newStatus, self::LEAVE_STATUSES);
        $isHalfDay = ($newStatus === '1/2 Present + 1/2 Leave');
        $deduct    = $isHalfDay ? 0.5 : 1.0;

        DB::beginTransaction();
        try {
            // Create a placeholder attendance row
            $attendance = UserAttendance::create([
                'user_id' => $userId,
                'in_date' => $date,
                'in_time' => null,
                'status'  => $newStatus,
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
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    protected function getFinancialYear($date): string
    {
        $d = Carbon::parse($date);
        return $d->month >= 4
            ? $d->year . '-' . substr($d->year + 1, -2)
            : ($d->year - 1) . '-' . substr($d->year, -2);
    }

    protected function getYears(): array
    {
        $years = [];
        for ($y = date('Y'); $y >= date('Y') - 3; $y--) {
            $years[] = $y;
        }
        return $years;
    }

    protected function getStatusOptions(): array
    {
        return [
            'Full Day Present',
            '1/2 Present + 1/2 Leave',
            'Allowed Full Day Leave',
            'Weekly Off',
            'Holiday',
            'Comp Off',
            'Absent',
            'LWP (Uninformed Absence)',
            'LWP (Unapproved Leave)',
            'LWP (Leave in excess quota)',
        ];
    }

    /**
     * Returns ['2026-04-14' => 'Dr Ambedkar Jayanti', ...]
     * Handles national, city-specific, and recurring holidays.
     */
    protected function getHolidaysForMonth(int $month, int $year): array
    {
        $monthPad = sprintf('%02d', $month);

        $rows = DB::table('holiday_lists')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_national', true)->orWhereNull('city');
            })
            ->where(function ($q) use ($month, $year, $monthPad) {
                $q->where(function ($inner) use ($month, $year) {
                    $inner->whereMonth('date', $month)->whereYear('date', $year);
                })->orWhere(function ($inner) use ($monthPad) {
                    $inner->where('is_recurring', true)
                          ->whereRaw("DATE_FORMAT(date, '%m') = ?", [$monthPad]);
                });
            })
            ->select('name', 'date', 'is_recurring')
            ->get();

        $map = [];
        foreach ($rows as $h) {
            $ds = $h->is_recurring
                ? ($year . '-' . sprintf('%02d', $month) . '-' . Carbon::parse($h->date)->format('d'))
                : Carbon::parse($h->date)->toDateString();
            $map[$ds] = $h->name;
        }
        return $map;
    }
}