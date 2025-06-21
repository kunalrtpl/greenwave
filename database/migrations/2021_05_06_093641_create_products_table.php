<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('lab_recipe_number');
            $table->string('product_name');
            $table->unsignedbigInteger('product_detail_id')->index()->nullable();
            $table->foreign('product_detail_id')->references('id')->on('product_details')->onDelete('cascade');
            $table->string('product_code');
            $table->string('hsn_code');
            $table->tinyInteger('is_trader_product');
            $table->string('description');
            $table->string('how_to_use');
            $table->string('suggested_dosage');
            $table->unsignedbigInteger('packing_size_id')->index()->nullable();
            $table->foreign('packing_size_id')->references('id')->on('packing_sizes')->onDelete('set null');
            $table->string('moq');
            $table->string('technical_literature');
            $table->string('msds');
            $table->string('certification');
            $table->string('inherit_type');
            $table->string('batch_out_duration');
            $table->float('rm_cost');
            $table->float('formulation_cost');
            $table->float('product_price');
            $table->float('packing_cost');
            $table->float('total_product_cost');
            $table->float('dp_calculation_cost');
            $table->float('company_mark_up');
            $table->float('dealer_price');
            $table->float('dealer_markup');
            $table->float('freight');
            $table->float('market_price');
            $table->integer('shelf_life');
            $table->biginteger('free_sample_unit');
            $table->text('keywords');
            $table->tinyInteger('status');
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
        Schema::dropIfExists('products');
    }
}
