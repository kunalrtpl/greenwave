<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDvrCustomerContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_dvr_customer_contacts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_dvr_id')->index();
            $table->integer('customer_contact_id')->index();

            $table->timestamps();

            // Foreign key
            $table->foreign('user_dvr_id')
                ->references('id')
                ->on('user_dvrs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_dvr_customer_contacts');
    }
}
