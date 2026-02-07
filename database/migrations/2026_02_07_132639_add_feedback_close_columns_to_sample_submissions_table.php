<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeedbackCloseColumnsToSampleSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::table('sample_submissions', function (Blueprint $table) {

            $table->unsignedBigInteger('feedback_close_user_id')
                ->nullable()
                ->after('feedback_dealer_id');

            $table->unsignedBigInteger('feedback_close_dealer_id')
                ->nullable()
                ->after('feedback_close_user_id');

            // Foreign Keys
            $table->foreign('feedback_close_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('feedback_close_dealer_id')
                ->references('id')
                ->on('dealers')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('sample_submissions', function (Blueprint $table) {

            $table->dropForeign(['feedback_close_user_id']);
            $table->dropForeign(['feedback_close_dealer_id']);

            $table->dropColumn('feedback_close_user_id');
            $table->dropColumn('feedback_close_dealer_id');
        });
    }
}
