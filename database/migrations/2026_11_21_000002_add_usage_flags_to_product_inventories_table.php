<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Where a finished-product batch is allowed to be used.
 *
 * Add Incoming Material now asks, for a Finished Product (RFD) entry only,
 * whether the batch may go out as a sample and whether it may go out on a
 * dispatch order. Both are ticked by default, so the normal entry behaves
 * exactly as it always has.
 *
 *   used_in_sample          — the batch is offered on Sample Dispatch Entry
 *   used_in_dispatch_order  — reserved for the dispatch order screens; stored
 *                             now so the entry desk can set it from day one,
 *                             nothing filters on it yet
 *
 * Every row that already exists is marked true: those batches were entered
 * before the question was asked, and they have always been usable for both,
 * so anything else would quietly withdraw stock the desk is relying on.
 */
class AddUsageFlagsToProductInventoriesTable extends Migration
{
    public function up()
    {
        Schema::table('product_inventories', function (Blueprint $table) {
            $table->boolean('used_in_sample')->default(1)->after('supplier_batch_no');
            $table->boolean('used_in_dispatch_order')->default(1)->after('used_in_sample');

            // Sample Dispatch Entry lists batches by product and this flag.
            $table->index(['product_id', 'used_in_sample'], 'pi_product_used_in_sample_idx');
        });

        // The column default already covers the existing rows; this is written
        // out so the intent survives even if the default is ever changed.
        DB::table('product_inventories')->update([
            'used_in_sample'         => 1,
            'used_in_dispatch_order' => 1,
        ]);
    }

    public function down()
    {
        Schema::table('product_inventories', function (Blueprint $table) {
            $table->dropIndex('pi_product_used_in_sample_idx');
            $table->dropColumn(['used_in_sample', 'used_in_dispatch_order']);
        });
    }
}
