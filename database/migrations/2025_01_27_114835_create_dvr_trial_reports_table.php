<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDvrTrialReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dvr_trial_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('dvr_id')->index()->nullable();
            $table->foreign('dvr_id')->references('id')->on('dvrs')->onDelete('cascade');
            $table->unsignedbigInteger('trial_report_id')->index()->nullable();
            $table->foreign('trial_report_id')->references('id')->on('trial_reports')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dvr_trial_reports');
    }
}
