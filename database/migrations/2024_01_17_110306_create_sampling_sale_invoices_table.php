<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplingSaleInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sampling_sale_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('sampling_id')->index()->nullable();
            $table->foreign('sampling_id')->references('id')->on('samplings')->onDelete('cascade');
            $table->unsignedbigInteger('sampling_item_id')->index()->nullable();
            $table->foreign('sampling_item_id')->references('id')->on('sampling_items')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->integer('do_number');
            $table->string('do_ref_no');
            $table->string('do_financial_year',50);
            $table->date('do_date')->nullable();
            $table->date('sale_invoice_date')->nullable();
            $table->string('transport_name');
            $table->string('lr_no');
            $table->date('dispatch_date');
            $table->integer('qty');
            $table->decimal('price',10,2);
            $table->decimal('subtotal',10,2);
            $table->float('gst_per');
            $table->float('gst');
            $table->decimal('grand_total',10,2);
            $table->tinyInteger('is_delivered');
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
        Schema::dropIfExists('sampling_sale_invoices');
    }
}
