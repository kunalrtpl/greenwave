<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Tracking table for the Daily Work Report emails.
 * One row per (user, report_date). The console command claims 2 pending
 * rows per run, so a run can never double-send.
 */
class CreateDailyReportEmailLogsTable extends Migration
{
    public function up()
    {
        Schema::create('daily_report_email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('report_date');                       // the "yesterday" being reported
            $table->enum('status', ['pending', 'processing', 'sent', 'skipped', 'failed'])
                  ->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->string('pdf_file')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'report_date']);
            $table->index(['report_date', 'status']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_report_email_logs');
    }
}
