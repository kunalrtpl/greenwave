<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('region_cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('region_id')->index()->nullable();
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
            $table->string('state');
            $table->string('city');
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
        Schema::dropIfExists('region_cities');
    }
}
