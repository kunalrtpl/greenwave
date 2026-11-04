<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The two emails a stock transfer sends, both between employees.
 *
 * Neither has a fixed recipient: the address is worked out at send time from
 * the two employees on the transfer, so `to_emails` stays null. While
 * MAIL_REDIRECT_TO is set, EmailService sends both to that inbox instead.
 */
class AddStockTransferEmailTemplates extends Migration
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
                'event_key'  => 'stock_transfer_requested_employee',
                'name'       => 'Stock Transfer Raised — to the Receiving Employee',
                'subject'    => 'Stock Transfer Request | {transfer.reference_no}',
                'blade_view' => 'emails.stock_transfer.requested',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
            [
                'event_key'  => 'stock_transfer_decided_employee',
                'name'       => 'Stock Transfer Accepted or Refused — to the Sending Employee',
                'subject'    => 'Stock Transfer {transfer.reference_no} | {transfer.status_label}',
                'blade_view' => 'emails.stock_transfer.decided',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
        ];
    }
}
