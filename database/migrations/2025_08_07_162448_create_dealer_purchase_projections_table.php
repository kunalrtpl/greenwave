<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealerPurchaseProjectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_purchase_projections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->double('projected_qty',8,3);
            $table->string('action');
            $table->string('month_year');
            $table->unsignedbigInteger('created_by')->index()->nullable();
            $table->foreign('created_by')->references('id')->on('dealers')->onDelete('cascade');
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
        Schema::dropIfExists('dealer_purchase_projections');
    }
}
