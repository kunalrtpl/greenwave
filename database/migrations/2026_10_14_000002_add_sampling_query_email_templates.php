<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The two sides of the sample request query thread.
 *
 * Both render the same view; `audience` in the mail data tells it which way
 * round it is being read. Only the admin one has a fixed recipient — the
 * employee's copy is addressed at send time.
 */
class AddSamplingQueryEmailTemplates extends Migration
{
    public function up()
    {
        foreach ($this->templates() as $template) {
            DB::table('email_templates')->updateOrInsert(
                ['event_key' => $template['event_key']],
                $template + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down()
    {
        DB::table('email_templates')
            ->whereIn('event_key', array_column($this->templates(), 'event_key'))
            ->delete();
    }

    private function templates()
    {
        return [
            [
                'event_key'  => 'sample_query_from_employee_admin',
                'name'       => 'Sample Request Query Raised — to the Admin',
                'subject'    => 'Query on Sample Request | {request.reference_no}',
                'blade_view' => 'emails.sampling.query_posted',
                'to_emails'  => json_encode(['mkanum786@gmail.com']),
                'is_active'  => 1,
            ],
            [
                'event_key'  => 'sample_query_from_admin_employee',
                'name'       => 'Sample Request Query Answered — to the Employee',
                'subject'    => 'Reply on Your Sample Request | {request.reference_no}',
                'blade_view' => 'emails.sampling.query_posted',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
        ];
    }
}
