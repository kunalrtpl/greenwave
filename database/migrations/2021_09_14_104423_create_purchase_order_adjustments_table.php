<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_adjustments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->unsignedbigInteger('purchase_order_id')->index()->nullable();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->unsignedbigInteger('purchase_order_item_id')->index()->nullable();
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items')->onDelete('cascade');
            $table->float('qty');
            $table->string('adjustment_by');
            $table->integer('ref_id');
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
        Schema::dropIfExists('purchase_order_adjustments');
    }
}
