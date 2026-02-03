<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerRegisterRequestIdToFreeSamplingStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('free_sampling_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_register_request_id')
                  ->nullable()
                  ->after('customer_id')
                  ->index();

            $table->foreign('customer_register_request_id')
                  ->references('id')
                  ->on('customer_register_requests')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('free_sampling_stocks', function (Blueprint $table) {
            // first drop foreign key, then column
            $table->dropForeign(['customer_register_request_id']);
            $table->dropColumn('customer_register_request_id');
        });
    }
}
