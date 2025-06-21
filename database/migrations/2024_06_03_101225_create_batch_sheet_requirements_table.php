<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchSheetRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_sheet_requirements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('batch_sheet_id')->index()->nullable();
            $table->foreign('batch_sheet_id')->references('id')->on('batch_sheets')->onDelete('cascade');
            $table->unsignedbigInteger('raw_material_inventory_id')->index()->nullable();
            $table->foreign('raw_material_inventory_id')->references('id')->on('raw_material_inventories')->onDelete('cascade');
            $table->unsignedbigInteger('product_inventory_id')->index()->nullable();
            $table->foreign('product_inventory_id')->references('id')->on('product_inventories')->onDelete('cascade');
            $table->unsignedbigInteger('ref_batch_sheet_id')->index()->nullable();
            $table->decimal('qty_per',10,3);
            $table->decimal('qty',10,3);
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
        Schema::dropIfExists('batch_sheet_requirements');
    }
}
