<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->string('name');
            $table->string('mobile');
            $table->string('designation');
            $table->string('email');
            $table->string('decrypt_password');
            $table->string('password');
            $table->string('notification_token');
            $table->tinyInteger('is_delete');
            $table->smallInteger('otp');
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
        Schema::dropIfExists('customer_employees');
    }
}
