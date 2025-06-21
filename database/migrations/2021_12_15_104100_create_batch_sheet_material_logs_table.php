<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchSheetMaterialLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batch_sheet_material_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->biginteger('batch_sheet_material_id')->index();
            $table->biginteger('raw_material_inventory_id')->index();
            $table->integer('issue_qty');
            $table->biginteger('created_by');
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
        Schema::dropIfExists('batch_sheet_material_logs');
    }
}
