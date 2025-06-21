<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchSheetHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_sheet_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('batch_sheet_id')->index()->nullable();
            $table->foreign('batch_sheet_id')->references('id')->on('batch_sheets')->onDelete('cascade');
            $table->string('status');
            $table->string('remarks');
            $table->integer('updated_by');
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
        Schema::dropIfExists('batch_sheet_histories');
    }
}
