<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductInventoryChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_inventory_checklists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->mediumInteger('sample_no');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->bigInteger('product_inventory_id');
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
        Schema::dropIfExists('product_inventory_checklists');
    }
}
