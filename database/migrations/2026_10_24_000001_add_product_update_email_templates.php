<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Email templates for every product change announced from Admin → Product
 * Pricing: available again, not available, back in stock and price revised,
 * one each for linked employees and linked dealers.
 *
 * The two "discontinued" templates from 2026_10_21_000001 are moved onto the
 * shared view folder, emails/product_updates, which handles both audiences.
 *
 * `to_emails` is null throughout: each linked person gets their own email,
 * addressed at send time. Subjects and the active flag can be changed from
 * Data Management → Email Templates.
 */
class AddProductUpdateEmailTemplates extends Migration
{
    public function up()
    {
        foreach ($this->templates() as $template) {
            DB::table('email_templates')->updateOrInsert(
                ['event_key' => $template['event_key']],
                $template + ['created_at' => now(), 'updated_at' => now()]
            );
        }

        DB::table('email_templates')
            ->whereIn('event_key', ['product_discontinued_employee', 'product_discontinued_dealer'])
            ->update(['blade_view' => 'emails.product_updates.discontinued', 'updated_at' => now()]);
    }

    public function down()
    {
        DB::table('email_templates')
            ->whereIn('event_key', array_column($this->templates(), 'event_key'))
            ->delete();

        DB::table('email_templates')->where('event_key', 'product_discontinued_employee')
            ->update(['blade_view' => 'emails.product_discontinued.employee']);
        DB::table('email_templates')->where('event_key', 'product_discontinued_dealer')
            ->update(['blade_view' => 'emails.product_discontinued.dealer']);
    }

    private function templates()
    {
        $rows = [];
        $events = [
            'reinstated'    => ['Product Available Again',            'Product Available Again | {product.product_name}'],
            'not_available' => ['Product Temporarily Not Available',  'Product Temporarily Not Available | {product.product_name}'],
            'available'     => ['Product Back in Stock',              'Product Back in Stock | {product.product_name}'],
            'price_changed' => ['Dealer Price Revised',               'Dealer Price {priceDirectionLabel} | {product.product_name}'],
        ];

        foreach ($events as $event => list($name, $subject)) {
            foreach (['employee' => 'Linked Employees', 'dealer' => 'Linked Dealers'] as $audience => $label) {
                $rows[] = [
                    'event_key'  => "product_{$event}_{$audience}",
                    'name'       => "{$name} — to {$label}",
                    'subject'    => $subject,
                    'blade_view' => "emails.product_updates.{$event}",
                    'to_emails'  => null,
                    'is_active'  => 1,
                ];
            }
        }

        return $rows;
    }
}
