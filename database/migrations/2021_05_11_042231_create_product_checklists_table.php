<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_checklists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedbigInteger('checklist_id')->index()->nullable();
            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');
            $table->string('range');
            $table->string('remarks');
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
        Schema::dropIfExists('product_checklists');
    }
}
