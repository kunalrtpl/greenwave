<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealerOperatingCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_operating_cities', function (Blueprint $table) {
            $table->bigIncrements('id');

            // dealer reference
            $table->unsignedBigInteger('dealer_id');

            // city name (or city_id if you have cities table)
            $table->string('city', 150);

            $table->timestamps();

            // indexes for fast search
            $table->index('dealer_id');
            $table->index('city');

            // foreign key (optional but recommended)
            // make sure dealers table uses bigIncrements('id')
            $table->foreign('dealer_id')
                  ->references('id')
                  ->on('dealers')
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
        Schema::dropIfExists('dealer_operating_cities');
    }
}
