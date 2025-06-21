<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('purchase_order_id')->index()->nullable();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->string('dealer_invoice_no');
            $table->date('sale_invoice_date');
            $table->decimal('price',10,2);
            $table->string('payment_term');
            $table->decimal('payment_discount_per',10,2);
            $table->decimal('payment_discount',10,2);
            $table->decimal('corporate_discount_per',10,2);
            $table->decimal('corporate_discount',10,2);
            $table->decimal('gst_per');
            $table->decimal('gst');
            $table->decimal('grand_total',10,2);
            $table->float('spsod');
            $table->text('remarks');
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
        Schema::dropIfExists('sale_invoices');
    }
}
