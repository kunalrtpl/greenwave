<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\UserAttendance;
use App\LeaveType;
use App\UserLeaveQuota;
use App\UserLeave;
use App\UserLeaveSetting;
use App\UserElAccrualLog;
use App\HolidayList;
use App\UserWeeklyOffCompensation;
use Validator;
use DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $resp;

    // ─────────────────────────────────────────────────────────────────────────
    // STATUS CONSTANTS — canonical list, must match AdminAttendanceController
    // ─────────────────────────────────────────────────────────────────────────
    const STATUS_PRESENT    = 'Full Day Present';
    const STATUS_HALF_LWP   = '1/2 Present + 1/2 LWP';
    const STATUS_HALF_LEAVE = '1/2 Present + 1/2 Leave';
    const STATUS_FULL_LEAVE = 'Allowed Full Day Leave';
    const STATUS_LWP_UNINF  = 'LWP (Uninformed Absence)';       // replaces STATUS_ABSENT
    const STATUS_LWP_UNAPP  = 'LWP (Unapproved Leave)';
    const STATUS_LWP_EXCESS = 'LWP (Leave in excess of quota)';
    const STATUS_HOLIDAY    = 'Allowed Holiday';                 // was 'Holiday'
    const STATUS_COMP_OFF   = 'Compensatory Weekly Off';         // was 'Comp Off'
    const STATUS_WEEKLY_OFF = 'Weekly Off';

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    protected function getFinancialYear($date = null): string
    {
        $d = $date ? Carbon::parse($date) : Carbon::now();
        if ($d->month >= 4) {
            return $d->year . '-' . substr($d->year + 1, -2);
        }
        return ($d->year - 1) . '-' . substr($d->year, -2);
    }

    protected function ensureQuota(int $userId, int $leaveTypeId, string $financialYear): UserLeaveQuota
    {
        $setting = $this->ensureUserLeaveSetting($userId, $leaveTypeId, $financialYear);

        return UserLeaveQuota::firstOrCreate(
            [
                'user_id'        => $userId,
                'leave_type_id'  => $leaveTypeId,
                'financial_year' => $financialYear,
            ],
            [
                'total_quota' => (float) ($setting->annual_quota ?? 0),
                'used_quota'  => 0,
            ]
        );
    }

    protected function ensureUserLeaveSetting(int $userId, int $leaveTypeId, string $fy): UserLeaveSetting
    {
        $leaveType = LeaveType::find($leaveTypeId);
        $isEL      = $leaveType && $leaveType->code === 'EL';

        return UserLeaveSetting::firstOrCreate(
            [
                'user_id'        => $userId,
                'leave_type_id'  => $leaveTypeId,
                'financial_year' => $fy,
            ],
            [
                'annual_quota'        => $isEL ? 5.0 : (float) ($leaveType->default_quota ?? 0),
                'monthly_accrual'     => $isEL ? 1.0  : null,
                'carry_forward'       => $isEL ? true : false,
                'carry_forward_limit' => $isEL ? 10.0 : 0,
            ]
        );
    }

    protected function getUserCity(int $userId): ?string
    {
        return DB::table('users')->where('id', $userId)->value('base_city');
    }

    protected function isHoliday(string $date, ?string $userCity): bool
    {
        $carbonDate = Carbon::parse($date);
        $monthDay   = $carbonDate->format('m-d');

        return HolidayList::where('is_active', true)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) {
                    $q->orWhere('city', $userCity);
                }
            })
            ->where(function ($q) use ($date, $monthDay) {
                $q->where('date', $date)
                  ->orWhere(function ($r) use ($monthDay) {
                      $r->where('is_recurring', true)
                        ->whereRaw("DATE_FORMAT(date, '%m-%d') = ?", [$monthDay]);
                  });
            })
            ->exists();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣  MARK IN
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

            // Sunday worked → comp-off
            if (Carbon::parse($inDate)->dayOfWeek === Carbon::SUNDAY) {
                $alreadyHasComp = UserWeeklyOffCompensation::where('user_id', $userId)
                    ->where('worked_date', $inDate)->exists();

                if (!$alreadyHasComp) {
                    $workedCarbon = Carbon::parse($inDate);
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
    // 2️⃣  MARK OUT
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
            ->where('user_id', $userId)->first();

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
    // 3️⃣  APPLY LEAVE (multi-date)
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
            // PHASE 1: Pre-validate all quotas
            $quotaMap = [];
            foreach ($request->leaves as $leave) {
                $leaveType = LeaveType::find($leave['leave_type_id']);
                if (!$leaveType || !$leaveType->is_active) {
                    DB::rollBack();
                    return response()->json(
                        apiErrorResponse("Leave type ID {$leave['leave_type_id']} is invalid or inactive"), 422
                    );
                }

                // ML (Miscellaneous) and no-quota types skip balance check
                if ($leaveType->has_quota) {
                    $fy     = $this->getFinancialYear($leave['date']);
                    $key    = $leave['leave_type_id'] . '_' . $fy;
                    $deduct = ($leave['leave_duration'] === 'half_day') ? 0.5 : 1.0;

                    if (!isset($quotaMap[$key])) {
                        $quota = $this->ensureQuota($userId, $leave['leave_type_id'], $fy);
                        $quotaMap[$key] = $quota->total_quota - $quota->used_quota;
                    }
                    $quotaMap[$key] -= $deduct;

                    if ($quotaMap[$key] < 0) {
                        DB::rollBack();
                        return response()->json(
                            apiErrorResponse("Insufficient {$leaveType->name} balance. Please check your quota."),
                            422
                        );
                    }
                }
            }

            // PHASE 2: Apply leaves
            $appliedLeaves = [];
            foreach ($request->leaves as $leave) {
                $leaveType        = LeaveType::find($leave['leave_type_id']);
                $fy               = $this->getFinancialYear($leave['date']);
                $deduct           = ($leave['leave_duration'] === 'half_day') ? 0.5 : 1.0;
                $attendanceStatus = ($leave['leave_duration'] === 'full_day')
                    ? self::STATUS_FULL_LEAVE
                    : self::STATUS_HALF_LEAVE;

                $attendance = UserAttendance::where('user_id', $userId)
                    ->whereDate('in_date', $leave['date'])
                    ->orderBy('id', 'desc')->first();

                if (!$attendance) {
                    $attendance = UserAttendance::create([
                        'user_id' => $userId,
                        'in_date' => $leave['date'],
                        'in_time' => null,
                        'status'  => $attendanceStatus,
                    ]);
                } else {
                    $attendance->update(['status' => $attendanceStatus]);
                }

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
        $leave  = UserLeave::where('id', $request->id)->where('user_id', $userId)->first();

        if (!$leave) {
            return response()->json(apiErrorResponse('Leave record not found'), 404);
        }
        if ($leave->status === 'cancelled') {
            return response()->json(apiErrorResponse('Leave is already cancelled'), 422);
        }

        DB::beginTransaction();
        try {
            $leaveType = LeaveType::find($leave->leave_type_id);

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

            if ($leave->attendance_id) {
                $attendance = UserAttendance::find($leave->attendance_id);
                if ($attendance) {
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
                apiSuccessResponse('Leave cancelled and quota restored successfully'), 200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5️⃣  ATTENDANCE LIST
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
        if ($request->filled('employee_id')) $userId = $request->employee_id;

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
            apiSuccessResponse('Leave list fetched', ['leaves' => $leaves]), 200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7️⃣  LEAVE QUOTA
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
            $quota   = $this->ensureQuota($userId, $leaveType->id, $financialYear);
            $setting = UserLeaveSetting::where('user_id', $userId)
                ->where('leave_type_id', $leaveType->id)
                ->where('financial_year', $financialYear)
                ->first();

            $row = [
                'leave_type_id'         => $leaveType->id,
                'leave_type_name'       => $leaveType->name,
                'leave_type_code'       => $leaveType->code,
                'color'                 => $leaveType->color,
                'quota_editable'        => $leaveType->quota_editable,
                'financial_year'        => $financialYear,
                'total_quota'           => (float) $quota->total_quota,
                'used_quota'            => (float) $quota->used_quota,
                'remaining_quota'       => max(0, (float) $quota->total_quota - (float) $quota->used_quota),
                'annual_quota_override' => $setting ? $setting->annual_quota : null,
            ];

            if ($leaveType->code === 'EL' && $setting) {
                $row['el_settings'] = [
                    'monthly_accrual'     => $setting->monthly_accrual,
                    'carry_forward'       => $setting->carry_forward,
                    'carry_forward_limit' => $setting->carry_forward_limit,
                ];
            }

            return $row;
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
    // EL ACCRUAL HISTORY
    // ─────────────────────────────────────────────────────────────────────────
    public function elAccrualHistory(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId        = $this->resp['user']['id'];
        $financialYear = $request->filled('financial_year')
            ? $request->financial_year
            : $this->getFinancialYear();

        $elType = LeaveType::where('code', 'EL')->first();
        if (!$elType) {
            return response()->json(apiErrorResponse('EL leave type not configured'), 404);
        }

        $logs = UserElAccrualLog::where('user_id', $userId)
            ->where('leave_type_id', $elType->id)
            ->where('financial_year', $financialYear)
            ->orderBy('accrual_year')
            ->orderBy('accrual_month')
            ->get();

        return response()->json(
            apiSuccessResponse('EL accrual history fetched', [
                'financial_year' => $financialYear,
                'logs'           => $logs,
            ]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // QUOTA HISTORY (all financial years)
    // ─────────────────────────────────────────────────────────────────────────
    public function quotaHistory(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $this->resp['user']['id'];

        $quotas = UserLeaveQuota::with('leaveType')
            ->where('user_id', $userId)
            ->orderBy('financial_year', 'desc')
            ->get()
            ->groupBy('financial_year');

        $history = $quotas->map(function ($rows, $fy) {
            return [
                'financial_year' => $fy,
                'leave_balances' => $rows->map(function ($q) {
                    return [
                        'leave_type_id'   => $q->leave_type_id,
                        'leave_type_name' => $q->leaveType ? $q->leaveType->name : null,
                        'leave_type_code' => $q->leaveType ? $q->leaveType->code : null,
                        'total_quota'     => (float) $q->total_quota,
                        'used_quota'      => (float) $q->used_quota,
                        'remaining_quota' => max(0, (float) $q->total_quota - (float) $q->used_quota),
                    ];
                })->values(),
            ];
        })->values();

        return response()->json(
            apiSuccessResponse('Quota history fetched', ['history' => $history]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAVE LEAVE SETTINGS PER USER
    // ─────────────────────────────────────────────────────────────────────────
    public function saveLeaveSettings(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $rules = [
            'user_id'                        => 'required|integer|exists:users,id',
            'financial_year'                 => 'required|string|regex:/^\d{4}-\d{2}$/',
            'settings'                       => 'required|array|min:1',
            'settings.*.leave_type_id'       => 'required|integer|exists:leave_types,id',
            'settings.*.annual_quota'        => 'nullable|numeric|min:0',
            'settings.*.monthly_accrual'     => 'nullable|numeric|min:0|max:30',
            'settings.*.carry_forward'       => 'nullable|boolean',
            'settings.*.carry_forward_limit' => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        DB::beginTransaction();
        try {
            $savedSettings = [];

            foreach ($request->settings as $item) {
                $leaveType = LeaveType::find($item['leave_type_id']);

                // EL total_quota is cron-managed — strip annual_quota if passed
                if ($leaveType && $leaveType->code === 'EL' && isset($item['annual_quota'])) {
                    unset($item['annual_quota']);
                }

                $setting = UserLeaveSetting::updateOrCreate(
                    [
                        'user_id'        => $request->user_id,
                        'leave_type_id'  => $item['leave_type_id'],
                        'financial_year' => $request->financial_year,
                    ],
                    [
                        'annual_quota'        => $item['annual_quota']        ?? null,
                        'monthly_accrual'     => $item['monthly_accrual']     ?? null,
                        'carry_forward'       => $item['carry_forward']       ?? false,
                        'carry_forward_limit' => $item['carry_forward_limit'] ?? 0,
                    ]
                );

                // Sync live quota total for SL/CL when annual_quota changes
                if ($leaveType && $leaveType->code !== 'EL' && isset($item['annual_quota'])) {
                    $existingQuota = UserLeaveQuota::where('user_id', $request->user_id)
                        ->where('leave_type_id', $item['leave_type_id'])
                        ->where('financial_year', $request->financial_year)
                        ->first();

                    if ($existingQuota) {
                        $existingQuota->update(['total_quota' => $item['annual_quota']]);
                    }
                }

                $savedSettings[] = $setting->load('leaveType');
            }

            DB::commit();

            return response()->json(
                apiSuccessResponse('Leave settings saved successfully', ['settings' => $savedSettings]),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET LEAVE SETTINGS FOR A USER
    // ─────────────────────────────────────────────────────────────────────────
    public function getLeaveSettings(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $request->filled('user_id')
            ? (int) $request->user_id
            : $this->resp['user']['id'];

        $financialYear = $request->filled('financial_year')
            ? $request->financial_year
            : $this->getFinancialYear();

        $leaveTypes = LeaveType::where('is_active', true)->orderBy('sort_order')->get();

        $data = $leaveTypes->map(function ($lt) use ($userId, $financialYear) {
            $setting = UserLeaveSetting::where('user_id', $userId)
                ->where('leave_type_id', $lt->id)
                ->where('financial_year', $financialYear)
                ->first();

            return [
                'leave_type_id'       => $lt->id,
                'leave_type_name'     => $lt->name,
                'leave_type_code'     => $lt->code,
                'has_quota'           => $lt->has_quota,
                'quota_editable'      => $lt->quota_editable,
                'global_default'      => $lt->default_quota,
                'annual_quota'        => $setting ? $setting->annual_quota : null,
                'monthly_accrual'     => $setting ? $setting->monthly_accrual : null,
                'carry_forward'       => $setting ? $setting->carry_forward : null,
                'carry_forward_limit' => $setting ? $setting->carry_forward_limit : null,
                'setting_exists'      => !is_null($setting),
            ];
        });

        return response()->json(
            apiSuccessResponse('Leave settings fetched', [
                'user_id'        => $userId,
                'financial_year' => $financialYear,
                'settings'       => $data,
            ]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8️⃣  LEAVE TYPES
    // ─────────────────────────────────────────────────────────────────────────
    public function leaveTypes(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'has_quota', 'quota_editable', 'color', 'default_quota']);

        return response()->json(
            apiSuccessResponse('Leave types fetched', ['leave_types' => $leaveTypes]), 200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9️⃣  HOLIDAYS
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
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) $q->orWhere('city', $userCity);
            })
            ->where(function ($q) use ($year) {
                $q->whereYear('date', $year)->orWhere('is_recurring', true);
            })
            ->orderByRaw("DATE_FORMAT(date, '%m-%d') asc")
            ->get()
            ->map(function ($h) use ($year) {
                $h->display_date = $h->is_recurring
                    ? $year . '-' . Carbon::parse($h->date)->format('m-d')
                    : $h->date;
                return $h;
            });

        return response()->json(
            apiSuccessResponse('Holidays fetched', [
                'year'     => $year,
                'city'     => $userCity,
                'holidays' => $holidays,
            ]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 🔟  CALENDAR VIEW
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
        $this->expireStaleCompOffs($userId);

        $attendances = UserAttendance::where('user_id', $userId)
            ->whereMonth('in_date', $month)->whereYear('in_date', $year)
            ->get()->groupBy(fn($a) => Carbon::parse($a->in_date)->toDateString());

        $leaves = UserLeave::with('leaveType')
            ->where('user_id', $userId)->where('status', 'approved')
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->get()->groupBy(fn($l) => Carbon::parse($l->date)->toDateString());

        $monthDay    = sprintf('%02d', $month);
        $holidayRows = HolidayList::where('is_active', true)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) $q->orWhere('city', $userCity);
            })
            ->where(function ($q) use ($month, $year, $monthDay) {
                $q->where(function ($inner) use ($month, $year) {
                    $inner->whereMonth('date', $month)->whereYear('date', $year);
                })->orWhere(function ($inner) use ($monthDay) {
                    $inner->where('is_recurring', true)
                          ->whereRaw("DATE_FORMAT(date, '%m') = ?", [$monthDay]);
                });
            })->get();

        $holidays = collect();
        foreach ($holidayRows as $h) {
            $ds = $h->is_recurring
                ? ($year . '-' . sprintf('%02d', $month) . '-' . Carbon::parse($h->date)->format('d'))
                : Carbon::parse($h->date)->toDateString();
            $holidays[$ds] = $h;
        }

        $compOffs = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'used')->whereNotNull('used_on')
            ->whereMonth('used_on', $month)->whereYear('used_on', $year)
            ->get()->keyBy(fn($c) => Carbon::parse($c->used_on)->toDateString());

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();
        $today     = Carbon::today();
        $calendar  = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $ds             = $date->toDateString();
            $isSunday       = ($date->dayOfWeek === Carbon::SUNDAY);
            $isHoliday      = isset($holidays[$ds]);
            $isCompOff      = isset($compOffs[$ds]);
            $isFuture       = $date->gt($today);
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
            apiSuccessResponse('Calendar fetched', ['month' => $month, 'year' => $year, 'calendar' => $calendar]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // computeDayStatus — UPDATED
    // Changes from old version:
    //   STATUS_HOLIDAY  → 'Allowed Holiday'        (was 'Holiday')
    //   STATUS_COMP_OFF → 'Compensatory Weekly Off' (was 'Comp Off')
    //   No more STATUS_ABSENT — past no-punch now returns STATUS_LWP_UNINF
    // ─────────────────────────────────────────────────────────────────────────
    protected function computeDayStatus(
        string $date,
        bool $isSunday,
        bool $isHoliday,
        bool $isCompOff,
        $dayAttendances,
        $dayLeaves,
        bool $isFuture
    ): ?string {
        if ($isHoliday) return self::STATUS_HOLIDAY;   // 'Allowed Holiday'
        if ($isCompOff) return self::STATUS_COMP_OFF;  // 'Compensatory Weekly Off'

        if ($dayLeaves->isNotEmpty()) {
            $first = $dayLeaves->first();
            return ($first->leave_duration === 'full_day')
                ? self::STATUS_FULL_LEAVE   // 'Allowed Full Day Leave'
                : self::STATUS_HALF_LEAVE;  // '1/2 Present + 1/2 Leave'
        }

        if ($isSunday && $dayAttendances->isEmpty()) return self::STATUS_WEEKLY_OFF;

        if ($dayAttendances->isNotEmpty()) {
            // Return whatever the DB says — admin may have set any status
            return $dayAttendances->first()->status ?? self::STATUS_PRESENT;
        }

        // Past date, no record, not Sunday/holiday → LWP Uninformed
        return $isFuture ? null : self::STATUS_LWP_UNINF;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣1️⃣  USE COMP-OFF — UPDATED: status = 'Compensatory Weekly Off'
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

        if ($useDate->dayOfWeek === Carbon::SUNDAY) {
            return response()->json(apiErrorResponse('Comp-off cannot be used on a Sunday'), 422);
        }

        $compOff = UserWeeklyOffCompensation::where('id', $request->comp_off_id)
            ->where('user_id', $userId)->where('status', 'available')->first();

        if (!$compOff) {
            return response()->json(apiErrorResponse('Comp-off not found, already used, or expired'), 404);
        }

        $validFrom = Carbon::parse($compOff->valid_from);
        $expiresOn = Carbon::parse($compOff->expires_on)->endOfDay();

        if ($useDate->lt($validFrom) || $useDate->gt($expiresOn)) {
            return response()->json(
                apiErrorResponse(
                    "Comp-off can only be used between {$compOff->valid_from} and {$compOff->expires_on} " .
                    "(following week of {$compOff->worked_date})"
                ),
                422
            );
        }

        $weekStart    = $useDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd      = $useDate->copy()->endOfWeek(Carbon::SATURDAY);
        $usedThisWeek = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'used')
            ->whereBetween('used_on', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->count();

        if ($usedThisWeek >= 1) {
            return response()->json(
                apiErrorResponse('You have already used a comp-off this week. Maximum 1 per week.'),
                422
            );
        }

        DB::beginTransaction();
        try {
            $compOff->update(['used_on' => $request->use_date, 'status' => 'used']);

            $attendance = UserAttendance::where('user_id', $userId)
                ->whereDate('in_date', $request->use_date)->first();

            if (!$attendance) {
                UserAttendance::create([
                    'user_id' => $userId,
                    'in_date' => $request->use_date,
                    'in_time' => null,
                    'status'  => self::STATUS_COMP_OFF, // 'Compensatory Weekly Off'
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
    // ─────────────────────────────────────────────────────────────────────────
    public function availableCompOffs(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $this->resp['user']['id'];
        $this->expireStaleCompOffs($userId);

        $compOffs = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'available')->orderBy('expires_on', 'asc')->get();

        return response()->json(
            apiSuccessResponse('Available comp-offs fetched', ['comp_offs' => $compOffs]), 200
        );
    }

    protected function expireStaleCompOffs(int $userId): void
    {
        UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'available')
            ->where('expires_on', '<', Carbon::today()->toDateString())
            ->update(['status' => 'expired']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣3️⃣  ATTENDANCE DETAIL
    // ─────────────────────────────────────────────────────────────────────────
    public function attendanceDetail($id)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        if (!is_numeric($id)) {
            return response()->json(apiErrorResponse('Invalid ID'), 422);
        }

        $attendance = UserAttendance::where('id', $id)
            ->where('user_id', $this->resp['user']['id'])->first();

        if (!$attendance) {
            return response()->json(apiErrorResponse('Attendance not found'), 404);
        }

        return response()->json(
            apiSuccessResponse('Attendance detail fetched', ['attendance' => $attendance]), 200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣4️⃣  MONTHLY SUMMARY
    // ─────────────────────────────────────────────────────────────────────────
    public function summary(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId    = $this->resp['user']['id'];
        $month     = (int) $request->query('month', now()->month);
        $year      = (int) $request->query('year',  now()->year);
        $userCity  = $this->getUserCity($userId);
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();
        $today     = Carbon::today();

        $holidays = HolidayList::where('is_active', true)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) $q->orWhere('city', $userCity);
            })
            ->where(function ($q) use ($month, $year) {
                $q->where(function ($inner) use ($month, $year) {
                    $inner->whereMonth('date', $month)->whereYear('date', $year);
                })->orWhere('is_recurring', true);
            })
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('m-d'))
            ->toArray();

        $totalWorkingDays = 0;
        for ($d = $startDate->copy(); $d->lte($endDate) && $d->lte($today); $d->addDay()) {
            if ($d->dayOfWeek !== Carbon::SUNDAY && !in_array($d->format('m-d'), $holidays)) {
                $totalWorkingDays++;
            }
        }

        $presentDays = UserAttendance::where('user_id', $userId)
            ->whereMonth('in_date', $month)->whereYear('in_date', $year)
            ->where('status', self::STATUS_PRESENT)
            ->distinct('in_date')->count('in_date');

        $leaveDays = UserLeave::where('user_id', $userId)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->where('status', 'approved')->sum('quota_deducted');

        $compOffDays = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'used')
            ->whereMonth('used_on', $month)->whereYear('used_on', $year)->count();

        $lwpDays = max(0, $totalWorkingDays - $presentDays - $leaveDays - $compOffDays);

        return response()->json(
            apiSuccessResponse('Summary fetched', [
                'month'               => $month,
                'year'                => $year,
                'total_working_days'  => $totalWorkingDays,
                'present_days'        => $presentDays,
                'leave_days'          => (float) $leaveDays,
                'comp_off_days'       => $compOffDays,
                'lwp_days'            => $lwpDays,  // renamed from absent_days
                'absent_days'         => $lwpDays,  // kept for backward compatibility
                'holidays_this_month' => count($holidays),
            ]),
            200
        );
    }
}