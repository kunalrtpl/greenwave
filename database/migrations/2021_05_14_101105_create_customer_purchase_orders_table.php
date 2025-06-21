<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->integer('customer_employee_id');
            $table->string('action');
            $table->string('customer_purchase_order_no');
            $table->string('mode');
            $table->decimal('price',10,2);
            $table->string('payment_term');
            $table->decimal('payment_discount_per',10,2);
            $table->decimal('payment_discount',10,2);
            $table->decimal('corporate_discount_per',10,2);
            $table->decimal('corporate_discount',10,2);
            $table->decimal('gst_per');
            $table->decimal('gst');
            $table->decimal('grand_total');
            $table->text('remarks');
            $table->string('po_status')->default('pending');
            $table->text('comments');
            $table->string('po_edited');
            $table->string('edited_by');
            $table->integer('edited_by_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_purchase_orders');
    }
}
