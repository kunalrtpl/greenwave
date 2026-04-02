<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * user_el_accrual_logs
 * ─────────────────────────────────────────────────────────────────
 * Audit log of every Earned Leave accrual event.
 *
 * Populated by:
 *   1. Monthly cron job (EarnedLeaveAccrualCommand)
 *   2. FY rollover logic (carry-forward balance transfer)
 *
 * source values:
 *   'monthly_cron'    → Regular monthly accrual by cron
 *   'fy_carry_forward'→ Balance carried over from previous FY
 *   'fy_reset'        → Balance zeroed out at FY start (no carry)
 *   'manual_admin'    → Admin manually adjusted EL
 */
class CreateUserElAccrualLogsTable extends Migration
{
    public function up()
    {
        Schema::create('user_el_accrual_logs', function (Blueprint $table) {
            $table->increments('id');

            // ⚠️  Match EXACTLY what your users table uses for its primary key.
            //  increments()    → users.id is unsigned int  → use unsignedInteger()
            //  bigIncrements() → users.id is unsigned bigint → use unsignedBigInteger()
            //  Laravel 5.8 default auth scaffold uses bigIncrements → unsignedBigInteger here.
            $table->unsignedBigInteger('user_id');

            // leave_types uses increments() → unsigned int → unsignedInteger is correct
            $table->unsignedInteger('leave_type_id');

            $table->string('financial_year', 10);

            // ✅ Use unsignedTinyInteger / unsignedSmallInteger — NOT tinyInteger()->unsigned()
            // Chained ->unsigned() behaves inconsistently in MySQL 5.x / Laravel 5.8
            $table->unsignedTinyInteger('accrual_month');   // 1–12
            $table->unsignedSmallInteger('accrual_year');   // e.g. 2026

            $table->decimal('days_added', 5, 2);
            $table->decimal('balance_before', 5, 2);
            $table->decimal('balance_after', 5, 2);
            $table->decimal('cap_applied', 5, 2)->default(0);

            $table->enum('source', [
                'monthly_cron',
                'fy_carry_forward',
                'fy_reset',
                'manual_admin',
            ])->default('monthly_cron');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes first, then FKs (avoids InnoDB ordering issues)
            $table->index(['user_id', 'financial_year']);
            $table->index(['accrual_year', 'accrual_month']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_el_accrual_logs');
    }
}