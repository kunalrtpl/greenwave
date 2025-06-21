<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInterDealerStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inter_dealer_stock_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('from_dealer_id')->index()->nullable();
            $table->foreign('from_dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('to_dealer_id')->index()->nullable();
            $table->foreign('to_dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->float('transfer_stock');
            $table->float('from_dealer_stock');
            $table->float('to_dealer_stock');
            $table->string('transfer_date');
            $table->string('invoice_number');
            $table->longtext('remarks');
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
        Schema::dropIfExists('inter_dealer_stock_logs');
    }
}
