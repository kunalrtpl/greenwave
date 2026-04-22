<?php
// database/seeds/EmailTemplateSeeder.php

use App\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'event_key'  => 'po_admin',
                'name'       => 'Admin — New PO Created',
                'subject'    => 'PO Approval Required | #{po.po_ref_no_string} | {dealer.business_name}-{dealer.city}',
                'blade_view' => 'emails.purchase_order.admin',
                'to_emails'  => [
                    'singhania.kamal@gmail.com',
                    'tejaswini@greenwaveglobal.com',
                    'mkanum786@gmail.com',
                ],
                'cc_emails'  => null,
                'bcc_emails' => null,
                'is_active'  => true,
            ],
            [
                'event_key'  => 'po_dealer_self',
                'name'       => 'Dealer — Self PO Created',
                'subject'    => 'PO Received | #{po.po_ref_no_string} | {dealer.business_name}-{dealer.city}',
                'blade_view' => 'emails.purchase_order.dealer_self',
                'to_emails'  => null,
                'cc_emails'  => null,
                'bcc_emails' => null,
                'is_active'  => true,
            ],
            [
                'event_key' => 'po_dealer_approved',
                'name'       => 'Dealer — PO Approved by Admin',
                'subject'    => 'PO Approved | #{po.po_ref_no_string} | {dealer.business_name}-{dealer.city}',
                'blade_view' => 'emails.purchase_order.dealer_po_approved',
                'to_emails'  => null,    // dealer email passed dynamically
                'cc_emails'  => null,
                'bcc_emails' => null,
                'is_active'  => true,
            ]
        ];

        foreach ($templates as $t) {
            EmailTemplate::updateOrCreate(
                ['event_key' => $t['event_key']],
                $t
            );
        }

        $this->command->info('Email templates seeded successfully.');
    }
}