<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\UserAttendance;
use App\LeaveType;
use App\UserLeaveQuota;
use App\UserLeave;
use App\HolidayList;
use App\UserWeeklyOffCompensation;
use Validator;
use DB;
use Carbon\Carbon;

/**
 * AttendanceController
 *
 * Handles full attendance lifecycle:
 *  - Mark IN / OUT
 *  - Apply Leave (single or multiple dates)
 *  - Leave Quota management (financial-year-wise)
 *  - Weekly Off Comp-Off carry-forward (Sunday → next week only)
 *  - Holiday list (national + city-specific)
 *  - Calendar view (attendance + leaves + holidays combined)
 */
class AttendanceController extends Controller
{
    protected $resp;

    // ─────────────────────────────────────────────────────────────────────────
    // ATTENDANCE STATUSES (constants for consistency)
    // ─────────────────────────────────────────────────────────────────────────
    const STATUS_PRESENT        = 'Full Day Present';
    const STATUS_FULL_LEAVE     = 'Allowed Full Day Leave';
    const STATUS_HALF_LEAVE     = '1/2 Present + 1/2 Leave';
    const STATUS_WEEKLY_OFF     = 'Weekly Off';
    const STATUS_COMP_OFF       = 'Comp Off';
    const STATUS_HOLIDAY        = 'Holiday';
    const STATUS_ABSENT         = 'Absent';

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get financial year string for a given date.
     * India FY runs April–March.
     * e.g. date in May 2026 → "2026-27"
     *      date in Feb 2026 → "2025-26"
     */
    protected function getFinancialYear($date = null): string
    {
        $d = $date ? Carbon::parse($date) : Carbon::now();
        if ($d->month >= 4) {
            return $d->year . '-' . substr($d->year + 1, -2);
        }
        return ($d->year - 1) . '-' . substr($d->year, -2);
    }

    /**
     * Ensure a quota row exists for user/leave_type/FY.
     * Creates with default_quota if missing.
     */
    protected function ensureQuota(int $userId, int $leaveTypeId, string $financialYear): UserLeaveQuota
    {
        $leaveType = LeaveType::find($leaveTypeId);

        return UserLeaveQuota::firstOrCreate(
            [
                'user_id'        => $userId,
                'leave_type_id'  => $leaveTypeId,
                'financial_year' => $financialYear,
            ],
            [
                'total_quota' => $leaveType->default_quota ?? 0,
                'used_quota'  => 0,
            ]
        );
    }

    /**
     * Get the logged-in user's base_city from users table.
     */
    protected function getUserCity(int $userId): ?string
    {
        return DB::table('users')->where('id', $userId)->value('base_city');
    }

