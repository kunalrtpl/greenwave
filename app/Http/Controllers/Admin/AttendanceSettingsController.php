<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\LeaveType;
use App\UserLeaveSetting;
use App\UserLeaveQuota;
use Validator;
use DB;
use Carbon\Carbon;

/**
 * Admin: Manage Attendance Leave Settings per User
 *
 * Add to your admin routes:
 *   Route::get('users/{id}/attendance-settings',       'Admin\AttendanceSettingsController@index')
 *        ->name('admin.users.attendance.settings');
 *   Route::post('users/{id}/attendance-settings/save', 'Admin\AttendanceSettingsController@save')
 *        ->name('admin.users.attendance.settings.save');
 */
class AttendanceSettingsController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/users/{id}/attendance-settings
    // ─────────────────────────────────────────────────────────────────────────
    public function index($userId)
    {
        $user = User::findOrFail($userId);

        // Show current FY + 2 previous FYs so admin can edit past FY quotas too
        $availableFys = $this->getAvailableFys(3);
        $currentFy    = $availableFys[0];

        // All active leave types
        $leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Build data grid: [fy => [leaveTypeId => [setting, quota, resolved values]]]
        $data = [];
        foreach ($availableFys as $fy) {
            $data[$fy] = [];
            foreach ($leaveTypes as $lt) {

                $setting = UserLeaveSetting::where('user_id', $userId)
                    ->where('leave_type_id', $lt->id)
                    ->where('financial_year', $fy)
                    ->first();

                $quota = UserLeaveQuota::where('user_id', $userId)
                    ->where('leave_type_id', $lt->id)
                    ->where('financial_year', $fy)
                    ->first();

                $data[$fy][$lt->id] = [
                    'leave_type'          => $lt,
                    // Editable values (from user_leave_settings, or defaults)
                    'annual_quota'        => $setting && !is_null($setting->annual_quota)
                                                ? (float) $setting->annual_quota
                                                : (float) $lt->default_quota,
                    'monthly_accrual'     => $setting ? (float) $setting->monthly_accrual  : 1.0,
                    'carry_forward'       => $setting ? (bool)  $setting->carry_forward     : false,
                    'carry_forward_limit' => $setting ? (float) $setting->carry_forward_limit : 10.0,
                    // Read-only live quota stats
                    'total_quota'         => $quota ? (float) $quota->total_quota : 0,
                    'used_quota'          => $quota ? (float) $quota->used_quota  : 0,
                    'remaining'           => $quota ? max(0, $quota->total_quota - $quota->used_quota) : 0,
                    'setting_exists'      => !is_null($setting),
                    'quota_exists'        => !is_null($quota),
                ];
            }
        }

        return view('admin.users.attendance_settings', compact(
            'user', 'leaveTypes', 'availableFys', 'currentFy', 'data'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/users/{id}/attendance-settings/save
    // ─────────────────────────────────────────────────────────────────────────
    public function save(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'financial_year'                           => 'required|string',
            'settings'                                 => 'required|array',
            'settings.*.leave_type_id'                 => 'required|integer|exists:leave_types,id',
            'settings.*.annual_quota'                  => 'nullable|numeric|min:0|max:365',
            'settings.*.monthly_accrual'               => 'nullable|numeric|min:0|max:31',
            'settings.*.carry_forward'                 => 'nullable|boolean',
            'settings.*.carry_forward_limit'           => 'nullable|numeric|min:0|max:365',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check the form.');
        }

        $fy = $request->financial_year;

        DB::beginTransaction();
        try {
            foreach ($request->settings as $item) {
                $leaveTypeId = $item['leave_type_id'];
                $leaveType   = LeaveType::find($leaveTypeId);
                $isEL        = $leaveType && $leaveType->code === 'EL';
                $hasQuota    = $leaveType && $leaveType->has_quota;

                // ── Save / update user_leave_settings ────────────────────────
                $setting = UserLeaveSetting::updateOrCreate(
                    [
                        'user_id'        => $userId,
                        'leave_type_id'  => $leaveTypeId,
                        'financial_year' => $fy,
                    ],
                    [
                        // EL: annual_quota is read-only (cron managed) — strip it
                        'annual_quota'        => $isEL
                            ? null
                            : (isset($item['annual_quota']) ? (float) $item['annual_quota'] : null),
                        'monthly_accrual'     => $isEL
                            ? (float) ($item['monthly_accrual'] ?? 1.0)
                            : null,
                        'carry_forward'       => $isEL
                            ? (bool) ($item['carry_forward'] ?? false)
                            : false,
                        'carry_forward_limit' => $isEL
                            ? (float) ($item['carry_forward_limit'] ?? 10)
                            : 0,
                    ]
                );

                // ── Also update live quota total for SL/CL (if quota row exists) ──
                // EL quota total is managed by cron — we do NOT touch it here
                if (!$isEL && $hasQuota && isset($item['annual_quota'])) {
                    $quota = UserLeaveQuota::where('user_id', $userId)
                        ->where('leave_type_id', $leaveTypeId)
                        ->where('financial_year', $fy)
                        ->first();

                    if ($quota) {
                        // Only update total_quota — never touch used_quota
                        $quota->update(['total_quota' => (float) $item['annual_quota']]);
                    } else {
                        // Create fresh quota row from the new setting
                        UserLeaveQuota::create([
                            'user_id'        => $userId,
                            'leave_type_id'  => $leaveTypeId,
                            'financial_year' => $fy,
                            'total_quota'    => (float) $item['annual_quota'],
                            'used_quota'     => 0,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.users.attendance.settings', $userId)
                ->with('success', 'Attendance settings saved successfully for ' . $user->name . ' (' . $fy . ')');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    protected function getFinancialYear($date = null): string
    {
        $d = $date ? Carbon::parse($date) : Carbon::now();
        return $d->month >= 4
            ? $d->year . '-' . substr($d->year + 1, -2)
            : ($d->year - 1) . '-' . substr($d->year, -2);
    }

    /**
     * Returns array of FY strings: current first, then going back.
     * e.g. ['2026-27', '2025-26', '2024-25']
     */
    protected function getAvailableFys(int $count = 3): array
    {
        $fys  = [];
        $now  = Carbon::now();
        $year = $now->month >= 4 ? $now->year : $now->year - 1;

        for ($i = 0; $i < $count; $i++) {
            $fys[] = ($year - $i) . '-' . substr(($year - $i + 1), -2);
        }
        return $fys;
    }
}