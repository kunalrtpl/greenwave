<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchSheetProductChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_sheet_product_checklists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('batch_sheet_id')->index()->nullable();
            $table->foreign('batch_sheet_id')->references('id')->on('batch_sheets')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedbigInteger('checklist_id')->index()->nullable();
            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');
            $table->string('product_range');
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
        Schema::dropIfExists('batch_sheet_product_checklists');
    }
}
