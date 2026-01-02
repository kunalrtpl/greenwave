<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToUserDvrAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::table('user_dvr_attachments', function (Blueprint $table) {
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

    public function down()
    {
        Schema::table('user_dvr_attachments', function (Blueprint $table) {
            // $table->dropForeign(['user_id']); // if FK added
            $table->dropColumn('user_id');
        });
    }
}
