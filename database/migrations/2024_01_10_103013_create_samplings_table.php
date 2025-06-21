<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('samplings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->string('action');
            $table->string('required_through');
            $table->string('sample_type');
            $table->integer('sample_ref_no');
            $table->string('sample_ref_no_string');
            $table->decimal('subtotal');
            $table->decimal('gst');
            $table->decimal('gst_per');
            $table->decimal('grand_total');
            $table->text('remarks');
            $table->string('sample_edited')->default('no');
            $table->string('edited_by');
            $table->string('order_placed_by');
            $table->integer('edited_by_id');
            $table->date('sampling_date');
            $table->string('sample_status');
            $table->string('financial_year');
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
        Schema::dropIfExists('samplings');
    }
}
