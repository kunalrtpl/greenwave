<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('market_samples', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('market_sample_no');
            $table->date('request_date');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedbigInteger('customer_register_request_id')->index()->nullable();
            $table->foreign('customer_register_request_id')->references('id')->on('customer_register_requests')->onDelete('cascade');
            $table->string('type');
            $table->string('product_category');
            $table->string('product_name');
            $table->string('make');
            $table->string('supplier');
            $table->string('price');
            $table->string('dosage');
            $table->string('monthly_consumption');
            $table->string('product_application');
            $table->string('purpose_of_sampling');
            $table->string('remarks');
            $table->string('admin_remarks');
            $table->string('sample_document');
            $table->string('courier_document');
            $table->string('status');
            $table->string('is_urgent');
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
        Schema::dropIfExists('market_samples');
    }
}
