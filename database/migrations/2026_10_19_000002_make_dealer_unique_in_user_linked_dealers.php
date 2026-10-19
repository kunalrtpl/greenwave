<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * A dealer can be linked to only one employee. The admin screen already blocks
 * it; this makes the database refuse it too.
 */
class MakeDealerUniqueInUserLinkedDealers extends Migration
{
    public function up()
    {
        Schema::table('user_linked_dealers', function (Blueprint $table) {
            $table->unique('dealer_id', 'user_linked_dealers_dealer_id_unique');
        });
    }

    public function down()
    {
        Schema::table('user_linked_dealers', function (Blueprint $table) {
            $table->dropUnique('user_linked_dealers_dealer_id_unique');
        });
    }
}
