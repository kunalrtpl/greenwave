<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * A stock transfer now says which batch the material is from.
 *
 * batch_ref is how the batch is addressed — "<source>:<id>", see
 * App\Services\ProductBatchService — and batch_no is the number itself, kept
 * beside it so a transfer still reads correctly years later even if the row it
 * came from is gone. Both are nullable for transfers raised before this, and
 * required on everything raised from now on.
 */
class AddBatchToStockTransferItemsTable extends Migration
{
    public function up()
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->string('batch_ref', 100)->nullable()->after('product_id');
            $table->string('batch_no', 100)->nullable()->after('batch_ref');
        });

    }

    public function down()
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropColumn(['batch_ref', 'batch_no']);
        });
    }
}
