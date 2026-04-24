<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\AttendanceStatus;
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

/**
 * API AttendanceController
 *
 * All status strings come from App\AttendanceStatus — never hard-coded here.
 *
 * Key Behaviours (Mark In):
 * ─────────────────────────────────────────────────────────────
 * FULL_LEAVE / HALF_DAY_LEAVE / HALF_LEAVE_LWP
 *   → Auto-cancel the leave, restore quota, allow Mark In as Present.
 *
 * HALF_LEAVE / HALF_LWP (user already punched in once for this day)
 *   → BLOCKED. Half-day days allow only 1 Mark In / Mark Out session.
 *
 * LWP_UNINF / LWP_UNAPP / LWP_EXCESS (admin-set)
 *   → BLOCKED. Admin must change status before employee can punch.
 *
 * PRESENT (last punch is complete — out_time set)
 *   → Allowed. Multiple swipes only for full present days.
 *
 * No record / WEEKLY_OFF / HOLIDAY / COMP_OFF
 *   → Allowed (working on an off day creates a new record).
 */
class AttendanceController extends Controller
{
    protected $resp;

    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CORE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    protected function getFinancialYear($date = null): string
    {
        $d = $date ? Carbon::parse($date) : Carbon::now();
        return $d->month >= 4
            ? $d->year . '-' . substr($d->year + 1, -2)
            : ($d->year - 1) . '-' . substr($d->year, -2);
    }

    protected function ensureUserLeaveSetting(int $userId, int $leaveTypeId, string $fy): UserLeaveSetting
    {
        $leaveType = LeaveType::find($leaveTypeId);
        $isEL      = $leaveType && $leaveType->code === 'EL';

        return UserLeaveSetting::firstOrCreate(
            ['user_id' => $userId, 'leave_type_id' => $leaveTypeId, 'financial_year' => $fy],
            [
                'annual_quota'        => $isEL ? 0 : (float)($leaveType->default_quota ?? 0),
                'monthly_accrual'     => $isEL ? 1.0 : null,
                'carry_forward'       => $isEL,
                'carry_forward_limit' => $isEL ? 10.0 : 0,
            ]
        );
    }

    protected function ensureQuota(int $userId, int $leaveTypeId, string $fy): UserLeaveQuota
    {
        $setting = $this->ensureUserLeaveSetting($userId, $leaveTypeId, $fy);
        return UserLeaveQuota::firstOrCreate(
            ['user_id' => $userId, 'leave_type_id' => $leaveTypeId, 'financial_year' => $fy],
            ['total_quota' => (float)($setting->annual_quota ?? 0), 'used_quota' => 0]
        );
    }

    /**
     * Batch-ensure default quotas for a user across all active leave types.
     * Single bulk INSERT — no N+1.
     */
    protected function ensureDefaultQuotasForUser(int $userId, string $fy): void
    {
        $leaveTypes = LeaveType::where('is_active', true)->where('has_quota', true)->get();
        $existing   = UserLeaveQuota::where('user_id', $userId)
            ->where('financial_year', $fy)
            ->pluck('leave_type_id')
            ->toArray();

        $toCreate = [];
        foreach ($leaveTypes as $lt) {
            if (in_array($lt->id, $existing)) continue;
            $setting    = $this->ensureUserLeaveSetting($userId, $lt->id, $fy);
            $toCreate[] = [
                'user_id'        => $userId,
                'leave_type_id'  => $lt->id,
                'financial_year' => $fy,
                'total_quota'    => (float)($setting->annual_quota ?? 0),
                'used_quota'     => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
        if ($toCreate) {
            UserLeaveQuota::insert($toCreate);
        }
    }

    protected function getUserCity(int $userId): ?string
    {
        return DB::table('users')->where('id', $userId)->value('base_city');
    }

    /**
     * Load holidays for a month keyed by date string.
     * Supports is_recurring (stored with year 2000).
     */
    protected function getHolidaysForMonth(int $month, int $year, ?string $userCity): array
    {
        $monthPad    = sprintf('%02d', $month);
        $holidayRows = HolidayList::where('is_active', true)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) $q->orWhere('city', $userCity);
            })
            ->where(function ($q) use ($month, $year, $monthPad) {
                $q->where(function ($i) use ($month, $year) {
                    $i->whereMonth('date', $month)->whereYear('date', $year);
                })->orWhere(function ($i) use ($monthPad) {
                    $i->where('is_recurring', true)
                      ->whereRaw("DATE_FORMAT(date,'%m')=?", [$monthPad]);
                });
            })
            ->get();

