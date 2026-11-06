<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/** The products on one monthly sale — one row per product, qty up to 3 decimals. */
class CreateMonthlySaleProductsTable extends Migration
{
    public function up()
    {
        Schema::create('monthly_sale_products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('monthly_sale_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('qty', 12, 3);

            $table->timestamps();

            $table->unique(['monthly_sale_id', 'product_id'], 'msp_sale_product_unq');

            $table->foreign('monthly_sale_id')->references('id')->on('monthly_sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_sale_products');
    }
}
