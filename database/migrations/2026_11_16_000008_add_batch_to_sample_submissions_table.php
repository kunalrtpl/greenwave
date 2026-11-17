<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * A sample submission now says which batch the material came out of.
 *
 * batch_ref is how the batch is addressed — "<source>:<id>", see
 * App\Services\ProductBatchService — and batch_no is the number itself, kept
 * beside it so the submission still reads correctly even if that batch row is
 * gone. Nullable for submissions made before this; required on everything
 * submitted from now on.
 */
class AddBatchToSampleSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::table('sample_submissions', function (Blueprint $table) {
            $table->string('batch_ref', 100)->nullable()->after('product_id');
            $table->string('batch_no', 100)->nullable()->after('batch_ref');
            $table->index('batch_ref');
        });
    }

    public function down()
    {
        Schema::table('sample_submissions', function (Blueprint $table) {
            $table->dropIndex(['batch_ref']);
            $table->dropColumn(['batch_ref', 'batch_no']);
        });
    }
}
