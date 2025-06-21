<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketProductInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('market_product_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dealer_id');
            $table->integer('customer_id');
            $table->integer('product_category_id');
            $table->text('others');
            $table->string('product_category_name');
            $table->string('product_name');
            $table->string('make');
            $table->string('dealer_name');
            $table->float('price');
            $table->string('dosage');
            $table->string('monthly_consumption');
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
        Schema::dropIfExists('market_product_infos');
    }
}
