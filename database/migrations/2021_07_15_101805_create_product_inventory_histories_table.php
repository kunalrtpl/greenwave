<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductInventoryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_inventory_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('product_inventory_id')->index()->nullable();
            $table->foreign('product_inventory_id')->references('id')->on('product_inventories')->onDelete('cascade');
            $table->unsignedbigInteger('packing_size_id')->index()->nullable();
            $table->foreign('packing_size_id')->references('id')->on('packing_sizes')->onDelete('cascade');
            $table->string('status');
            $table->text('remarks');
            $table->unsignedbigInteger('updated_by')->index()->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('product_inventory_histories');
    }
}
