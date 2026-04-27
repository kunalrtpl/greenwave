<?php

namespace App;

/**
 * AttendanceStatus
 *
 * SINGLE SOURCE OF TRUTH for all attendance status strings, groupings,
 * colors, CSS classes, and stat-weight helpers across the entire codebase.
 *
 * Both AdminAttendanceController and ApiAttendanceController MUST import
 * and use only this class — never hard-code status strings elsewhere.
 *
 * ┌──────────────────────────────────────┬─────────────────────────────────────────┐
 * │ Constant                               │ When used                               │
 * ├──────────────────────────────────────┼─────────────────────────────────────────┤
 * │ PRESENT                                │ Punched IN (or IN+OUT), no leave        │
 * │ HALF_LEAVE      (1/2 P + 1/2 Leave) │ Punched + half-day leave today/past     │
 * │ HALF_LWP        (1/2 P + 1/2 LWP)   │ Punched + no leave on that half         │
 * │ HALF_DAY_LEAVE                        │ Future half-day leave (not yet present) │
 * │ HALF_LEAVE_LWP  (1/2 L + 1/2 LWP)   │ Half-leave applied, NOT punched in      │
 * │ FULL_LEAVE                            │ Full-day leave approved                 │
 * │ LWP_UNINF                             │ No punch, past, no record               │
 * │ LWP_UNAPP                             │ Admin-set unapproved leave              │
 * │ LWP_EXCESS                            │ Leave but quota exhausted               │
 * │ HOLIDAY                               │ National / city holiday                 │
 * │ COMP_OFF                              │ Compensatory weekly off used            │
 * │ WEEKLY_OFF                            │ Sunday, no attendance                   │
 * └──────────────────────────────────────┴─────────────────────────────────────────┘
 */
class AttendanceStatus
{
    // ── Canonical DB values ───────────────────────────────────────────────────
    const PRESENT         = 'Present';
    const HALF_LEAVE      = '1/2 Present + 1/2 Leave';
    const HALF_LWP        = '1/2 Present + 1/2 LWP';
    const HALF_DAY_LEAVE  = '1/2 Day Leave';
    const HALF_LEAVE_LWP  = '1/2 Leave + 1/2 LWP';
    const FULL_LEAVE      = 'Allowed Full Day Leave';
    const LWP_UNINF       = 'LWP (Uninformed Absence)';
    const LWP_UNAPP       = 'LWP (Unapproved Leave)';
    const LWP_EXCESS      = 'LWP (Exceeds Quota)';
    const HOLIDAY         = 'Holiday';
    const COMP_OFF        = 'Compensatory Weekly Off';
    const WEEKLY_OFF      = 'Weekly Off';

    /** Legacy value kept for backward-compat with existing DB rows (pre-rename). */
    const FULL_DAY_PRESENT = 'Full Day Present';

    // ── Grouped sets ─────────────────────────────────────────────────────────

    /** Statuses that consume leave quota */
    const LEAVE_STATUSES = [
        self::FULL_LEAVE,
        self::HALF_LEAVE,
        self::HALF_DAY_LEAVE,
        self::HALF_LEAVE_LWP,
    ];

    /** Statuses that are LWP (Loss of Pay) */
    const LWP_STATUSES = [
        self::LWP_UNINF,
        self::LWP_UNAPP,
        self::LWP_EXCESS,
        self::HALF_LWP,
        self::HALF_LEAVE_LWP,
    ];

    /** Statuses that mean the employee physically attended */
    const PRESENT_STATUSES = [
        self::PRESENT,
        self::HALF_LEAVE,
        self::HALF_LWP,
        self::FULL_DAY_PRESENT, // legacy
    ];

    /** Statuses that require admin to pick a leave type (quota picker shown) */
    const QUOTA_DEDUCT_STATUSES = [
        self::FULL_LEAVE,
        self::HALF_LEAVE,
        self::HALF_DAY_LEAVE,
        self::HALF_LEAVE_LWP,
    ];

    /**
     * Half-day leave statuses — presence component depends on whether
     * the employee punched in that day.
     */
    const HALF_DAY_LEAVE_STATUSES = [
        self::HALF_LEAVE,
        self::HALF_DAY_LEAVE,
        self::HALF_LEAVE_LWP,
    ];

    /** Statuses visible in the admin "Select New Status" modal */
    const ADMIN_OPTIONS = [
        self::PRESENT,
        self::HALF_LWP,
        self::HALF_LEAVE,
        self::HALF_DAY_LEAVE,
        self::FULL_LEAVE,
        self::LWP_UNINF,
        self::LWP_UNAPP,
        self::LWP_EXCESS,
        self::HOLIDAY,
        self::COMP_OFF,
        self::WEEKLY_OFF,
    ];

    // ── Mobile app color palette ──────────────────────────────────────────────

    const MOBILE_COLORS = [
        self::PRESENT          => ['primary' => '#10B981', 'background' => '#E8F8F1'],
        self::FULL_DAY_PRESENT => ['primary' => '#10B981', 'background' => '#E8F8F1'],
        self::HALF_LEAVE       => ['primary' => '#F59E0B', 'background' => '#FFF4DD'],
        self::HALF_LWP         => ['primary' => '#EF4444', 'background' => '#FDECEC'],
        self::HALF_DAY_LEAVE   => ['primary' => '#F59E0B', 'background' => '#FFF4DD'],
        self::HALF_LEAVE_LWP   => ['primary' => '#EF4444', 'background' => '#FDECEC'],
        self::FULL_LEAVE       => ['primary' => '#F59E0B', 'background' => '#FFF4DD'],
        self::LWP_UNINF        => ['primary' => '#EF4444', 'background' => '#FDECEC'],
        self::LWP_UNAPP        => ['primary' => '#EF4444', 'background' => '#FDECEC'],
        self::LWP_EXCESS       => ['primary' => '#EF4444', 'background' => '#FDECEC'],
        self::HOLIDAY          => ['primary' => '#8B5CF6', 'background' => '#F2ECFF'],
        self::COMP_OFF         => ['primary' => '#8B5CF6', 'background' => '#F2ECFF'],
        self::WEEKLY_OFF       => ['primary' => '#8B5CF6', 'background' => '#F2ECFF'],
    ];

