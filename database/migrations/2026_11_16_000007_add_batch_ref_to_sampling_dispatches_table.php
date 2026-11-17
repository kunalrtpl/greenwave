<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A dispatch already records the batch number it went out on. It now records
 * the batch reference beside it — "<source>:<id>", see
 * App\Services\ProductBatchService — so the material an employee is holding
 * can be traced back to the exact batch row it came from, whichever book that
 * row lives in.
 */
class AddBatchRefToSamplingDispatchesTable extends Migration
{
    public function up()
    {
        Schema::table('sampling_dispatches', function (Blueprint $table) {
            $table->string('batch_ref', 100)->nullable()->after('batch_no');
        });

        // Dispatches made before this column: matched back by batch number,
        // and only where that number points at exactly one batch.
        DB::statement("
            UPDATE sampling_dispatches d
              JOIN (
                   SELECT supplier_batch_no, product_id, MIN(id) AS id, COUNT(*) AS c
                     FROM product_inventories
                    WHERE type = 'RFDM'
                 GROUP BY supplier_batch_no, product_id
              ) b ON b.supplier_batch_no = d.batch_no AND b.product_id = d.product_id AND b.c = 1
               SET d.batch_ref = CONCAT('inventory:', b.id)
             WHERE d.batch_ref IS NULL AND d.batch_no IS NOT NULL AND d.batch_no <> ''
        ");
    }

    public function down()
    {
        Schema::table('sampling_dispatches', function (Blueprint $table) {
            $table->dropColumn('batch_ref');
        });
    }
}
