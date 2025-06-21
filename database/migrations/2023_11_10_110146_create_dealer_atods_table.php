<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealerAtodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_atods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('financial_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->float('range_from');
            $table->float('range_to');
            $table->float('discount');
            $table->string('created_by');
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
        Schema::dropIfExists('dealer_atods');
    }
}
