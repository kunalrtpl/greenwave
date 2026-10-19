<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Single-page Sample Dispatch (admin/sample-dispatch).
 *
 *  - is_voluntarily       : 1 when the sampling was created directly by admin at
 *                           dispatch time (no prior request from the executive).
 *  - dispatch_dealer_id   : dealer the material was dispatched to.
 *  - dispatch_customer_id : customer the material was dispatched to.
 */
class AddDispatchFieldsToSamplingsTable extends Migration
{
    public function up()
    {
        Schema::table('samplings', function (Blueprint $table) {
            $table->boolean('is_voluntarily')->default(0)->after('sample_type');
            $table->unsignedBigInteger('dispatch_dealer_id')->nullable()->after('dispatch_to');
            $table->unsignedBigInteger('dispatch_customer_id')->nullable()->after('dispatch_dealer_id');

            $table->foreign('dispatch_dealer_id')->references('id')->on('dealers')->onDelete('set null');
            $table->foreign('dispatch_customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('samplings', function (Blueprint $table) {
            $table->dropForeign(['dispatch_dealer_id']);
            $table->dropForeign(['dispatch_customer_id']);
            $table->dropColumn(['is_voluntarily', 'dispatch_dealer_id', 'dispatch_customer_id']);
        });
    }
}
