<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Dealers linked to a Marketing employee, picked on the admin user screen.
 *
 * Only top-level dealers (parent_id NULL) in the "confirmed" stage can be
 * picked. Rows are replaced on every save, and cleared when the user is moved
 * out of the Marketing department.
 */
class CreateUserLinkedDealersTable extends Migration
{
    public function up()
    {
        Schema::create('user_linked_dealers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('dealer_id');
            $table->timestamps();

            $table->unique(['user_id', 'dealer_id']);
            $table->index('dealer_id');

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('dealer_id')
                  ->references('id')->on('dealers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_linked_dealers');
    }
}
