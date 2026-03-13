<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLinkedDealerIdToDealersTable extends Migration
{
    public function up()
    {
        Schema::table('dealers', function (Blueprint $table) {
            // Adding linked_dealer_id to store the Parent Dealer ID
            $table->unsignedBigInteger('linked_dealer_id')->nullable()->after('id');
            // Adding foreign key reference to the same table
            $table->foreign('linked_dealer_id')->references('id')->on('dealers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropForeign(['linked_dealer_id']);
            $table->dropColumn('linked_dealer_id');
        });
    }
}