    /**
     * Check if a given date is a holiday for the user's city.
     */
    protected function isHoliday(string $date, ?string $userCity): bool
    {
        return HolidayList::where('date', $date)
            ->where('is_active', true)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) {
                    $q->orWhere('city', $userCity);
                }
            })
            ->exists();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣  MARK IN ATTENDANCE
    //     POST v2/attendance/in
    // ─────────────────────────────────────────────────────────────────────────
    public function markIn(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'in_date'                         => 'required|date',
            'in_time'                         => 'required|date_format:H:i',
            'in_latitude'                     => 'required|numeric',
            'in_longitude'                    => 'required|numeric',
            'in_latitude_longitude_address'   => 'required|string|max:500',
            'in_place_of_attendance'          => 'required|string|max:255',
            'in_other'                        => 'nullable|string|max:500',
            'in_customer_id'                  => 'nullable|integer|exists:customers,id',
            'in_customer_register_request_id' => 'nullable|integer|exists:customer_register_requests,id',
            'in_dealer_id'                    => 'nullable|integer|exists:dealers,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        $userId = $this->resp['user']['id'];
        $inDate = $request->in_date;

        // ── Guard: must not have an open (out-less) attendance for the same date ──
        $openAttendance = UserAttendance::where('user_id', $userId)
            ->whereDate('in_date', $inDate)
            ->whereNull('out_time')
            ->where('missed', false)
            ->first();

        if ($openAttendance) {
            return response()->json(
                apiErrorResponse(
                    'You have an open attendance for this date (ID: ' . $openAttendance->id . '). ' .
                    'Please mark OUT first before marking IN again.'
                ),
                422
            );
        }

        DB::beginTransaction();
        try {
            $attendance = UserAttendance::create([
                'user_id'                         => $userId,
                'in_date'                         => $inDate,
                'in_time'                         => $request->in_time,
                'in_latitude'                     => $request->in_latitude,
                'in_longitude'                    => $request->in_longitude,
                'in_latitude_longitude_address'   => $request->in_latitude_longitude_address,
                'in_place_of_attendance'          => $request->in_place_of_attendance,
                'in_other'                        => $request->in_other ?? null,
                'in_customer_id'                  => $request->in_customer_id ?? null,
                'in_customer_register_request_id' => $request->in_customer_register_request_id ?? null,
                'in_dealer_id'                    => $request->in_dealer_id ?? null,
                'status'                          => self::STATUS_PRESENT,
            ]);

            // ── Sunday worked → generate comp-off (once per Sunday) ──
            if (Carbon::parse($inDate)->dayOfWeek === Carbon::SUNDAY) {
                $alreadyHasComp = UserWeeklyOffCompensation::where('user_id', $userId)
                    ->where('worked_date', $inDate)
                    ->exists();

                if (!$alreadyHasComp) {
                    $workedCarbon = Carbon::parse($inDate);
                    // Comp-off valid: next Mon (worked+1) to next Sat (worked+6)
                    UserWeeklyOffCompensation::create([
                        'user_id'     => $userId,
                        'worked_date' => $inDate,
                        'valid_from'  => $workedCarbon->copy()->addDay()->toDateString(),
                        'expires_on'  => $workedCarbon->copy()->addDays(6)->toDateString(),
                        'status'      => 'available',
                    ]);
                }
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse('Attendance marked successfully', ['attendance' => $attendance]),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2️⃣  MARK OUT ATTENDANCE
    //     POST v2/attendance/out
    // ─────────────────────────────────────────────────────────────────────────
    public function markOut(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'attendance_id'                    => 'required|integer|exists:user_attendances,id',
            'out_date'                         => 'required|date',
            'out_time'                         => 'required|date_format:H:i',
            'out_latitude'                     => 'required|numeric',
            'out_longitude'                    => 'required|numeric',
            'out_latitude_longitude_address'   => 'required|string|max:500',
            'out_place_of_attendance'          => 'required|string|max:255',
            'out_other'                        => 'nullable|string|max:500',
            'out_customer_id'                  => 'nullable|integer|exists:customers,id',
            'out_customer_register_request_id' => 'nullable|integer|exists:customer_register_requests,id',
            'out_dealer_id'                    => 'nullable|integer|exists:dealers,id',
            'missed'                           => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        $userId = $this->resp['user']['id'];

        $attendance = UserAttendance::where('id', $request->attendance_id)
            ->where('user_id', $userId)
            ->first();

        if (!$attendance) {
            return response()->json(apiErrorResponse('Attendance record not found'), 404);
        }

        if (!is_null($attendance->out_time)) {
            return response()->json(apiErrorResponse('OUT is already marked for this attendance'), 422);
        }

        try {
            $attendance->update([
                'out_date'                         => $request->out_date,
                'out_time'                         => $request->out_time,
                'out_latitude'                     => $request->out_latitude,
                'out_longitude'                    => $request->out_longitude,
                'out_latitude_longitude_address'   => $request->out_latitude_longitude_address,
                'out_place_of_attendance'          => $request->out_place_of_attendance,
                'out_other'                        => $request->out_other ?? null,
                'out_customer_id'                  => $request->out_customer_id ?? null,
                'out_customer_register_request_id' => $request->out_customer_register_request_id ?? null,
                'out_dealer_id'                    => $request->out_dealer_id ?? null,
                'missed'                           => $request->input('missed', 0),
            ]);

            return response()->json(
                apiSuccessResponse('OUT attendance marked successfully', ['attendance' => $attendance->fresh()]),
                200
            );

        } catch (\Exception $e) {
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3️⃣  APPLY LEAVE (SUPPORTS MULTIPLE DATES IN ONE CALL)
    //     POST v2/attendance/leave/apply
    //
    //  Payload example:
    //  {
    //    "leaves": [
    //      {
    //        "date": "2026-01-01",
    //        "leave_type_id": 1,            ← FK to leave_types.id
    //        "leave_duration": "full_day",  ← full_day | half_day
    //        "half_day_type": null,         ← first_half | second_half (required if half_day)
    //        "remarks": "Sick"
    //      }
    //    ]
    //  }
    //
    //  Leave type suggestions to show in UI dropdown:
    //   ID 1 → Sick Leave      (SL)  has_quota=true  quota_editable=true
    //   ID 2 → Casual Leave    (CL)  has_quota=true  quota_editable=true
    //   ID 3 → Earned Leave    (EL)  has_quota=true  quota_editable=false ← admin CANNOT edit
    //   ID 4 → Leave Without Pay (LWP) has_quota=false ← no quota tracking at all
    // ─────────────────────────────────────────────────────────────────────────
    public function applyLeave(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'leaves'                   => 'required|array|min:1',
            'leaves.*.date'            => 'required|date',
            'leaves.*.leave_type_id'   => 'required|integer|exists:leave_types,id',
            'leaves.*.leave_duration'  => 'required|in:full_day,half_day',
            'leaves.*.half_day_type'   => 'nullable|in:first_half,second_half',
            'leaves.*.remarks'         => 'nullable|string|max:500',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        // Extra: half_day_type required when leave_duration is half_day
        foreach ($request->leaves as $i => $leave) {
            if ($leave['leave_duration'] === 'half_day' && empty($leave['half_day_type'])) {
                return response()->json(
                    apiErrorResponse("half_day_type is required at index {$i} when leave_duration is 'half_day'"),
                    422
                );
            }
        }

        $userId = $this->resp['user']['id'];

        DB::beginTransaction();

        try {
            // ── PHASE 1: Pre-validate all quotas before touching anything ──────
            // Group deductions by leave_type_id + financial_year
            $quotaMap = []; // key = "leaveTypeId_FY" → remaining after all deductions

            foreach ($request->leaves as $leave) {
                $leaveType = LeaveType::find($leave['leave_type_id']);

                if (!$leaveType || !$leaveType->is_active) {
                    DB::rollBack();
                    return response()->json(
                        apiErrorResponse("Leave type ID {$leave['leave_type_id']} is invalid or inactive"),
                        422
                    );
                }

                if ($leaveType->has_quota) {
                    $fy       = $this->getFinancialYear($leave['date']);
                    $key      = $leave['leave_type_id'] . '_' . $fy;
                    $deduct   = ($leave['leave_duration'] === 'half_day') ? 0.5 : 1.0;

                    if (!isset($quotaMap[$key])) {
                        $quota = $this->ensureQuota($userId, $leave['leave_type_id'], $fy);
                        $quotaMap[$key] = $quota->total_quota - $quota->used_quota;
                    }

                    $quotaMap[$key] -= $deduct;

                    if ($quotaMap[$key] < 0) {
                        DB::rollBack();
                        return response()->json(
                            apiErrorResponse(
                                "Insufficient {$leaveType->name} balance for the selected dates. " .
                                "Please check your quota."
                            ),
                            422
                        );
                    }
                }
            }

            // ── PHASE 2: Apply each leave ─────────────────────────────────────
            $appliedLeaves = [];

            foreach ($request->leaves as $leave) {
                $leaveType   = LeaveType::find($leave['leave_type_id']);
                $fy          = $this->getFinancialYear($leave['date']);
                $deduct      = ($leave['leave_duration'] === 'half_day') ? 0.5 : 1.0;

                $attendanceStatus = ($leave['leave_duration'] === 'full_day')
                    ? self::STATUS_FULL_LEAVE
                    : self::STATUS_HALF_LEAVE;

                // Create or update attendance row for this date
                $attendance = UserAttendance::where('user_id', $userId)
                    ->whereDate('in_date', $leave['date'])
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$attendance) {
                    // No existing attendance → create a placeholder row
                    $attendance = UserAttendance::create([
                        'user_id' => $userId,
                        'in_date' => $leave['date'],
                        'in_time' => null,
                        'status'  => $attendanceStatus,
                    ]);
                } else {
                    $attendance->update(['status' => $attendanceStatus]);
                }

                // Create leave record
                $userLeave = UserLeave::create([
                    'user_id'        => $userId,
                    'leave_type_id'  => $leave['leave_type_id'],
                    'date'           => $leave['date'],
                    'leave_duration' => $leave['leave_duration'],
                    'half_day_type'  => $leave['half_day_type'] ?? null,
                    'remarks'        => $leave['remarks'] ?? null,
                    'status'         => 'approved',
                    'attendance_id'  => $attendance->id,
                    'quota_deducted' => $deduct,
                ]);

                // Deduct quota if applicable
                if ($leaveType->has_quota) {
                    $quota = $this->ensureQuota($userId, $leave['leave_type_id'], $fy);
                    $quota->increment('used_quota', $deduct);
                }

                $appliedLeaves[] = $userLeave->load('leaveType');
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse(
                    count($appliedLeaves) . ' leave(s) applied successfully',
                    ['leaves' => $appliedLeaves]
                ),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4️⃣  CANCEL LEAVE
    //     POST v2/attendance/leave/cancel
    // ─────────────────────────────────────────────────────────────────────────
    public function cancelLeave(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:user_leaves,id',
        ]);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        $userId = $this->resp['user']['id'];

        $leave = UserLeave::where('id', $request->id)
            ->where('user_id', $userId)
            ->first();

        if (!$leave) {
            return response()->json(apiErrorResponse('Leave record not found'), 404);
        }

        if ($leave->status === 'cancelled') {
            return response()->json(apiErrorResponse('Leave is already cancelled'), 422);
        }

        DB::beginTransaction();
        try {
            $leaveType = LeaveType::find($leave->leave_type_id);

            // Restore quota
            if ($leaveType && $leaveType->has_quota) {
                $fy    = $this->getFinancialYear($leave->date);
                $quota = UserLeaveQuota::where('user_id', $userId)
                    ->where('leave_type_id', $leave->leave_type_id)
                    ->where('financial_year', $fy)
                    ->first();

                if ($quota) {
                    $quota->decrement('used_quota', $leave->quota_deducted);
                }
            }

            // Revert attendance status
            if ($leave->attendance_id) {
                $attendance = UserAttendance::find($leave->attendance_id);
                if ($attendance) {
                    // Check if other leaves exist for same date
                    $otherLeaves = UserLeave::where('attendance_id', $leave->attendance_id)
                        ->where('id', '!=', $leave->id)
                        ->where('status', 'approved')
                        ->count();

                    if ($otherLeaves === 0) {
                        $attendance->update(['status' => self::STATUS_PRESENT]);
                    }
                }
            }

            $leave->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json(
                apiSuccessResponse('Leave cancelled and quota restored successfully'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5️⃣  ATTENDANCE LIST (MONTH/YEAR FILTER)
    //     GET v2/attendance/list?month=4&year=2026
    // ─────────────────────────────────────────────────────────────────────────
    public function attendanceList(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year',  now()->year);

        if ($month < 1 || $month > 12 || $year < 2000) {
            return response()->json(apiErrorResponse('Invalid month or year'), 422);
        }

        $userId = $this->resp['user']['id'];
        if ($request->filled('employee_id')) {
            $userId = $request->employee_id;
        }

        $attendances = UserAttendance::where('user_id', $userId)
            ->whereMonth('in_date', $month)
            ->whereYear('in_date', $year)
            ->orderBy('in_date', 'desc')
            ->orderBy('in_time', 'desc')
            ->get();

        return response()->json(
            apiSuccessResponse('Attendance list fetched', [
                'month'       => $month,
                'year'        => $year,
                'total'       => $attendances->count(),
                'attendances' => $attendances,
            ]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6️⃣  LEAVE LIST
    //     GET v2/attendance/leaves?month=4&year=2026
    // ─────────────────────────────────────────────────────────────────────────
    public function leaveList(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $this->resp['user']['id'];
        $month  = (int) $request->query('month', now()->month);
        $year   = (int) $request->query('year',  now()->year);

        $leaves = UserLeave::with(['leaveType'])
            ->where('user_id', $userId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(
            apiSuccessResponse('Leave list fetched', ['leaves' => $leaves]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7️⃣  LEAVE QUOTA (FINANCIAL YEAR WISE)
    //     GET v2/attendance/quota?financial_year=2025-26
    // ─────────────────────────────────────────────────────────────────────────
    public function leaveQuota(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId        = $this->resp['user']['id'];
        $financialYear = $request->filled('financial_year')
            ? $request->financial_year
            : $this->getFinancialYear();

        $leaveTypes = LeaveType::where('is_active', true)
            ->where('has_quota', true)
            ->orderBy('sort_order')
            ->get();

        $quotaData = $leaveTypes->map(function ($leaveType) use ($userId, $financialYear) {
            $quota = $this->ensureQuota($userId, $leaveType->id, $financialYear);

            return [
                'leave_type_id'    => $leaveType->id,
                'leave_type_name'  => $leaveType->name,
                'leave_type_code'  => $leaveType->code,
                'color'            => $leaveType->color,
                'quota_editable'   => $leaveType->quota_editable, // false = EL (admin can't edit)
                'financial_year'   => $financialYear,
                'total_quota'      => (float) $quota->total_quota,
                'used_quota'       => (float) $quota->used_quota,
                'remaining_quota'  => max(0, (float) $quota->total_quota - (float) $quota->used_quota),
            ];
        });

        return response()->json(
            apiSuccessResponse('Leave quota fetched', [
                'financial_year' => $financialYear,
                'quota'          => $quotaData,
            ]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8️⃣  LEAVE TYPES LIST (for UI dropdowns)
    //     GET v2/attendance/leave-types
    // ─────────────────────────────────────────────────────────────────────────
    public function leaveTypes(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('sort_order')
            ->get([
                'id', 'name', 'code', 'has_quota',
                'quota_editable', 'color', 'default_quota'
            ]);

        return response()->json(
            apiSuccessResponse('Leave types fetched', ['leave_types' => $leaveTypes]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9️⃣  HOLIDAY LIST (NATIONAL + USER'S CITY)
    //     GET v2/attendance/holidays?year=2026
    // ─────────────────────────────────────────────────────────────────────────
    public function holidays(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId   = $this->resp['user']['id'];
        $userCity = $this->getUserCity($userId);
        $year     = (int) $request->query('year', now()->year);

        $holidays = HolidayList::where('is_active', true)
            ->whereYear('date', $year)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) {
                    $q->orWhere('city', $userCity);
                }
            })
            ->orderBy('date', 'asc')
            ->get();

        return response()->json(
            apiSuccessResponse('Holidays fetched', [
                'year'      => $year,
                'city'      => $userCity,
                'holidays'  => $holidays,
            ]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 🔟  CALENDAR VIEW (COMBINED: ATTENDANCE + LEAVES + HOLIDAYS)
    //     GET v2/attendance/calendar?month=4&year=2026
    // ─────────────────────────────────────────────────────────────────────────
    public function calendar(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $this->resp['user']['id'];
        $month  = (int) $request->query('month', now()->month);
        $year   = (int) $request->query('year',  now()->year);

        if ($month < 1 || $month > 12 || $year < 2000) {
            return response()->json(apiErrorResponse('Invalid month or year'), 422);
        }

        $userCity = $this->getUserCity($userId);

        // Auto-expire stale comp-offs before building calendar
        $this->expireStaleCompOffs($userId);

        // Pre-load all data for the month in bulk
        $attendances = UserAttendance::where('user_id', $userId)
            ->whereMonth('in_date', $month)
            ->whereYear('in_date', $year)
            ->get()
            ->groupBy(function ($a) {
                return Carbon::parse($a->in_date)->toDateString();
            });

        $leaves = UserLeave::with('leaveType')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(function ($l) {
                return Carbon::parse($l->date)->toDateString();
            });

        $holidays = HolidayList::where('is_active', true)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) {
                    $q->orWhere('city', $userCity);
                }
            })
            ->get()
            ->keyBy(function ($h) {
                return Carbon::parse($h->date)->toDateString();
            });

        $compOffs = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'used')
            ->whereNotNull('used_on')
            ->whereMonth('used_on', $month)
            ->whereYear('used_on', $year)
            ->get()
            ->keyBy(function ($c) {
                return Carbon::parse($c->used_on)->toDateString();
            });

        // Build day-by-day calendar array
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();
        $today     = Carbon::today();
        $calendar  = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $ds        = $date->toDateString();
            $isSunday  = ($date->dayOfWeek === Carbon::SUNDAY);
            $isHoliday = isset($holidays[$ds]);
            $isCompOff = isset($compOffs[$ds]);
            $isFuture  = $date->gt($today);

            $dayAttendances = $attendances->get($ds, collect());
            $dayLeaves      = $leaves->get($ds, collect());

            $calendar[] = [
                'date'        => $ds,
                'day_name'    => $date->format('l'),
                'day_number'  => $date->day,
                'is_sunday'   => $isSunday,
                'is_today'    => $date->isToday(),
                'is_future'   => $isFuture,
                'is_holiday'  => $isHoliday,
                'holiday'     => $isHoliday ? $holidays[$ds] : null,
                'is_comp_off' => $isCompOff,
                'comp_off'    => $isCompOff ? $compOffs[$ds] : null,
                'status'      => $this->computeDayStatus(
                    $ds, $isSunday, $isHoliday, $isCompOff,
                    $dayAttendances, $dayLeaves, $isFuture
                ),
                'attendances' => $dayAttendances->values(),
                'leaves'      => $dayLeaves->values(),
            ];
        }

        return response()->json(
            apiSuccessResponse('Calendar fetched', [
                'month'    => $month,
                'year'     => $year,
                'calendar' => $calendar,
            ]),
            200
        );
    }

    /**
     * Determine the display status for a calendar day.
     */
    protected function computeDayStatus(
        string $date,
        bool $isSunday,
        bool $isHoliday,
        bool $isCompOff,
        $dayAttendances,
        $dayLeaves,
        bool $isFuture
    ): ?string {
        // Priority order: Holiday → Comp Off → Leave → Weekly Off → Present → Absent
        if ($isHoliday) {
            return self::STATUS_HOLIDAY;
        }

        if ($isCompOff) {
            return self::STATUS_COMP_OFF;
        }

        if ($dayLeaves->isNotEmpty()) {
            $first = $dayLeaves->first();
            return ($first->leave_duration === 'full_day')
                ? self::STATUS_FULL_LEAVE
                : self::STATUS_HALF_LEAVE;
        }

        if ($isSunday && $dayAttendances->isEmpty()) {
            return self::STATUS_WEEKLY_OFF;
        }

        if ($dayAttendances->isNotEmpty()) {
            return $dayAttendances->first()->status ?? self::STATUS_PRESENT;
        }

        return $isFuture ? null : self::STATUS_ABSENT;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣1️⃣  USE COMP-OFF (WEEKLY OFF CARRY FORWARD)
    //      POST v2/attendance/weekly-off/use
    //
    //  Rules:
    //   - Comp-off earned by working on Sunday
    //   - Can only be used Mon–Sat of the FOLLOWING week
    //   - If not used by that Saturday → expires (no further carry-forward)
    //   - Max 2 weekly offs per week (Sunday + 1 comp-off)
    // ─────────────────────────────────────────────────────────────────────────
    public function useCompOff(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'comp_off_id' => 'required|integer|exists:user_weekly_off_compensations,id',
            'use_date'    => 'required|date',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        $userId  = $this->resp['user']['id'];
        $useDate = Carbon::parse($request->use_date);

        // Cannot use on a Sunday (Sunday is already Weekly Off)
        if ($useDate->dayOfWeek === Carbon::SUNDAY) {
            return response()->json(
                apiErrorResponse('Comp-off cannot be used on a Sunday'),
                422
            );
        }

        $compOff = UserWeeklyOffCompensation::where('id', $request->comp_off_id)
            ->where('user_id', $userId)
            ->where('status', 'available')
            ->first();

        if (!$compOff) {
            return response()->json(
                apiErrorResponse('Comp-off not found, already used, or expired'),
                404
            );
        }

        // Validate: use_date must fall within valid_from → expires_on
        $validFrom  = Carbon::parse($compOff->valid_from);
        $expiresOn  = Carbon::parse($compOff->expires_on)->endOfDay();

        if ($useDate->lt($validFrom) || $useDate->gt($expiresOn)) {
            return response()->json(
                apiErrorResponse(
                    "Comp-off can only be used between " .
                    $compOff->valid_from . " and " . $compOff->expires_on .
                    " (the week following " . $compOff->worked_date . ")"
                ),
                422
            );
        }

        // Guard: max 2 weekly offs in a week (Sun + 1 comp-off max)
        $weekStart = $useDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $useDate->copy()->endOfWeek(Carbon::SATURDAY);

        $compOffsUsedThisWeek = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'used')
            ->whereBetween('used_on', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->count();

        if ($compOffsUsedThisWeek >= 1) {
            return response()->json(
                apiErrorResponse(
                    'You have already used a comp-off this week. ' .
                    'Maximum 1 comp-off per week is allowed.'
                ),
                422
            );
        }

        DB::beginTransaction();
        try {
            $compOff->update([
                'used_on' => $request->use_date,
                'status'  => 'used',
            ]);

            // Create/update attendance row for the comp-off date
            $attendance = UserAttendance::where('user_id', $userId)
                ->whereDate('in_date', $request->use_date)
                ->first();

            if (!$attendance) {
                UserAttendance::create([
                    'user_id' => $userId,
                    'in_date' => $request->use_date,
                    'in_time' => null,
                    'status'  => self::STATUS_COMP_OFF,
                ]);
            } else {
                $attendance->update(['status' => self::STATUS_COMP_OFF]);
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse(
                    'Comp-off used successfully for ' . $request->use_date,
                    ['comp_off' => $compOff->fresh()]
                ),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣2️⃣  AVAILABLE COMP-OFFS
    //      GET v2/attendance/weekly-off/available
    // ─────────────────────────────────────────────────────────────────────────
    public function availableCompOffs(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $this->resp['user']['id'];

        // Auto-expire before fetching
        $this->expireStaleCompOffs($userId);

        $compOffs = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'available')
            ->orderBy('expires_on', 'asc')
            ->get();

        return response()->json(
            apiSuccessResponse('Available comp-offs fetched', ['comp_offs' => $compOffs]),
            200
        );
    }

    /**
     * Mark all expired comp-offs for the user.
     */
    protected function expireStaleCompOffs(int $userId): void
    {
        UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'available')
            ->where('expires_on', '<', Carbon::today()->toDateString())
            ->update(['status' => 'expired']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣3️⃣  ATTENDANCE DETAIL (SINGLE RECORD)
    //      GET v2/attendance/detail/{id}
    // ─────────────────────────────────────────────────────────────────────────
    public function attendanceDetail($id)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        if (!is_numeric($id)) {
            return response()->json(apiErrorResponse('Invalid ID'), 422);
        }

        $userId = $this->resp['user']['id'];

        $attendance = UserAttendance::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$attendance) {
            return response()->json(apiErrorResponse('Attendance not found'), 404);
        }

        return response()->json(
            apiSuccessResponse('Attendance detail fetched', ['attendance' => $attendance]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣4️⃣  SUMMARY (MONTHLY STATS)
    //      GET v2/attendance/summary?month=4&year=2026
    // ─────────────────────────────────────────────────────────────────────────
    public function summary(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId   = $this->resp['user']['id'];
        $month    = (int) $request->query('month', now()->month);
        $year     = (int) $request->query('year',  now()->year);
        $userCity = $this->getUserCity($userId);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();
        $today     = Carbon::today();

        // Count working days so far (excluding Sundays & holidays)
        $holidays = HolidayList::where('is_active', true)
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) $q->orWhere('city', $userCity);
            })
            ->pluck('date')
            ->toArray();

        $totalWorkingDays = 0;
        for ($d = $startDate->copy(); $d->lte($endDate) && $d->lte($today); $d->addDay()) {
            if ($d->dayOfWeek !== Carbon::SUNDAY && !in_array($d->toDateString(), $holidays)) {
                $totalWorkingDays++;
            }
        }

        $presentDays = UserAttendance::where('user_id', $userId)
            ->whereMonth('in_date', $month)->whereYear('in_date', $year)
            ->where('status', self::STATUS_PRESENT)
            ->distinct('in_date')->count('in_date');

        $leaveDays = UserLeave::where('user_id', $userId)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->where('status', 'approved')
            ->sum('quota_deducted');

        $compOffDays = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'used')
            ->whereMonth('used_on', $month)->whereYear('used_on', $year)
            ->count();

        return response()->json(
            apiSuccessResponse('Summary fetched', [
                'month'              => $month,
                'year'               => $year,
                'total_working_days' => $totalWorkingDays,
                'present_days'       => $presentDays,
                'leave_days'         => (float) $leaveDays,
                'comp_off_days'      => $compOffDays,
                'absent_days'        => max(0, $totalWorkingDays - $presentDays - $leaveDays - $compOffDays),
                'holidays_this_month'=> count($holidays),
            ]),
            200
        );
    }
}