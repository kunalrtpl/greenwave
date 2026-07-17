<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Executive Price Requests — saved by field executives via API (status
 * defaults to Pending), reviewed by admin who can Approve (row is pushed
 * into customer_discounts as a net_products entry) or Reject with a reason.
 */
class CreateCustomerPriceRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_price_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');              // executive who raised it
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('product_id');

            // commercial context
            $table->string('payment_term', 50);                 // Advance / 1-7 days / ... / 60 days
            $table->string('freight_basis', 50)->default('Paid by Company');
            $table->decimal('freight', 8, 2)->default(0);       // Rs./kg

            // pricing snapshot
            $table->string('packing_size', 50)->default('Standard');   // Standard / 5kg*2 / 1kg*10
            $table->decimal('final_msp', 10, 2)->default(0);           // Final MSP (Rs./kg)
            $table->decimal('final_customer_price', 10, 2);            // Final Customer Price (Rs./kg)
            $table->string('selling_expense_basis', 20)->default('%'); // '%' | 'Rs/kg'
            $table->decimal('selling_expense_value', 8, 3)->default(0);// Selling Expenses / ORC %
            $table->decimal('selling_expenses', 8, 3)->default(0);     // Selling Expenses (Rs./kg)
            $table->decimal('additional_realization', 10, 2)->default(0); // can be negative

            // workflow
            $table->string('status', 20)->default('Pending');   // Pending / Approved / Rejected
            $table->text('reject_reason')->nullable();
            $table->unsignedInteger('action_by')->nullable();   // admin who approved/rejected
            $table->timestamp('action_at')->nullable();
            $table->unsignedBigInteger('customer_discount_id')->nullable(); // row created on approval

            $table->timestamps();

            $table->index('user_id');
            $table->index('customer_id');
            $table->index('product_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_price_requests');
    }
}
