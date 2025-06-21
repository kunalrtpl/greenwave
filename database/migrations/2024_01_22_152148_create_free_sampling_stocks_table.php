<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFreeSamplingStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('free_sampling_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->decimal('stock_in_hand',10,3);
            $table->decimal('in_transit',10,3);
            $table->decimal('pending_orders',10,3);
            $table->decimal('pending_customer_orders',10,3);
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
        Schema::dropIfExists('free_sampling_stocks');
    }
}
