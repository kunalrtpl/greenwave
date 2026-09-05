<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonthlyReportEmailLogsTable extends Migration
{
    /**
     * Mirrors weekly_report_email_logs but for the monthly report.
     * report_date = the FIRST DAY of the reported month.
     */
    public function up()
    {
        Schema::create('monthly_report_email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('report_date');                       // month-start (1st)
            $table->string('status', 20)->default('pending');  // pending|processing|sent|failed|skipped
            $table->unsignedInteger('attempts')->default(0);
            $table->string('pdf_file')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            // one row per user per month — makes seeding idempotent (INSERT IGNORE)
            $table->unique(['user_id', 'report_date'], 'monthly_report_user_month_unique');
            $table->index('report_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_report_email_logs');
    }
}
