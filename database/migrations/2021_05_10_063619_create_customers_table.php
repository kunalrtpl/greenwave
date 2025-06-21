<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('category');
            $table->text('activity');
            $table->string('address');
            $table->string('mobile');
            $table->string('email');
            $table->string('password');
            $table->string('business_model');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->string('notification_token');
            $table->string('payment_term_type');
            $table->string('payment_term');
            $table->float('payment_discount');
            $table->tinyInteger('status');
            $table->smallInteger('otp');
            $table->string('is_spsod');
            $table->string('is_monthly_turnover_discount');
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
        Schema::dropIfExists('customers');
    }
}
