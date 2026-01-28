<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestProductIdToSamplingItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sampling_items', function (Blueprint $table) {
            $table->unsignedBigInteger('requested_product_id')
                  ->nullable()
                  ->after('sampling_id');
            $table->foreign('requested_product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sampling_items', function (Blueprint $table) {
            $table->dropColumn('request_product_id');
        });
    }
}
