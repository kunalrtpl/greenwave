<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByToTrialsTable extends Migration
{
    public function up()
    {
        Schema::table('trials', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
        });

        // Optional: Set existing rows created_by = user_id
        DB::statement('UPDATE trials SET created_by = user_id');

        Schema::table('trials', function (Blueprint $table) {
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('trials', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
}