        $map = [];
        foreach ($holidayRows as $h) {
            $ds = $h->is_recurring
                ? ($year . '-' . sprintf('%02d', $month) . '-' . Carbon::parse($h->date)->format('d'))
                : Carbon::parse($h->date)->toDateString();
            $map[$ds] = $h;
        }
        return $map;
    }

    /**
     * Compute the canonical status for a calendar day.
     * Priority: Holiday > Comp Off > Full Leave > Half Leave > Weekly Off > DB status > LWP
     */
    protected function computeDayStatus(
        string $date,
        bool $isSunday,
        bool $isHoliday,
        bool $isCompOff,
        $dayAttendances,
        $dayLeaves,
        bool $isFuture,
        bool $isToday = false          // ← ADD THIS
    ): ?string {
        if ($isHoliday) return AttendanceStatus::HOLIDAY;
        if ($isCompOff) return AttendanceStatus::COMP_OFF;

        if ($dayLeaves->isNotEmpty()) {
            $first = $dayLeaves->first();
            if ($first->leave_duration === 'full_day') {
                return AttendanceStatus::FULL_LEAVE;
            }
            $hasPunch = $dayAttendances->filter(function ($a) {
                return !is_null($a->in_time);
            })->isNotEmpty();
            return AttendanceStatus::resolveHalfDayLeaveStatus($isFuture, $hasPunch, $isToday); // ← pass isToday
        }

        if ($isSunday && $dayAttendances->isEmpty()) return AttendanceStatus::WEEKLY_OFF;

        if ($dayAttendances->isNotEmpty()) {
            $firstAtt = $dayAttendances->first();
            $current  = $firstAtt->status ?? AttendanceStatus::PRESENT;
            if (AttendanceStatus::isHalfDay($current)) {
                $hasPunch = $dayAttendances->filter(function ($a) {
                    return !is_null($a->in_time);
                })->isNotEmpty();
                $resynced = AttendanceStatus::resyncHalfDayStatus(
                    $current, $hasPunch, $isFuture, $isToday   // ← pass isToday
                );
                if ($resynced) {
                    $ids = $dayAttendances->pluck('id')->toArray();
                    UserAttendance::whereIn('id', $ids)->update(['status' => $resynced]);
                    return $resynced;
                }
            }
            return $current;
        }

        return $isFuture ? null : AttendanceStatus::LWP_UNINF;
    }

    protected function expireStaleCompOffs(int $userId): void
    {
        UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'available')
            ->where('expires_on', '<', Carbon::today()->toDateString())
            ->update(['status' => 'expired']);
    }

    /**
     * AUTO-CANCEL LEAVE HELPER
     *
     * Cancels all approved leaves for a user on a given date and
     * restores the quota for each. Returns a summary of what was cancelled.
     *
     * Used by markIn() when a user shows up on a day they booked as leave.
     *
     * @param  int    $userId
     * @param  string $date   Y-m-d
     * @return array  ['cancelled_count' => int, 'restored_days' => float, 'types' => string[]]
     */
    protected function autoCancelLeavesForDate(int $userId, string $date): array
    {
        $leaves = UserLeave::where('user_id', $userId)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->get();

        if ($leaves->isEmpty()) {
            return ['cancelled_count' => 0, 'restored_days' => 0.0, 'types' => []];
        }

        $cancelledCount  = 0;
        $totalRestored   = 0.0;
        $typeNames       = [];

        foreach ($leaves as $leave) {
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

            $leave->update(['status' => 'cancelled']);
            $cancelledCount++;
            $totalRestored += (float)$leave->quota_deducted;
            if ($leaveType) {
                $typeNames[] = $leaveType->name;
            }
        }

        return [
            'cancelled_count' => $cancelledCount,
            'restored_days'   => $totalRestored,
            'types'           => $typeNames,
        ];
    }

    /**
     * GET CURRENT DAY STATUS
     *
     * Returns an array with the effective status of a user's day and
     * metadata needed to make mark-in decisions.
     *
     * @return array{
     *   status: string|null,
     *   has_real_punch: bool,
     *   has_open_punch: bool,
     *   is_half_day: bool,
     *   has_leave: bool,
     *   punch_count: int,
     *   existing_record: UserAttendance|null,
     * }
     */
    protected function getDayState(int $userId, string $date): array
    {
        $today    = Carbon::today();
        $dc       = Carbon::parse($date);
        $isFuture = $dc->gt($today);

        // All attendance records for this date
        $records = UserAttendance::where('user_id', $userId)
            ->whereDate('in_date', $date)
            ->orderBy('id', 'asc')
            ->get();

        // Approved leaves for this date
        $leaves = UserLeave::where('user_id', $userId)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->get();

        // Real punches (have in_time set)
        $realPunches = $records->filter(function ($r) {
            return !is_null($r->in_time);
        });

        // Open punch (in without out)
        $openPunch = $realPunches->first(function ($r) {
            return is_null($r->out_time) && !$r->missed;
        });

        // Determine effective status
        $effectiveStatus = null;
        if ($records->isNotEmpty()) {
            $effectiveStatus = $records->first()->status;
        }

        // Leave overrides if no real punch yet
        if ($leaves->isNotEmpty() && $realPunches->isEmpty()) {
            $firstLeave = $leaves->first();
            if ($firstLeave->leave_duration === 'full_day') {
                $effectiveStatus = AttendanceStatus::FULL_LEAVE;
            } else {
                $effectiveStatus = AttendanceStatus::resolveHalfDayLeaveStatus($isFuture, false);
            }
        }

        return [
            'status'          => $effectiveStatus,
            'has_real_punch'  => $realPunches->isNotEmpty(),
            'has_open_punch'  => !is_null($openPunch),
            'open_punch'      => $openPunch,
            'is_half_day'     => $effectiveStatus ? AttendanceStatus::isHalfDay($effectiveStatus) : false,
            'has_leave'       => $leaves->isNotEmpty(),
            'punch_count'     => $realPunches->count(),
            'records'         => $records,
            'real_punches'    => $realPunches,
            'leaves'          => $leaves,
            'existing_record' => $records->first(), // leave placeholder or first punch
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1️⃣  MARK IN
    //
    // Decision table:
    // ┌─────────────────────────────────┬────────────────────────────────────────────┐
    // │ Current Day Status              │ Action                                     │
    // ├─────────────────────────────────┼────────────────────────────────────────────┤
    // │ No records at all               │ ✅ Allow — create new PRESENT record        │
    // │ FULL_LEAVE (no punch)           │ ✅ Auto-cancel leave → create PRESENT       │
    // │ HALF_DAY_LEAVE (no punch)       │ ✅ Auto-cancel leave → create PRESENT       │
    // │ HALF_LEAVE_LWP (no punch)       │ ✅ Auto-cancel leave → create PRESENT       │
    // │ PRESENT, last punch complete    │ ✅ Allow re-entry (multi-swipe)             │
    // │ PRESENT, has open punch         │ ❌ Block — mark OUT first                   │
    // │ HALF_LEAVE (already punched)    │ ❌ Block — half-day = 1 session only        │
    // │ HALF_LWP   (already punched)    │ ❌ Block — half-day = 1 session only        │
    // │ LWP_UNINF / UNAPP / EXCESS      │ ❌ Block — admin must change status first   │
    // │ WEEKLY_OFF / HOLIDAY / COMP_OFF │ ✅ Allow (working on off day)               │
    // └─────────────────────────────────┴────────────────────────────────────────────┘
    // ─────────────────────────────────────────────────────────────────────────
    public function markIn(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $validator = Validator::make($request->all(), [
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
        ]);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        $userId = $this->resp['user']['id'];
        $inDate = $request->in_date;

        // ── Future date guard ──────────────────────────────────────────────
        if (Carbon::parse($inDate)->gt(Carbon::today())) {
            return response()->json(apiErrorResponse('Cannot mark attendance for a future date.'), 422);
        }

        // ── Load full day state ────────────────────────────────────────────
        $state  = $this->getDayState($userId, $inDate);
        $status = $state['status'];

        // Common punch data payload
        $punchData = [
            'in_time'                         => $request->in_time,
            'in_latitude'                     => $request->in_latitude,
            'in_longitude'                    => $request->in_longitude,
            'in_latitude_longitude_address'   => $request->in_latitude_longitude_address,
            'in_place_of_attendance'          => $request->in_place_of_attendance,
            'in_other'                        => $request->in_other ?? null,
            'in_customer_id'                  => $request->in_customer_id ?? null,
            'in_customer_register_request_id' => $request->in_customer_register_request_id ?? null,
            'in_dealer_id'                    => $request->in_dealer_id ?? null,
        ];

        // ── BLOCK: Admin-set LWP statuses ─────────────────────────────────
        $blockedLwp = [
            AttendanceStatus::LWP_UNINF,
            AttendanceStatus::LWP_UNAPP,
            AttendanceStatus::LWP_EXCESS,
        ];
        if (in_array($status, $blockedLwp)) {
            return response()->json(
                apiErrorResponse(
                    'Attendance cannot be marked. Your status for this date is "' . $status . '". ' .
                    'Please contact your administrator to update it.'
                ),
                422
            );
        }

        // ── BLOCK: Already punched on a half-day leave day ─────────────────
        // HALF_LEAVE = punched + leave  |  HALF_LWP = punched + no leave
        // Both mean: user already has exactly 1 punch session — no second entry.
        if (in_array($status, [AttendanceStatus::HALF_LEAVE, AttendanceStatus::HALF_LWP])) {
            return response()->json(
                apiErrorResponse(
                    'You have already marked attendance for this date (' . $status . '). ' .
                    'Half-day configurations allow only one Mark In / Mark Out session.'
                ),
                422
            );
        }

        // ── BLOCK: Open punch exists ───────────────────────────────────────
        if ($state['has_open_punch']) {
            $openId = $state['open_punch']->id;
            return response()->json(
                apiErrorResponse(
                    'You have an open attendance session (ID: ' . $openId . '). ' .
                    'Please Mark OUT before marking IN again.'
                ),
                422
            );
        }

        DB::beginTransaction();
        try {
            $cancelled  = null;
            $attendance = null;

            // ── AUTO-CANCEL LEAVE + MARK IN ────────────────────────────────
            // ── CASE A: FULL DAY LEAVE → user decided to come in full day ─────────
            // Cancel the leave, restore quota, mark as PRESENT.
            if ($status === AttendanceStatus::FULL_LEAVE) {
                $cancelled = $this->autoCancelLeavesForDate($userId, $inDate);

                $placeholder = $state['records']->first(function ($r) {
                    return is_null($r->in_time);
                });

                if ($placeholder) {
                    $placeholder->update(array_merge($punchData, [
                        'status' => AttendanceStatus::PRESENT,
                    ]));
                    $attendance = $placeholder->fresh();
                } else {
                    $attendance = UserAttendance::create(array_merge($punchData, [
                        'user_id' => $userId,
                        'in_date' => $inDate,
                        'status'  => AttendanceStatus::PRESENT,
                    ]));
                }

                DB::commit();

                $msg = 'Attendance marked. ';
                if ($cancelled && $cancelled['cancelled_count'] > 0) {
                    $msg .= $cancelled['cancelled_count'] . ' leave(s) auto-cancelled and ' .
                            $cancelled['restored_days'] . ' day(s) restored to your quota.';
                }

                return response()->json(
                    apiSuccessResponse($msg, [
                        'attendance'      => $attendance,
                        'leave_cancelled' => $cancelled,
                    ]),
                    200
                );
            }

            // ── CASE B: HALF-DAY LEAVE (not yet punched) → user coming in for the other half ──
            // Keep the leave intact (quota stays deducted), just convert status to HALF_LEAVE.
            $halfDayStatuses = [
                AttendanceStatus::HALF_DAY_LEAVE,
                AttendanceStatus::HALF_LEAVE_LWP,
            ];
            if (in_array($status, $halfDayStatuses)) {
                $placeholder = $state['records']->first(function ($r) {
                    return is_null($r->in_time);
                });

                if ($placeholder) {
                    $placeholder->update(array_merge($punchData, [
                        'status' => AttendanceStatus::HALF_LEAVE,
                    ]));
                    $attendance = $placeholder->fresh();
                } else {
                    $attendance = UserAttendance::create(array_merge($punchData, [
                        'user_id' => $userId,
                        'in_date' => $inDate,
                        'status'  => AttendanceStatus::HALF_LEAVE,
                    ]));
                }

                DB::commit();
                return response()->json(
                    apiSuccessResponse(
                        'Attendance marked. Half-day leave remains active.',
                        ['attendance' => $attendance]
                    ),
                    200
                );
            }

            // ── CASE C: No records / PRESENT (all closed) / WEEKLY_OFF / HOLIDAY / COMP_OFF ──
            // Normal mark in — create a new PRESENT record.
            $attendance = UserAttendance::create(array_merge($punchData, [
                'user_id' => $userId,
                'in_date' => $inDate,
                'status'  => AttendanceStatus::PRESENT,
            ]));

            // Sync any existing records for this date to PRESENT
            if ($state['records']->isNotEmpty()) {
                UserAttendance::where('user_id', $userId)
                    ->whereDate('in_date', $inDate)
                    ->where('id', '!=', $attendance->id)
                    ->update(['status' => AttendanceStatus::PRESENT]);
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

        $validator = Validator::make($request->all(), [
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
        ]);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        $userId     = $this->resp['user']['id'];
        $attendance = UserAttendance::where('id', $request->attendance_id)
            ->where('user_id', $userId)->first();

        if (!$attendance) return response()->json(apiErrorResponse('Attendance record not found'), 404);
        if (!is_null($attendance->out_time)) return response()->json(apiErrorResponse('OUT already marked'), 422);

        DB::beginTransaction();
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

            // ── Sunday comp-off: only if total worked >= 6 hours ──────────
            // We aggregate ALL punch pairs for this Sunday AFTER saving the
            // current out_time so the current session is included.
            $inDate = Carbon::parse($attendance->in_date)->toDateString();

            if (Carbon::parse($inDate)->dayOfWeek === Carbon::SUNDAY) {
                // Re-fetch all completed records for this Sunday including the one we just saved
                $sundayRecords = UserAttendance::where('user_id', $userId)
                    ->whereDate('in_date', $inDate)
                    ->whereNotNull('in_time')
                    ->whereNotNull('out_time')
                    ->get();

                $totalMins = 0;
                foreach ($sundayRecords as $rec) {
                    try {
                        $inDt      = Carbon::parse($rec->in_date . ' ' . $rec->in_time);
                        $outDateStr = $rec->out_date ?? $rec->in_date;
                        $outDt     = Carbon::parse($outDateStr . ' ' . $rec->out_time);
                        $totalMins += $inDt->diffInMinutes($outDt);
                    } catch (\Exception $e) {
                        // Skip malformed records silently
                    }
                }

                // Grant comp-off only once and only if >= 6 hours total
                if ($totalMins >= 360) {
                    $alreadyGranted = UserWeeklyOffCompensation::where('user_id', $userId)
                        ->where('worked_date', $inDate)
                        ->exists();

                    if (!$alreadyGranted) {
                        $workedCarbon = Carbon::parse($inDate);
                        UserWeeklyOffCompensation::create([
                            'user_id'     => $userId,
                            'worked_date' => $inDate,
                            // Valid from the next day (Monday) through the following Saturday
                            'valid_from'  => $workedCarbon->copy()->addDay()->toDateString(),
                            'expires_on'  => $workedCarbon->copy()->addDays(6)->toDateString(),
                            'status'      => 'available',
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(
                apiSuccessResponse('OUT marked successfully', ['attendance' => $attendance->fresh()]),
                200
            );
        } catch (\Exception $e) {
            DB::rollBack();
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

        $validator = Validator::make($request->all(), [
            'leaves'                   => 'required|array|min:1',
            'leaves.*.date'            => 'required|date',
            'leaves.*.leave_type_id'   => 'required|integer|exists:leave_types,id',
            'leaves.*.leave_duration'  => 'required|in:full_day,half_day',
            'leaves.*.half_day_type'   => 'nullable|in:first_half,second_half',
            'leaves.*.remarks'         => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        foreach ($request->leaves as $i => $leave) {
            if ($leave['leave_duration'] === 'half_day' && empty($leave['half_day_type'])) {
                return response()->json(
                    apiErrorResponse("half_day_type required at index {$i} for half_day duration"),
                    422
                );
            }
        }

        $userId = $this->resp['user']['id'];
        $today  = Carbon::today();

        DB::beginTransaction();
        try {
            // ── PHASE 1: Pre-validate quotas ──────────────────────────────
            $quotaMap = [];
            foreach ($request->leaves as $leave) {
                $leaveType = LeaveType::find($leave['leave_type_id']);
                if (!$leaveType || !$leaveType->is_active) {
                    DB::rollBack();
                    return response()->json(
                        apiErrorResponse("Leave type {$leave['leave_type_id']} invalid or inactive"), 422
                    );
                }

                if ($leaveType->has_quota) {
                    $fy     = $this->getFinancialYear($leave['date']);
                    $key    = $leave['leave_type_id'] . '_' . $fy;
                    $deduct = ($leave['leave_duration'] === 'half_day') ? 0.5 : 1.0;

                    if (!isset($quotaMap[$key])) {
                        $quota          = $this->ensureQuota($userId, $leave['leave_type_id'], $fy);
                        $quotaMap[$key] = $quota->total_quota - $quota->used_quota;
                    }
                    $quotaMap[$key] -= $deduct;

                    if ($quotaMap[$key] < 0) {
                        DB::rollBack();
                        return response()->json(
                            apiErrorResponse("Insufficient {$leaveType->name} balance."), 422
                        );
                    }
                }
            }

            // ── PHASE 2: Apply leaves ─────────────────────────────────────
            $appliedLeaves = [];
            foreach ($request->leaves as $leave) {
                $leaveType = LeaveType::find($leave['leave_type_id']);
                $fy        = $this->getFinancialYear($leave['date']);
                $deduct    = ($leave['leave_duration'] === 'half_day') ? 0.5 : 1.0;
                $isFuture  = Carbon::parse($leave['date'])->gt($today);
                $isHalfDay = ($leave['leave_duration'] === 'half_day');

                // Determine correct attendance status
                if ($isHalfDay) {
                    $hasPunch = UserAttendance::where('user_id', $userId)
                        ->whereDate('in_date', $leave['date'])
                        ->whereNotNull('in_time')
                        ->exists();
                    $attendanceStatus = AttendanceStatus::resolveHalfDayLeaveStatus($isFuture, $hasPunch);
                } else {
                    $attendanceStatus = AttendanceStatus::FULL_LEAVE;
                }

                // Upsert attendance record
                $attendance = UserAttendance::where('user_id', $userId)
                    ->whereDate('in_date', $leave['date'])
                    ->orderBy('id', 'desc')
                    ->first();

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
                apiSuccessResponse(count($appliedLeaves) . ' leave(s) applied', ['leaves' => $appliedLeaves]),
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

        $validator = Validator::make($request->all(), ['id' => 'required|integer|exists:user_leaves,id']);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        $userId = $this->resp['user']['id'];
        $leave  = UserLeave::where('id', $request->id)->where('user_id', $userId)->first();

        if (!$leave) return response()->json(apiErrorResponse('Leave not found'), 404);
        if ($leave->status === 'cancelled') return response()->json(apiErrorResponse('Already cancelled'), 422);

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
                if ($quota) $quota->decrement('used_quota', $leave->quota_deducted);
            }

            // Update attendance status
            if ($leave->attendance_id) {
                $attendance = UserAttendance::find($leave->attendance_id);
                if ($attendance) {
                    $otherLeaves = UserLeave::where('attendance_id', $leave->attendance_id)
                        ->where('id', '!=', $leave->id)
                        ->where('status', 'approved')
                        ->count();

                    if ($otherLeaves === 0) {
                        $hasPunch  = !is_null($attendance->in_time);
                        $newStatus = $hasPunch ? AttendanceStatus::PRESENT : AttendanceStatus::LWP_UNINF;
                        $attendance->update(['status' => $newStatus]);
                    }
                }
            }

            $leave->update(['status' => 'cancelled']);
            DB::commit();

            return response()->json(
                apiSuccessResponse('Leave cancelled and quota restored'), 200
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

        $month = (int)$request->query('month', now()->month);
        $year  = (int)$request->query('year',  now()->year);

        if ($month < 1 || $month > 12 || $year < 2000) {
            return response()->json(apiErrorResponse('Invalid month or year'), 422);
        }

        $userId = $this->resp['user']['id'];
        if ($request->filled('employee_id')) $userId = (int)$request->employee_id;

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
        $month  = (int)$request->query('month', now()->month);
        $year   = (int)$request->query('year',  now()->year);

        $leaves = UserLeave::with(['leaveType'])
            ->where('user_id', $userId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(apiSuccessResponse('Leave list fetched', ['leaves' => $leaves]), 200);
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

        // Batch-seed missing quotas silently
        $this->ensureDefaultQuotasForUser($userId, $financialYear);

        $leaveTypes = LeaveType::where('is_active', true)
            ->where('has_quota', true)
            ->orderBy('sort_order')
            ->get();

        $quotaData = $leaveTypes->map(function ($lt) use ($userId, $financialYear) {
            $quota   = $this->ensureQuota($userId, $lt->id, $financialYear);
            $setting = UserLeaveSetting::where('user_id', $userId)
                ->where('leave_type_id', $lt->id)
                ->where('financial_year', $financialYear)
                ->first();

            $row = [
                'leave_type_id'         => $lt->id,
                'leave_type_name'       => $lt->name,
                'leave_type_code'       => $lt->code,
                'color'                 => $lt->color,
                'mobile_colors'         => AttendanceStatus::mobileColor(AttendanceStatus::FULL_LEAVE),
                'quota_editable'        => $lt->quota_editable,
                'financial_year'        => $financialYear,
                'total_quota'           => (float)$quota->total_quota,
                'used_quota'            => (float)$quota->used_quota,
                'remaining_quota'       => max(0, (float)$quota->total_quota - (float)$quota->used_quota),
                'annual_quota_override' => $setting ? $setting->annual_quota : null,
            ];

            if ($lt->code === 'EL' && $setting) {
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
        if (!$elType) return response()->json(apiErrorResponse('EL type not configured'), 404);

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
    // QUOTA HISTORY
    // ─────────────────────────────────────────────────────────────────────────
    public function quotaHistory(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId  = $this->resp['user']['id'];
        $history = UserLeaveQuota::with('leaveType')
            ->where('user_id', $userId)
            ->orderBy('financial_year', 'desc')
            ->get()
            ->groupBy('financial_year')
            ->map(function ($rows, $fy) {
                return [
                    'financial_year' => $fy,
                    'leave_balances' => $rows->map(function ($q) {
                        return [
                            'leave_type_id'   => $q->leave_type_id,
                            'leave_type_name' => $q->leaveType ? $q->leaveType->name : null,
                            'leave_type_code' => $q->leaveType ? $q->leaveType->code : null,
                            'total_quota'     => (float)$q->total_quota,
                            'used_quota'      => (float)$q->used_quota,
                            'remaining_quota' => max(0, (float)$q->total_quota - (float)$q->used_quota),
                        ];
                    })->values(),
                ];
            })->values();

        return response()->json(apiSuccessResponse('Quota history fetched', ['history' => $history]), 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAVE LEAVE SETTINGS
    // ─────────────────────────────────────────────────────────────────────────
    public function saveLeaveSettings(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id'                        => 'required|integer|exists:users,id',
            'financial_year'                 => 'required|string|regex:/^\d{4}-\d{2}$/',
            'settings'                       => 'required|array|min:1',
            'settings.*.leave_type_id'       => 'required|integer|exists:leave_types,id',
            'settings.*.annual_quota'        => 'nullable|numeric|min:0',
            'settings.*.monthly_accrual'     => 'nullable|numeric|min:0|max:30',
            'settings.*.carry_forward'       => 'nullable|boolean',
            'settings.*.carry_forward_limit' => 'nullable|numeric|min:0',
        ]);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        DB::beginTransaction();
        try {
            $savedSettings = [];
            foreach ($request->settings as $item) {
                $leaveType = LeaveType::find($item['leave_type_id']);
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

                if ($leaveType && $leaveType->code !== 'EL' && isset($item['annual_quota'])) {
                    UserLeaveQuota::where('user_id', $request->user_id)
                        ->where('leave_type_id', $item['leave_type_id'])
                        ->where('financial_year', $request->financial_year)
                        ->update(['total_quota' => $item['annual_quota']]);
                }

                $savedSettings[] = $setting->load('leaveType');
            }
            DB::commit();
            return response()->json(apiSuccessResponse('Settings saved', ['settings' => $savedSettings]), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(apiErrorResponse($e->getMessage()), 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET LEAVE SETTINGS
    // ─────────────────────────────────────────────────────────────────────────
    public function getLeaveSettings(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $request->filled('user_id') ? (int)$request->user_id : $this->resp['user']['id'];
        $fy     = $request->filled('financial_year') ? $request->financial_year : $this->getFinancialYear();

        $data = LeaveType::where('is_active', true)->orderBy('sort_order')->get()
            ->map(function ($lt) use ($userId, $fy) {
                $setting = UserLeaveSetting::where('user_id', $userId)
                    ->where('leave_type_id', $lt->id)
                    ->where('financial_year', $fy)
                    ->first();
                return [
                    'leave_type_id'       => $lt->id,
                    'leave_type_name'     => $lt->name,
                    'leave_type_code'     => $lt->code,
                    'has_quota'           => $lt->has_quota,
                    'quota_editable'      => $lt->quota_editable,
                    'global_default'      => $lt->default_quota,
                    'annual_quota'        => optional($setting)->annual_quota,
                    'monthly_accrual'     => optional($setting)->monthly_accrual,
                    'carry_forward'       => optional($setting)->carry_forward,
                    'carry_forward_limit' => optional($setting)->carry_forward_limit,
                    'setting_exists'      => !is_null($setting),
                ];
            });

        return response()->json(
            apiSuccessResponse('Settings fetched', [
                'user_id'        => $userId,
                'financial_year' => $fy,
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

        return response()->json(apiSuccessResponse('Leave types fetched', ['leave_types' => $leaveTypes]), 200);
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
        $year     = (int)$request->query('year', now()->year);

        $holidays = HolidayList::where('is_active', true)
            ->where(function ($q) use ($userCity) {
                $q->where('is_national', true);
                if ($userCity) $q->orWhere('city', $userCity);
            })
            ->where(function ($q) use ($year) {
                $q->whereYear('date', $year)->orWhere('is_recurring', true);
            })
            ->orderByRaw("DATE_FORMAT(date,'%m-%d') asc")
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
    //
    // Requirement 5:
    // - PRESENT days → return ALL attendance records (multi-swipe)
    // - All other statuses → return at most ONE record (the primary/leave placeholder)
    //   This ensures the mobile app never renders duplicate cards for leave/LWP days.
    // ─────────────────────────────────────────────────────────────────────────
    public function calendar(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $this->resp['user']['id'];
        $month  = (int)$request->query('month', now()->month);
        $year   = (int)$request->query('year',  now()->year);

        if ($month < 1 || $month > 12 || $year < 2000) {
            return response()->json(apiErrorResponse('Invalid month or year'), 422);
        }

        $userCity = $this->getUserCity($userId);
        $this->expireStaleCompOffs($userId);

        // Batch-seed quotas silently on first calendar load
        $fy = $this->getFinancialYear(Carbon::create($year, $month, 1)->toDateString());
        $this->ensureDefaultQuotasForUser($userId, $fy);

        // Load all attendance records grouped by date
        $allAttendances = UserAttendance::where('user_id', $userId)
            ->whereMonth('in_date', $month)
            ->whereYear('in_date', $year)
            ->orderBy('id', 'asc')
            ->get();

        // Group by date string
        $attendancesByDate = [];
        foreach ($allAttendances as $a) {
            $ds = Carbon::parse($a->in_date)->toDateString();
            if (!isset($attendancesByDate[$ds])) {
                $attendancesByDate[$ds] = collect();
            }
            $attendancesByDate[$ds]->push($a);
        }

        $leaves = UserLeave::with('leaveType')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $leavesByDate = [];
        foreach ($leaves as $l) {
            $ds = Carbon::parse($l->date)->toDateString();
            if (!isset($leavesByDate[$ds])) {
                $leavesByDate[$ds] = collect();
            }
            $leavesByDate[$ds]->push($l);
        }

        $holidays = $this->getHolidaysForMonth($month, $year, $userCity);

        $compOffs = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'used')
            ->whereNotNull('used_on')
            ->whereMonth('used_on', $month)
            ->whereYear('used_on', $year)
            ->get();

        $compOffsByDate = [];
        foreach ($compOffs as $c) {
            $ds = Carbon::parse($c->used_on)->toDateString();
            $compOffsByDate[$ds] = $c;
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();
        $today     = Carbon::today();
        $calendar  = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $ds             = $date->toDateString();
            $isSunday       = ($date->dayOfWeek === Carbon::SUNDAY);
            $isHoliday      = isset($holidays[$ds]);
            $isCompOff      = isset($compOffsByDate[$ds]);
            $isFuture       = $date->gt($today);
            $dayAttendances = isset($attendancesByDate[$ds]) ? $attendancesByDate[$ds] : collect();
            $dayLeaves      = isset($leavesByDate[$ds])      ? $leavesByDate[$ds]      : collect();

            $status = $this->computeDayStatus(
                $ds, $isSunday, $isHoliday, $isCompOff, $dayAttendances, $dayLeaves, $isFuture,
                $date->isToday()    // ← fixes Bug 2
            );

            // ── Ensure all DB records for this date share the same status ──
            // This keeps the DB consistent with what the calendar computes.
            if ($status && $dayAttendances->isNotEmpty()) {
                $mismatch = $dayAttendances->first(function ($a) use ($status) {
                    return $a->status !== $status;
                });
                if ($mismatch) {
                    $ids = $dayAttendances->pluck('id')->toArray();
                    UserAttendance::whereIn('id', $ids)->update(['status' => $status]);
                    // Reflect in the local collection too
                    foreach ($dayAttendances as $a) {
                        $a->status = $status;
                    }
                }
            }

            // ── Requirement 5: Attendance array returned to app ────────────
            // PRESENT days → all records (multi-swipe support)
            // Everything else → single primary record only (no confusing duplicates)
            $isFullPresent = ($status === AttendanceStatus::PRESENT || $status === AttendanceStatus::FULL_DAY_PRESENT);

            if ($isFullPresent) {
                $attendancesForResponse = $dayAttendances->values();
            } else {
                // Return the single most relevant record (leave placeholder or first punch)
                $primary = $dayAttendances->first();
                $attendancesForResponse = $primary ? collect([$primary]) : collect();
            }

            $calendar[] = [
                'date'         => $ds,
                'day_name'     => $date->format('l'),
                'day_number'   => $date->day,
                'is_sunday'    => $isSunday,
                'is_today'     => $date->isToday(),
                'is_future'    => $isFuture,
                'is_holiday'   => $isHoliday,
                'holiday'      => $isHoliday ? $holidays[$ds] : null,
                'is_comp_off'  => $isCompOff,
                'comp_off'     => $isCompOff ? $compOffsByDate[$ds] : null,
                'status'       => $status,
                'mobile_color' => AttendanceStatus::mobileColor($status),
                'badge_css'    => AttendanceStatus::badgeCss($status),
                'attendances'  => $attendancesForResponse,
                'leaves'       => $dayLeaves->values(),
                // Extra flags for app UI decisions
                'can_mark_in'  => $this->canMarkIn($status, $dayAttendances, $isFuture),
                'can_mark_out' => $this->canMarkOut($status, $dayAttendances, $isFuture),
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
     * Helper for calendar: can the user mark IN on this day?
     * Returns true/false so the app can enable/disable the button.
     */
    protected function canMarkIn(?string $status, $dayAttendances, bool $isFuture): bool
    {
        if ($isFuture) return false;

        // Blocked statuses
        $blocked = [
            AttendanceStatus::LWP_UNINF,
            AttendanceStatus::LWP_UNAPP,
            AttendanceStatus::LWP_EXCESS,
            AttendanceStatus::HALF_LEAVE,
            AttendanceStatus::HALF_LWP,
        ];
        if (in_array($status, $blocked)) return false;

        // Has an open (unclosed) punch
        $hasOpen = $dayAttendances->contains(function ($a) {
            return !is_null($a->in_time) && is_null($a->out_time) && !$a->missed;
        });
        if ($hasOpen) return false;

        return true;
    }

    /**
     * Helper for calendar: can the user mark OUT on this day?
     */
    protected function canMarkOut(?string $status, $dayAttendances, bool $isFuture): bool
    {
        if ($isFuture) return false;
        $hasOpen = $dayAttendances->contains(function ($a) {
            return !is_null($a->in_time) && is_null($a->out_time) && !$a->missed;
        });
        return $hasOpen;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // USE COMP-OFF
    // ─────────────────────────────────────────────────────────────────────────
    public function useCompOff(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $validator = Validator::make($request->all(), [
            'comp_off_id' => 'required|integer|exists:user_weekly_off_compensations,id',
            'use_date'    => 'required|date',
        ]);
        if ($validator->fails()) return response()->json(validationResponse($validator), 422);

        $userId  = $this->resp['user']['id'];
        $useDate = Carbon::parse($request->use_date);

        if ($useDate->dayOfWeek === Carbon::SUNDAY) {
            return response()->json(apiErrorResponse('Comp-off cannot be used on Sunday'), 422);
        }

        $compOff = UserWeeklyOffCompensation::where('id', $request->comp_off_id)
            ->where('user_id', $userId)
            ->where('status', 'available')
            ->first();
        if (!$compOff) return response()->json(apiErrorResponse('Comp-off not found or unavailable'), 404);

        $validFrom = Carbon::parse($compOff->valid_from);
        $expiresOn = Carbon::parse($compOff->expires_on)->endOfDay();

        if ($useDate->lt($validFrom) || $useDate->gt($expiresOn)) {
            return response()->json(
                apiErrorResponse("Comp-off valid between {$compOff->valid_from} and {$compOff->expires_on}"),
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
            return response()->json(apiErrorResponse('Max 1 comp-off per week already used'), 422);
        }

        DB::beginTransaction();
        try {
            $compOff->update(['used_on' => $request->use_date, 'status' => 'used']);

            $att = UserAttendance::where('user_id', $userId)
                ->whereDate('in_date', $request->use_date)
                ->first();

            if (!$att) {
                UserAttendance::create([
                    'user_id' => $userId,
                    'in_date' => $request->use_date,
                    'in_time' => null,
                    'status'  => AttendanceStatus::COMP_OFF,
                ]);
            } else {
                $att->update(['status' => AttendanceStatus::COMP_OFF]);
            }

            DB::commit();
            return response()->json(
                apiSuccessResponse(
                    'Comp-off used for ' . $request->use_date,
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
    // AVAILABLE COMP-OFFS
    // ─────────────────────────────────────────────────────────────────────────
    public function availableCompOffs(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId = $this->resp['user']['id'];
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

    // ─────────────────────────────────────────────────────────────────────────
    // ATTENDANCE DETAIL
    // ─────────────────────────────────────────────────────────────────────────
    public function attendanceDetail($id)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        if (!is_numeric($id)) return response()->json(apiErrorResponse('Invalid ID'), 422);

        $attendance = UserAttendance::where('id', $id)
            ->where('user_id', $this->resp['user']['id'])
            ->first();

        if (!$attendance) return response()->json(apiErrorResponse('Attendance not found'), 404);

        return response()->json(
            apiSuccessResponse('Attendance detail fetched', ['attendance' => $attendance]),
            200
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MONTHLY SUMMARY
    // ─────────────────────────────────────────────────────────────────────────
    public function summary(Request $request)
    {
        if (!$this->resp['status'] || !isset($this->resp['user'])) {
            return response()->json(apiErrorResponse('Unauthorized'), 401);
        }

        $userId    = $this->resp['user']['id'];
        $month     = (int)$request->query('month', now()->month);
        $year      = (int)$request->query('year',  now()->year);
        $userCity  = $this->getUserCity($userId);
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();
        $today     = Carbon::today();

        $holidays = $this->getHolidaysForMonth($month, $year, $userCity);

        $totalWorkingDays = 0;
        for ($d = $startDate->copy(); $d->lte($endDate) && $d->lte($today); $d->addDay()) {
            if ($d->dayOfWeek !== Carbon::SUNDAY && !isset($holidays[$d->toDateString()])) {
                $totalWorkingDays++;
            }
        }

        $presentDays = UserAttendance::where('user_id', $userId)
            ->whereMonth('in_date', $month)
            ->whereYear('in_date', $year)
            ->whereIn('status', AttendanceStatus::PRESENT_STATUSES)
            ->distinct('in_date')
            ->count('in_date');

        $leaveDays = UserLeave::where('user_id', $userId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'approved')
            ->sum('quota_deducted');

        $compOffDays = UserWeeklyOffCompensation::where('user_id', $userId)
            ->where('status', 'used')
            ->whereMonth('used_on', $month)
            ->whereYear('used_on', $year)
            ->count();

        $lwpDays = max(0, $totalWorkingDays - $presentDays - $leaveDays - $compOffDays);

        return response()->json(
            apiSuccessResponse('Summary fetched', [
                'month'               => $month,
                'year'                => $year,
                'total_working_days'  => $totalWorkingDays,
                'present_days'        => $presentDays,
                'leave_days'          => (float)$leaveDays,
                'comp_off_days'       => $compOffDays,
                'lwp_days'            => $lwpDays,
                'absent_days'         => $lwpDays, // backward compat
                'holidays_this_month' => count($holidays),
            ]),
            200
        );
    }
}