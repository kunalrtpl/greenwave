<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Email templates for the "product discontinued" notice sent from
 * Admin → Product Pricing.
 *
 * `to_emails` is null on both because the recipient is worked out at send
 * time: each employee or dealer linked to the product gets their own email.
 * Subject and active flag can be changed from Data Management → Email
 * Templates.
 */
class AddProductDiscontinuedEmailTemplates extends Migration
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
                'event_key'  => 'product_discontinued_employee',
                'name'       => 'Product Discontinued — to Linked Employees',
                'subject'    => 'Product Discontinued | {product.product_name}',
                'blade_view' => 'emails.product_discontinued.employee',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
            [
                'event_key'  => 'product_discontinued_dealer',
                'name'       => 'Product Discontinued — to Linked Dealers',
                'subject'    => 'Product Discontinuation Notice | {product.product_name}',
                'blade_view' => 'emails.product_discontinued.dealer',
                'to_emails'  => null,
                'is_active'  => 1,
            ],
        ];
    }
}
