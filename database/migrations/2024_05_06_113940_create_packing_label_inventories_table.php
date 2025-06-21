<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackingLabelInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packing_label_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('label_id')->index()->nullable();
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->integer('stock');
            $table->text('remarks');
            $table->unsignedbigInteger('added_by')->index()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('packing_label_inventories');
    }
}
