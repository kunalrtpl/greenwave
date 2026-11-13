<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The two sides of the DVR query thread.
 *
 * Both render the same view; `audience` in the mail data tells it which way
 * round it is being read. Neither has fixed recipients: the employee's copy
 * goes to the DVR's owner, and the admins' copy goes to every admin who has
 * written on that DVR's thread, so both are addressed at send time.
 */
class AddDvrQueryEmailTemplates extends Migration
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
                'event_key'  => 'dvr_query_from_admin_employee',
                'name'       => 'CVR Query Raised — to the Employee',
                'subject'    => 'Query Raised on Your CVR | {dvrRef}',
                'blade_view' => 'emails.dvr.query_posted',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
            [
                'event_key'  => 'dvr_query_from_employee_admin',
                'name'       => 'CVR Query Responded — to the Admins on the Thread',
                'subject'    => 'Query Responded on CVR | {dvrRef}',
                'blade_view' => 'emails.dvr.query_posted',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
        ];
    }
}
