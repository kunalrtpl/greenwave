<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * user_leave_settings
 * ─────────────────────────────────────────────────────────────────
 * Stores PER-USER leave configuration per leave_type per FY.
 *
 * Why per-FY?
 *   An employee's EL accrual rate or SL quota may change year to year.
 *   Storing FY-wise keeps history clean and auditable.
 *
 * Key columns:
 *   annual_quota        → For SL/CL: how many days this user gets this FY
 *                         (overrides leave_types.default_quota)
 *                         For EL: NOT used directly; EL is accrual-based
 *
 *   monthly_accrual     → EL only: days added per month (e.g. 1, 1.25, 1.5)
 *
 *   carry_forward       → EL only: does unused EL roll into next FY?
 *
 *   carry_forward_limit → EL only: max EL balance allowed at any time
 *                         (e.g. 10 means even if accrued 15, cap at 10)
 */
class CreateUserLeaveSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('user_leave_settings', function (Blueprint $table) {
            $table->increments('id');
            // Match users.id type — bigIncrements() → unsignedBigInteger
            $table->unsignedBigInteger('user_id');
            // leave_types uses increments() → unsignedInteger is correct
            $table->unsignedInteger('leave_type_id');
            $table->string('financial_year', 10);         // e.g. "2026-27"

            // ── SL / CL ──────────────────────────────────────────────────────
            // Annual quota override for this user in this FY.
            // NULL = fall back to leave_types.default_quota
            $table->decimal('annual_quota', 5, 2)->nullable();

            // ── EL SPECIFIC ───────────────────────────────────────────────────
            // How many days are added to EL quota each month.
            // Common values: 1.0, 1.25, 1.5, 0.75
            // NULL for non-EL types.
            $table->decimal('monthly_accrual', 4, 2)->nullable();

            // Does unused EL carry forward to the next FY?
            $table->boolean('carry_forward')->default(false);

            // Maximum EL balance allowed at any time.
            // 0 = no limit (not recommended), e.g. 10 = cap at 10 days.
            $table->decimal('carry_forward_limit', 5, 2)->default(0);

            $table->timestamps();

            // One setting row per user + leave type + FY
            $table->unique(
                ['user_id', 'leave_type_id', 'financial_year'],
                'uq_user_leavetype_fy'
            );

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_leave_settings');
    }
}