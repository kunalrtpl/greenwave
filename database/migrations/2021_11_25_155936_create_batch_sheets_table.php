<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_sheets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('batch_no');
            $table->string('batch_no_str');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->integer('batch_size');
            $table->string('machine_number');
            $table->string('machine_capacity');
            $table->string('operator_name');
            $table->string('batch_start_time');
            $table->string('expected_batch_out_time');
            $table->string('batch_out_complete');
            $table->unsignedbigInteger('packing_size_id')->index()->nullable();
            $table->foreign('packing_size_id')->references('id')->on('packing_sizes')->onDelete('cascade');
            $table->integer('no_of_packing_required');
            $table->string('status');
            $table->integer('created_by');
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
        Schema::dropIfExists('batch_sheets');
    }
}
