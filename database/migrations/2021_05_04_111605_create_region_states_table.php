<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('region_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('region_id')->index()->nullable();
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
            $table->string('state');
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
        Schema::dropIfExists('region_states');
    }
}
