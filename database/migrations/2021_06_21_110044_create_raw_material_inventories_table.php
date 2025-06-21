<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRawMaterialInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_material_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('raw_material_id')->index()->nullable();
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('cascade');
            $table->string('serial_no');
            $table->integer('no_of_packs');
            $table->integer('no_of_samples');
            $table->bigInteger('stock');
            $table->bigInteger('remaining_stock');
            $table->text('remarks');
            $table->decimal('price');
            $table->string('supplier_batch_no');
            $table->unsignedbigInteger('packing_size_id')->index()->nullable();
            $table->foreign('packing_size_id')->references('id')->on('packing_sizes')->onDelete('cascade');
            $table->unsignedbigInteger('added_by')->index()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('status');
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
        Schema::dropIfExists('raw_material_inventories');
    }
}
