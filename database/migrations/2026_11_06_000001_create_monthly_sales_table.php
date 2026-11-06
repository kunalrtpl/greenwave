<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Monthly sale — a dealer records what a customer bought in a given month.
 * Entered from the dealer app only. The products sit in monthly_sale_products.
 */
class CreateMonthlySalesTable extends Migration
{
    public function up()
    {
        Schema::create('monthly_sales', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            // Always the main (parent) dealer, never an add-on user.
            $table->unsignedBigInteger('dealer_id');
            $table->unsignedBigInteger('customer_id');
            $table->text('remarks')->nullable();

            // The login that added it: the main dealer itself or one of its add-on users.
            $table->unsignedBigInteger('created_by_dealer_id')->nullable();

            $table->timestamps();

            $table->index(['dealer_id', 'year', 'month'], 'ms_dealer_period_idx');
            $table->index('customer_id', 'ms_customer_idx');

            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('created_by_dealer_id')->references('id')->on('dealers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_sales');
    }
}
