<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeeklyReportEmailLogsTable extends Migration
{
    /**
     * Mirrors daily_report_email_logs but for the weekly report.
     * report_date = the MONDAY (start) of the reported week.
     * Separate table so the daily command's 5-day purge never touches it.
     */
    public function up()
    {
        Schema::create('weekly_report_email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('report_date');                       // week-start (Monday)
            $table->string('status', 20)->default('pending');  // pending|processing|sent|failed|skipped
            $table->unsignedInteger('attempts')->default(0);
            $table->string('pdf_file')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            // one row per user per week — makes seeding idempotent (INSERT IGNORE)
            $table->unique(['user_id', 'report_date'], 'weekly_report_user_week_unique');
            $table->index('report_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_report_email_logs');
    }
}
