<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * A sample stock adjustment now says which batch it came off.
 *
 * Same pair as everywhere else: batch_ref is how the batch is addressed —
 * "<source>:<id>", see App\Services\ProductBatchService — and batch_no is the
 * number itself, kept beside it. Nullable for adjustments made before this,
 * required on everything posted from now on.
 */
class AddBatchToSampleStockAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::table('sample_stock_adjustments', function (Blueprint $table) {
            $table->string('batch_ref', 100)->nullable()->after('product_id');
            $table->string('batch_no', 100)->nullable()->after('batch_ref');
            $table->index('batch_ref');
        });
    }

    public function down()
    {
        Schema::table('sample_stock_adjustments', function (Blueprint $table) {
            $table->dropIndex(['batch_ref']);
            $table->dropColumn(['batch_ref', 'batch_no']);
        });
    }
}
