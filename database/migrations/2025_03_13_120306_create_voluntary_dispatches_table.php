<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoluntaryDispatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voluntary_dispatches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date_of_entry')->nullable();
            $table->string('dispatch_to')->nullable();
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('dispatch_basis')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('challan_no')->nullable();
            $table->date('dispatch_date')->nullable();
            $table->string('sent_through')->nullable();
            $table->string('gr_no')->nullable();
            $table->string('pod_no')->nullable();
            $table->date('sent_date')->nullable();
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
        Schema::dropIfExists('voluntary_dispatches');
    }
}
