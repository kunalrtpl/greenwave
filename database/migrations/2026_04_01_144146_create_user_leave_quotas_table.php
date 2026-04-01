<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLeaveQuotasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_leave_quotas', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Match users table (Default bigIncrements)
            $table->unsignedBigInteger('user_id');

            /**
             * If leave_types uses $table->increments('id'), use unsignedInteger.
             * If leave_types uses $table->bigIncrements('id'), use unsignedBigInteger.
             * Given the error, try unsignedInteger first if the previous BigInt failed.
             */
            $table->unsignedInteger('leave_type_id'); 

            $table->string('financial_year', 10);
            $table->decimal('total_quota', 5, 1)->default(0);
            $table->decimal('used_quota', 5, 1)->default(0);
            $table->timestamps();

            // Constraints
            $table->unique(['user_id', 'leave_type_id', 'financial_year'], 'uq_user_leave_fy');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('leave_type_id')
                  ->references('id')
                  ->on('leave_types')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user_leave_quotas');
    }
}