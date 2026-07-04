<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Customer Add/Edit revamp — Laravel 5.8
 *
 * customers:
 *   - freight_basis  : 'Paid by Company' | 'Paid by Customer'  (Direct Customer / Hybrid)
 *   - freight        : Rs./kg, used only when freight_basis = Paid by Company
 *     (payment_term column already exists and now stores the dropdown value:
 *      Advance / 1-7 days / 15 days / 30 days / 45 days / 60 days)
 *
 * customer_discounts (rows of discount_type = 'net_products' — the "Products" section):
 *   Existing columns are re-purposed as follows so old data keeps working:
 *     net_price       -> Customer Selling Price (Rs./kg)
 *     moq             -> MOQ (kg)
 *     packing_type    -> Packing Size (Standard / 5kg*2 / 1kg*10)
 *     for_qty         -> For MOQ (kg)         [Conditional Special block]
 *     applicable_type -> Special Basis        ('Special Price' | 'Special Discount')
 *     value           -> Special Price / Special Discount value
 *   New columns:
 *     selling_expense_basis / selling_expense_value          (main block)
 *     has_special                                            ('yes' | 'no')
 *     special_selling_expense_basis / special_selling_expense_value
 */
class AddPricingFieldsForCustomerRevamp extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('freight_basis', 50)->nullable()->after('payment_term');
            $table->decimal('freight', 8, 2)->nullable()->after('freight_basis');
        });

        Schema::table('customer_discounts', function (Blueprint $table) {
            $table->string('selling_expense_basis', 20)->nullable()->after('net_price');   // '%' | 'Rs/kg'
            $table->decimal('selling_expense_value', 8, 3)->nullable()->after('selling_expense_basis');
            $table->string('has_special', 5)->default('no')->after('packing_type');        // 'yes' | 'no'
            $table->string('special_selling_expense_basis', 20)->nullable()->after('has_special');
            $table->decimal('special_selling_expense_value', 8, 3)->nullable()->after('special_selling_expense_basis');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['freight_basis', 'freight']);
        });

        Schema::table('customer_discounts', function (Blueprint $table) {
            $table->dropColumn([
                'selling_expense_basis',
                'selling_expense_value',
                'has_special',
                'special_selling_expense_basis',
                'special_selling_expense_value',
            ]);
        });
    }
}
