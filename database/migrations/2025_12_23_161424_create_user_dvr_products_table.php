<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDvrProductsTable extends Migration
{
    public function up()
    {
        Schema::create('user_dvr_products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_dvr_id')->index();
            $table->unsignedBigInteger('product_id')->index();

            $table->timestamps();

            $table->foreign('user_dvr_id')
                ->references('id')
                ->on('user_dvrs')
                ->onDelete('cascade');

             $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_dvr_products');
    }
}
