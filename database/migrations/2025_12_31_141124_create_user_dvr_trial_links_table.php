<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDvrTrialLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_dvr_trial_links', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_dvr_id')->nullable()->index();
            $table->unsignedBigInteger('trial_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('user_dvr_id')
                  ->references('id')
                  ->on('user_dvrs')
                  ->onDelete('cascade');

            $table->foreign('trial_id')
                  ->references('id')
                  ->on('trials')
                  ->onDelete('cascade');

            $table->unique(['user_dvr_id', 'trial_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_dvr_trial_links');
    }
}
