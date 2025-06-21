<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('business_name');
            $table->string('city');
            $table->string('office_phone');
            $table->string('owner_name');
            $table->string('owner_mobile');
            $table->string('password');
            $table->string('email');
            $table->integer('payment_term');
            $table->float('security_amount');
            $table->float('interest_rate_on_security');
            $table->float('credit_multiple');
            $table->float('credit_allowed');
            $table->float('freight');
            $table->string('base_sale_margin_lock');
            $table->float('base_sale_level_to_archive');
            $table->float('margin_lock');
            $table->string('applicable_from');
            $table->string('applicable_to');
            $table->tinyInteger('status');
            $table->string('notification_token');
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
        Schema::dropIfExists('dealers');
    }
}
