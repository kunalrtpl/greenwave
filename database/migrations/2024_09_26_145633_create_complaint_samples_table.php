<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComplaintSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaint_samples', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('complaint_sample_no');
            $table->date('request_date');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedbigInteger('feedback_id')->index()->nullable();
            $table->foreign('feedback_id')->references('id')->on('feedback')->onDelete('cascade');
            $table->string('type');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('complaint_details_by_customer');
            $table->string('complaint_details_by_you');
            $table->string('sample_batch_number');
            $table->string('previous_batch_number');
            $table->string('monthly_consumption');
            $table->string('remarks');
            $table->string('admin_remarks');
            $table->string('sample_document');
            $table->string('courier_document');
            $table->string('status');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
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
        Schema::dropIfExists('complaint_samples');
    }
}
