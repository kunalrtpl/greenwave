<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleInvoiceDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_invoice_discounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('sale_invoice_id')->index()->nullable();
            $table->foreign('sale_invoice_id')->references('id')->on('sale_invoices')->onDelete('cascade');
            $table->string('discount_type');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->decimal('dealer_share_per',10,2);
            $table->decimal('company_share_per',10,2);
            $table->decimal('total_share_per',10,2);
             $table->decimal('dealer_share',10,2);
            $table->decimal('company_share',10,2);
            $table->decimal('total_share',10,2);
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
        Schema::dropIfExists('sale_invoice_discounts');
    }
}
