<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrialReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trial_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedbigInteger('customer_register_request_id')->index()->nullable();
            $table->foreign('customer_register_request_id')->references('id')->on('customer_register_requests')->onDelete('cascade');
            $table->unsignedbigInteger('dvr_id')->index()->nullable();
            $table->foreign('dvr_id')->references('id')->on('dvrs')->onDelete('cascade');
            $table->unsignedbigInteger('feedback_id')->index()->nullable();
            $table->foreign('feedback_id')->references('id')->on('feedback')->onDelete('cascade');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('trial_type')->nullable();
            $table->string('trial_objective')->nullable();
            $table->string('is_jointly')->nullable();
            $table->unsignedbigInteger('other_team_member_id')->index()->nullable();
            $table->foreign('other_team_member_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('other_team_member_name')->nullable();
            $table->string('substrate_count')->nullable();
            $table->string('lot_no')->nullable();
            $table->decimal('lot_size',8,3)->nullable();
            $table->string('shade')->nullable();
            $table->string('process_type')->nullable();
            $table->string('machine_type')->nullable();
            $table->string('machine_no')->nullable();
            $table->string('machine_make')->nullable();
            $table->decimal('fabric_pick_up',8,3)->nullable();
            $table->decimal('trough_loss',8,3)->nullable();
            $table->decimal('solution_required_in_trough',8,3)->nullable();
            $table->string('operator_name')->nullable();
            $table->longtext('initial_precautions')->nullable();
            $table->tinyInteger('pdf_generated')->default(0);
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
        Schema::dropIfExists('trial_reports');
    }
}