    // ── Admin UI CSS helper maps ──────────────────────────────────────────────

    const BADGE_CSS = [
        self::PRESENT          => 'sb-present',
        self::FULL_DAY_PRESENT => 'sb-present',
        self::HALF_LEAVE       => 'sb-half',
        self::HALF_LWP         => 'sb-lwp',
        self::HALF_DAY_LEAVE   => 'sb-half',
        self::HALF_LEAVE_LWP   => 'sb-lwp',
        self::FULL_LEAVE       => 'sb-leave',
        self::LWP_UNINF        => 'sb-lwp',
        self::LWP_UNAPP        => 'sb-lwp',
        self::LWP_EXCESS       => 'sb-lwp',
        self::HOLIDAY          => 'sb-holiday',
        self::COMP_OFF         => 'sb-compoff',
        self::WEEKLY_OFF       => 'sb-weekly',
    ];

    const BAR_CSS = [
        self::PRESENT          => 'present',
        self::FULL_DAY_PRESENT => 'present',
        self::HALF_LEAVE       => 'half',
        self::HALF_LWP         => 'halfLwp',
        self::HALF_DAY_LEAVE   => 'half',
        self::HALF_LEAVE_LWP   => 'halfLwp',
        self::FULL_LEAVE       => 'leave',
        self::LWP_UNINF        => 'lwp',
        self::LWP_UNAPP        => 'lwp',
        self::LWP_EXCESS       => 'lwp',
        self::HOLIDAY          => 'holiday',
        self::COMP_OFF         => 'compoff',
        self::WEEKLY_OFF       => 'weekly',
    ];

    // ── Stat weight helpers ───────────────────────────────────────────────────

    /** How many leave-days this status counts as (for reports/stats) */
    public static function leaveWeight(string $status): float
    {
        switch ($status) {
            case self::FULL_LEAVE:
                return 1.0;
            case self::HALF_LEAVE:
            case self::HALF_DAY_LEAVE:
            case self::HALF_LEAVE_LWP:
                return 0.5;
            default:
                return 0.0;
        }
    }

    /** How many present-days this status counts as */
    public static function presentWeight(string $status): float
    {
        switch ($status) {
            case self::PRESENT:
            case self::FULL_DAY_PRESENT:
                return 1.0;
            case self::HALF_LEAVE:
            case self::HALF_LWP:
                return 0.5;
            default:
                return 0.0;
        }
    }

    /** How many LWP-days this status counts as */
    public static function lwpWeight(string $status): float
    {
        switch ($status) {
            case self::LWP_UNINF:
            case self::LWP_UNAPP:
            case self::LWP_EXCESS:
                return 1.0;
            case self::HALF_LWP:
            case self::HALF_LEAVE_LWP:
                return 0.5;
            default:
                return 0.0;
        }
    }

    // ── Boolean helpers ───────────────────────────────────────────────────────

    public static function isPresent(string $status): bool
    {
        return in_array($status, self::PRESENT_STATUSES, true);
    }

    public static function isLeave(string $status): bool
    {
        return in_array($status, self::LEAVE_STATUSES, true);
    }

    public static function isLwp(string $status): bool
    {
        return in_array($status, self::LWP_STATUSES, true);
    }

    public static function isHalfDay(string $status): bool
    {
        return in_array($status, self::HALF_DAY_LEAVE_STATUSES, true);
    }

    public static function requiresQuotaPicker(string $status): bool
    {
        return in_array($status, self::QUOTA_DEDUCT_STATUSES, true);
    }

    public static function badgeCss(?string $status): string
    {
        return self::BADGE_CSS[$status ?? ''] ?? 'sb-future';
    }

    public static function barCss(?string $status): string
    {
        return self::BAR_CSS[$status ?? ''] ?? 'future';
    }

    public static function mobileColor(?string $status): array
    {
        return self::MOBILE_COLORS[$status ?? ''] ?? ['primary' => '#d1d5db', 'background' => '#f4f6f9'];
    }

    /**
     * Compute the correct status for a half-day leave based on context.
     *
     * - Future date           → HALF_DAY_LEAVE  (leave planned, not happened yet)
     * - Today, no punch yet   → HALF_DAY_LEAVE  (day not over, don't penalise yet)
     * - Today/Past, punched   → HALF_LEAVE      (Present + Leave)
     * - Past, no punch        → HALF_LEAVE_LWP  (Leave + LWP, day is over)
     */
    public static function resolveHalfDayLeaveStatus(
        bool $isFuture,
        bool $hasPunch,
        bool $isToday = false
    ): string {
        if ($isFuture || ($isToday && !$hasPunch)) return self::HALF_DAY_LEAVE;
        return $hasPunch ? self::HALF_LEAVE : self::HALF_LEAVE_LWP;
    }

    /**
     * Resync a stored half-day status against actual punch state.
     * Returns the corrected status string, or null if no change needed.
     */
    public static function resyncHalfDayStatus(
        string $currentStatus,
        bool $hasPunch,
        bool $isFuture,
        bool $isToday = false
    ): ?string {
        if (!self::isHalfDay($currentStatus)) return null;
        $correct = self::resolveHalfDayLeaveStatus($isFuture, $hasPunch, $isToday);
        return $correct !== $currentStatus ? $correct : null;
    }
}