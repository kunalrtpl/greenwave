<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSchedulersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_schedulers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedbigInteger('customer_register_request_id')->index()->nullable();
            $table->foreign('customer_register_request_id')->references('id')->on('customer_register_requests')->onDelete('cascade');
            $table->unsignedbigInteger('dvr_id')->index()->nullable();
            $table->foreign('dvr_id')->references('id')->on('dvrs')->onDelete('cascade');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedbigInteger('previous_scheduler_id')->index()->nullable();
            $table->unsignedbigInteger('next_scheduler_id')->index()->nullable();
            $table->date('scheduler_date');
            $table->time('scheduler_time');
            $table->longtext('description');
            $table->string('status')->default('Open');
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
        Schema::dropIfExists('user_schedulers');
    }
}
