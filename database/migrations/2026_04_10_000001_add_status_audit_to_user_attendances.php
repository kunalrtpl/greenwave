<?php
// FILE 1: database/migrations/2026_04_10_000001_add_status_audit_to_user_attendances.php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusAuditToUserAttendances extends Migration
{
    public function up()
    {
        Schema::table('user_attendances', function (Blueprint $table) {
            // Previous status before admin change
            $table->string('previous_status')->nullable()->after('status');
            // Admin who last changed the status (null = user self-punch)
            $table->unsignedBigInteger('status_changed_by')->nullable()->after('previous_status');
            // Human-readable audit note
            $table->string('status_change_note')->nullable()->after('status_changed_by');
            // Timestamp of last status change by admin
            $table->timestamp('status_changed_at')->nullable()->after('status_change_note');

            $table->foreign('status_changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('user_attendances', function (Blueprint $table) {
            $table->dropForeign(['status_changed_by']);
            $table->dropColumn(['previous_status','status_changed_by','status_change_note','status_changed_at']);
        });
    }
}