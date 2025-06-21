<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchSheetMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_sheet_materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('batch_sheet_id')->index()->nullable();
            $table->foreign('batch_sheet_id')->references('id')->on('batch_sheets')->onDelete('cascade');
            $table->bigInteger('raw_material_id')->index()->nullable();
            $table->integer('qty');
            $table->integer('issued_qty');
            $table->integer('percentage_included');
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
        Schema::dropIfExists('batch_sheet_materials');
    }
}
