<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDepartmentRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_department_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedbigInteger('user_department_id')->index()->nullable();
            $table->foreign('user_department_id')->references('id')->on('user_departments')->onDelete('cascade');
            $table->unsignedbigInteger('region_id')->index()->nullable();
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
            $table->unsignedbigInteger('sub_region_id')->index()->nullable();
            $table->foreign('sub_region_id')->references('id')->on('regions')->onDelete('cascade');
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
        Schema::dropIfExists('user_department_regions');
    }
}
