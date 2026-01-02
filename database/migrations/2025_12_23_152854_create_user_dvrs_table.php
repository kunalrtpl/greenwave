<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDvrsTable extends Migration
{
    public function up()
    {
        Schema::create('user_dvrs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->integer('customer_register_request_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->date('dvr_date')->index();

            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();

            $table->string('start_location')->nullable();
            $table->string('end_location')->nullable();

            $table->string('start_lat_long')->nullable();
            $table->string('end_lat_long')->nullable();

            $table->string('visit_recorded')->nullable();
            $table->string('purpose_of_visit', 191)->nullable();

            $table->text('other')->nullable();
            $table->text('query')->nullable();

            $table->integer('complaint_id')->nullable()->index();
            $table->text('other_purpose')->nullable();

            $table->string('visit_type')->nullable();
            $table->longText('visit_detail')->nullable();

            $table->text('remarks')->nullable();
            $table->text('next_plan')->nullable();
            $table->string('verify_later_reason')->nullable();
            $table->string('site_type')->nullable();

            $table->unsignedBigInteger('sample_submission_id')->nullable()->index();
            $table->unsignedBigInteger('market_sample_id')->nullable()->index();
            $table->unsignedBigInteger('complaint_sample_id')->nullable()->index();
            $table->unsignedBigInteger('user_scheduler_id')->nullable()->index();

            $table->integer('customer_contact_id')->nullable()->index();

            $table->dateTime('dvr_verified_date_time')->nullable();
            $table->tinyInteger('have_you_met')->nullable();
            $table->tinyInteger('ongoing_visit')->nullable();
            $table->tinyInteger('is_submitted')->nullable();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_dvrs');
    }
}
