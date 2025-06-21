<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrialReportBathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trial_report_baths', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('trial_report_id')->index()->nullable();
            $table->foreign('trial_report_id')->references('id')->on('trial_reports')->onDelete('cascade');
            $table->longtext('description')->nullable();
            $table->decimal('material',8,3)->nullable();
            $table->decimal('liquor',8,3)->nullable();
            $table->longtext('application_details')->nullable();
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
        Schema::dropIfExists('trial_report_baths');
    }
}
