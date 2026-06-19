<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealerOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('dealer_otps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identifier', 191)->comment('email or mobile number');
            $table->enum('identifier_type', ['email', 'mobile']);
            $table->string('otp', 6);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['identifier', 'identifier_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dealer_otps');
    }
}