<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDvrAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::create('user_dvr_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_dvr_id')->index();
            $table->unsignedBigInteger('user_dvr_trial_id')->nullable()->index();

            $table->string('type')->nullable();
            $table->string('label')->nullable();
            $table->string('file')->nullable();

            $table->tinyInteger('share')->default(0);

            $table->timestamps();

            $table->foreign('user_dvr_id')
                ->references('id')
                ->on('user_dvrs')
                ->onDelete('cascade');

            $table->foreign('user_dvr_trial_id')
                ->references('id')
                ->on('user_dvr_trials')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_dvr_attachments');
    }
}
