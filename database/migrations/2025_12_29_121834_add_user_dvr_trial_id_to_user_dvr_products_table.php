<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserDvrTrialIdToUserDvrProductsTable extends Migration
{
    public function up()
    {
        Schema::table('user_dvr_products', function (Blueprint $table) {
            // Add nullable column
            $table->unsignedBigInteger('user_dvr_trial_id')
                  ->nullable()
                  ->after('user_dvr_id')
                  ->index();

            // Add foreign key reference
            $table->foreign('user_dvr_trial_id')
                  ->references('id')
                  ->on('user_dvr_trials')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('user_dvr_products', function (Blueprint $table) {
            // Drop FK first
            $table->dropForeign(['user_dvr_trial_id']);

            // Drop column
            $table->dropColumn('user_dvr_trial_id');
        });
    }
}
