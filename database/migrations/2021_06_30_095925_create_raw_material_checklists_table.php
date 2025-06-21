<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRawMaterialChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_material_checklists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('raw_material_id')->index()->nullable();
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('cascade');
            $table->unsignedbigInteger('checklist_id')->index()->nullable();
            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');
            $table->string('range');
            $table->text('remarks');
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
        Schema::dropIfExists('raw_material_checklists');
    }
}
