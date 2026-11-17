<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * One row per product used to be the rule on a stock transfer. The same
 * product may now appear twice when it is being sent out of two different
 * batches, so uniqueness moves from product to product + batch.
 */
class AllowOneProductPerBatchOnStockTransfers extends Migration
{
    public function up()
    {
        /*
         | The new index is created first: the old one leads with
         | stock_transfer_id and the foreign key leans on it, so dropping it
         | before a replacement exists is refused (MySQL error 1553).
         */
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->unique(['stock_transfer_id', 'product_id', 'batch_ref'], 'sti_transfer_product_batch_unq');
        });

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropUnique('sti_transfer_product_unq');
        });
    }

    public function down()
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->unique(['stock_transfer_id', 'product_id'], 'sti_transfer_product_unq');
        });

        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropUnique('sti_transfer_product_batch_unq');
        });
    }
}
