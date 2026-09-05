<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class RenameMonthlyReportEmailLogsTable extends Migration
{
    /**
     * The report was renamed "Monthly Customer Visit Analysis" — renaming the
     * tracking table to match, so the DB is a single source of truth alongside
     * the renamed model/command/views.
     */
    public function up()
    {
        Schema::rename('monthly_report_email_logs', 'monthly_customer_visit_analysis_logs');
    }

    public function down()
    {
        Schema::rename('monthly_customer_visit_analysis_logs', 'monthly_report_email_logs');
    }
}
