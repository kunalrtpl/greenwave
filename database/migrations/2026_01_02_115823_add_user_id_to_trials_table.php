<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToTrialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trials', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                  ->after('id')
                  ->index()
                  ->nullable();

            // OPTIONAL (recommended if users table exists)
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
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
        Schema::table('trials', function (Blueprint $table) {
            // $table->dropForeign(['user_id']); // if FK added
            $table->dropColumn('user_id');
        });
    }
}
