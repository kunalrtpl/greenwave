<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceDealerIdToPurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('source_dealer_id')
                  ->nullable()
                  ->after('dealer_id')
                  ->index();
            
            $table->foreign('source_dealer_id')
                  ->references('id')
                  ->on('dealers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['source_dealer_id']);
            $table->dropColumn('source_dealer_id');
        });
    }
}
