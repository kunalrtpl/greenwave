<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackingSizeInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packing_size_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('packing_size_id')->index()->nullable();
            $table->foreign('packing_size_id')->references('id')->on('packing_sizes')->onDelete('cascade');
            $table->string('type');
            $table->decimal('price',10,2);
            $table->bigInteger('stock');
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
        Schema::dropIfExists('packing_size_inventories');
    }
}
