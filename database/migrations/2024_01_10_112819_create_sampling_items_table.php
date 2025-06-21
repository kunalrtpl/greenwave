<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplingItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sampling_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('sampling_id')->index()->nullable();
            $table->foreign('sampling_id')->references('id')->on('samplings')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedbigInteger('product_detail_id')->index()->nullable();
            $table->foreign('product_detail_id')->references('id')->on('product_details')->onDelete('cascade');
            $table->integer('pack_size');
            $table->integer('no_of_packs');
            $table->float('qty');
            $table->float('actual_qty');
            $table->float('dispatched_qty');
            $table->integer('actual_pack_size');
            $table->decimal('price')->nullable();
            $table->float('additional_cost')->nullable();
            $table->decimal('net_price')->nullable();
            $table->text('discounts');
            $table->longtext('raw_materials');
            $table->text('comments');
            $table->string('item_action')
            $table->tinyInteger('is_urgent')->default(0);
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
        Schema::dropIfExists('sampling_items');
    }
}
