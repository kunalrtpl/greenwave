<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLostSaleReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lost_sale_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('report_date');
            $table->date('wef_date');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->string('monthly_requirement');
            $table->text('reason');
            $table->string('replaced_by_product_name');
            $table->string('replaced_by_company_name');
            $table->string('replaced_by_dealer_name');
            $table->decimal('replaced_by_price',8,3);
            $table->string('replaced_by_application');
            $table->string('replaced_by_dosage_type');
            $table->float('replaced_by_dosage_percent');
            $table->float('replaced_by_cost_percent');
            $table->float('replaced_by_dosage_gpl');
            $table->striing('replaced_by_mlr');
            $table->float('replaced_by_cost_gpl');
            $table->float('replaced_by_pick_up');
            $table->float('replaced_by_trough_loss');
            $table->float('replaced_by_lot_size');
            $table->float('replaced_by_dosage_pm');
            $table->float('replaced_by_cost_pm');
            $table->decimal('price',8,3);
            $table->string('application');
            $table->string('dosage_type');
            $table->float('dosage_percent');
            $table->float('cost_percent');
            $table->float('dosage_gpl');
            $table->string('mlr');
            $table->float('cost_gpl');
            $table->float('pick_up');
            $table->float('trough_loss');
            $table->float('lot_size');
            $table->float('dosage_pm');
            $table->float('cost_pm');
            $table->string('creation_type');
            $table->integer('created_by')->index();
            $table->text('remarks');
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
        Schema::dropIfExists('lost_sale_reports');
    }
}
