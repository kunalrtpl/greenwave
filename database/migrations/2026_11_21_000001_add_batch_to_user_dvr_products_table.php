<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock used in a trial comes out of a named batch.
 *
 * The V3 trial API deducts the qty from the employee's stock, so the line has
 * to say which batch it came from — same as a sample handed to a customer or a
 * write-off. batch_no is kept beside the reference so a row still reads on its
 * own once the batch it points at is gone.
 *
 * own_stock is left in place for the rows already written with it, and marked
 * as unused: every V3 trial line now moves stock, so the flag no longer
 * decides anything.
 */
class AddBatchToUserDvrProductsTable extends Migration
{
    public function up()
    {
        Schema::table('user_dvr_products', function (Blueprint $table) {
            $table->string('batch_ref', 100)->nullable()->after('product_id');
            $table->string('batch_no', 100)->nullable()->after('batch_ref');

            $table->index('batch_ref', 'udp_batch_ref_idx');
        });

        DB::statement(
            "ALTER TABLE user_dvr_products MODIFY own_stock TINYINT(1) NOT NULL DEFAULT 0 "
            . "COMMENT 'Deprecated: every V3 trial line consumes the employee stock named by batch_ref. Kept for historical rows.'"
        );
    }

    public function down()
    {
        DB::statement("ALTER TABLE user_dvr_products MODIFY own_stock TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''");

        Schema::table('user_dvr_products', function (Blueprint $table) {
            $table->dropIndex('udp_batch_ref_idx');
            $table->dropColumn(['batch_ref', 'batch_no']);
        });
    }
}
