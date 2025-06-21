<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('serial_no');
            $table->string('batch_no');
            $table->string('no_of_packs');
            $table->string('no_of_samples');
            $table->bigInteger('stock');
            $table->text('remarks');
            $table->decimal('price');
            $table->unsignedbigInteger('packing_size_id')->index()->nullable();
            $table->foreign('packing_size_id')->references('id')->on('packing_sizes')->onDelete('cascade');
            $table->string('change_required');
            $table->integer('change_packing_size_id');
            $table->integer('material_fill');
            $table->integer('no_of_packs_required');
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
        Schema::dropIfExists('product_inventories');
    }
}
