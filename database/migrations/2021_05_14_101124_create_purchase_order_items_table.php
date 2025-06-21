<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('trader_po')->default(0);
            $table->unsignedbigInteger('purchase_order_id')->index()->nullable();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->integer('qty');
            $table->integer('actual_qty');
            $table->integer('dispatched_qty');
            $table->unsignedbigInteger('product_detail_id')->index()->nullable();
            $table->foreign('product_detail_id')->references('id')->on('product_details')->onDelete('cascade');
            $table->unsignedbigInteger('packing_size_id')->index()->nullable();
            $table->foreign('packing_size_id')->references('id')->on('packing_sizes')->onDelete('cascade');
            $table->string('inherit_type');
            $table->string('batch_out_duration');
            $table->float('rm_cost');
            $table->float('formulation_cost');
            $table->float('packing_cost');
            $table->float('total_product_cost');
            $table->float('dp_calculation_cost');
            $table->float('company_mark_up');
            $table->float('dealer_price');
            $table->float('dealer_markup');
            $table->float('freight');
            $table->float('market_price');
            $table->float('net_price');
            $table->float('product_price');
            $table->float('spsod');
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
        Schema::dropIfExists('purchase_order_items');
    }
}
