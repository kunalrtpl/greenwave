<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDealerIdToQtyDiscountsTable extends Migration
{
    public function up()
    {
        Schema::table('qty_discounts', function (Blueprint $table) {

            $table->unsignedBigInteger('dealer_id')
                  ->nullable()
                  ->after('product_id');

            $table->foreign('dealer_id')
                  ->references('id')
                  ->on('dealers')
                  ->onDelete('set null'); // important for nullable

        });
    }

    public function down()
    {
        Schema::table('qty_discounts', function (Blueprint $table) {

            $table->dropForeign(['dealer_id']);
            $table->dropColumn('dealer_id');

        });
    }
}