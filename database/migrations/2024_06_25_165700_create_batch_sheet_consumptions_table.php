<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchSheetConsumptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_sheet_consumptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('batch_sheet_id')->index()->nullable();
            $table->foreign('batch_sheet_id')->references('id')->on('batch_sheets')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedbigInteger('packing_type_id')->index()->nullable();
            $table->foreign('packing_type_id')->references('id')->on('packing_types')->onDelete('cascade');
            $table->integer('final_no_of_packs');
            $table->decimal('final_net_fill_size',8,3);
            $table->decimal('final_material_filled',8,3);
            $table->integer('packs_consumed');
            $table->unsignedbigInteger('label_id')->index()->nullable();
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->integer('labels_consumed');
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
        Schema::dropIfExists('batch_sheet_consumptions');
    }
}
