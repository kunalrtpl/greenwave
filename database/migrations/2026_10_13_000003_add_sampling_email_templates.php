<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Email templates for the sampling module.
 *
 * `to_emails` is null on the three employee-facing ones because the recipient
 * is worked out at send time — the employee who raised the request. Only the
 * admin alert has a fixed address.
 *
 * The admin address is set to the testing inbox for now; change it from
 * Data Management → Email Templates when the module goes live.
 */
class AddSamplingEmailTemplates extends Migration
{
    const ADMIN_RECIPIENTS = ['mkanum786@gmail.com'];

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
                'event_key'  => 'sample_request_created_employee',
                'name'       => 'Sample Request Raised — to the Employee',
                'subject'    => 'Sample Request Received | {groupReference}',
                'blade_view' => 'emails.sampling.created_employee',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
            [
                'event_key'  => 'sample_request_created_admin',
                'name'       => 'Sample Request Raised — to the Admin',
                'subject'    => 'New Sample Request | {employee.name} | {groupReference}',
                'blade_view' => 'emails.sampling.created_admin',
                'to_emails'  => json_encode(self::ADMIN_RECIPIENTS),
                'is_active'  => 1,
            ],
            [
                'event_key'  => 'sample_request_decided_employee',
                'name'       => 'Sample Request Approved or Declined — to the Employee',
                'subject'    => 'Sample Request {request.email_status} | {request.reference_no}',
                'blade_view' => 'emails.sampling.approved_employee',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
            [
                'event_key'  => 'sample_request_dispatched_employee',
                'name'       => 'Sample Dispatched — to the Employee',
                'subject'    => 'Sample Dispatched | {dispatch.ref_no} | {dispatch.lr_no}',
                'blade_view' => 'emails.sampling.dispatched_employee',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
        ];
    }
}
