<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDvrTrialsTable extends Migration
{
    public function up()
    {
        Schema::create('user_dvr_trials', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_dvr_id')->index();
            $table->unsignedBigInteger('trial_report_id')->nullable()->index();

            $table->string('trial_type')->nullable();
            $table->integer('complaint_id')->nullable()->index();
            $table->unsignedBigInteger('other_team_member_id')->nullable()->index();

            $table->text('objective')->nullable();

            $table->tinyInteger('trial_done')->default(0);
            $table->tinyInteger('is_jointly')->default(0);

            $table->string('other_team_member_name')->nullable();
            $table->string('status')->nullable();

            $table->timestamps();

            $table->foreign('user_dvr_id')
                ->references('id')
                ->on('user_dvrs')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_dvr_trials');
    }
}
