<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class RenameMonthlyCustomerVisitAnalysisToMonthlyVisitAnalysis extends Migration
{
    /**
     * The report was renamed "Monthly Visit Analysis" (dropping "Customer") —
     * renaming the tracking table and updating the stored email-template row
     * to match, so the DB stays a single source of truth alongside the
     * renamed model/command/controller/views.
     */
    public function up()
    {
        Schema::rename('monthly_customer_visit_analysis_logs', 'monthly_visit_analysis_logs');

        DB::table('email_templates')
            ->where('event_key', 'monthly_customer_visit_analysis')
            ->update([
                'event_key'  => 'monthly_visit_analysis',
                'name'       => 'Monthly Visit Analysis to Employee',
                'subject'    => 'Monthly Visit Analysis | {employee_name} | {monthLabel}',
                'blade_view' => 'emails.monthly_visit_analysis.employee',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        Schema::rename('monthly_visit_analysis_logs', 'monthly_customer_visit_analysis_logs');

        DB::table('email_templates')
            ->where('event_key', 'monthly_visit_analysis')
            ->update([
                'event_key'  => 'monthly_customer_visit_analysis',
                'name'       => 'Monthly Customer Visit Analysis to Employee',
                'subject'    => 'Monthly Customer Visit Analysis | {employee_name} | {monthLabel}',
                'blade_view' => 'emails.monthly_customer_visit_analysis.employee',
                'updated_at' => now(),
            ]);
    }
}
