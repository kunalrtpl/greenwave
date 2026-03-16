<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToExpenseTables extends Migration
{
    public function up()
    {
        // ── user_expenses ──────────────────────────────────────────────
        Schema::table('user_expenses', function (Blueprint $table) {
            // Verification remark entered by admin when ticking the checkbox
            $table->text('internal_remarks')->nullable()->after('verified_by');

            // Remark entered by admin when Approving / Partially Approving / Rejecting
            $table->text('admin_remarks')->nullable()->after('internal_remarks');
        });

        // ── user_expenses_queries ──────────────────────────────────────
        Schema::table('user_expenses_queries', function (Blueprint $table) {
            // 1 = admin has read this message, 0 = unread by admin
            $table->tinyInteger('admin_read')->default(0)->after('message');

            // 1 = employee has read this message, 0 = unread by employee
            $table->tinyInteger('user_read')->default(0)->after('admin_read');
        });
    }

    public function down()
    {
        Schema::table('user_expenses', function (Blueprint $table) {
            $table->dropColumn(['internal_remarks', 'admin_remarks']);
        });

        Schema::table('user_expenses_queries', function (Blueprint $table) {
            $table->dropColumn(['admin_read', 'user_read']);
        });
    }
}