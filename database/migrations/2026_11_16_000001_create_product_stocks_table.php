<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Running balance of finished-product stock, kept apart from products.current_stock
 * so the old column can stay exactly as it is while this one takes over.
 *
 * One row per product:
 *   production_stock + incoming_stock = total_stock
 */
class CreateProductStocksTable extends Migration
{
    public function up()
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('production_stock', 15, 3)->default(0);
            $table->decimal('incoming_stock', 15, 3)->default(0);
            $table->decimal('total_stock', 15, 3)->default(0);
            $table->timestamps();

            $table->unique('product_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stocks');
    }
}
