<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A voluntary sample is raised by the admin at dispatch time, so no employee
 * asked for it and nobody would otherwise be told. This template notifies the
 * admin, the same way po_admin does for a purchase order.
 *
 * The recipient is set to the testing inbox for now; change it from
 * Data Management → Email Templates when the module goes live.
 */
class AddVoluntarySampleAdminEmailTemplate extends Migration
{
    const EVENT_KEY = 'sample_voluntary_created_admin';

    public function up()
    {
        DB::table('email_templates')->updateOrInsert(
            ['event_key' => self::EVENT_KEY],
            [
                'name'       => 'Voluntary Sample Raised — to the Admin',
                'subject'    => 'Voluntary Sample Dispatched | {accountable} | {dispatch.ref_no}',
                'blade_view' => 'emails.sampling.voluntary_admin',
                'to_emails'  => json_encode(['mkanum786@gmail.com']),
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down()
    {
        DB::table('email_templates')->where('event_key', self::EVENT_KEY)->delete();
    }
}
