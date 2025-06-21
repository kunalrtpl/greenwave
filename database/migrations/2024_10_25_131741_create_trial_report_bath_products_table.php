<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrialReportBathProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trial_report_bath_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('trial_report_bath_id')->index()->nullable();
            $table->foreign('trial_report_bath_id')->references('id')->on('trial_report_baths')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('product_name')->nullable();
            $table->longtext('product_description')->nullable();
            $table->string('make')->nullable();
            $table->string('dosage_type')->nullable();
            $table->decimal('dosage',8,3)->nullable();
            $table->decimal('dosage_kg',8,3)->nullable();
            $table->decimal('gross_price',8,3)->nullable();
            $table->decimal('net_price',8,3)->nullable();
            $table->decimal('discount',8,3)->nullable();
            $table->longtext('application_details')->nullable();
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
        Schema::dropIfExists('trial_report_bath_products');
    }
}
