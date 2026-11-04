<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * The products on one stock transfer — one row per product.
 *
 * The qty is fixed when the request is raised: the receiving employee accepts
 * or refuses the whole transfer, so there is nothing to edit here afterwards.
 */
class CreateStockTransferItemsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('stock_transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('qty', 12, 3);

            $table->timestamps();

            $table->unique(['stock_transfer_id', 'product_id'], 'sti_transfer_product_unq');

            $table->foreign('stock_transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfer_items');
    }
}
