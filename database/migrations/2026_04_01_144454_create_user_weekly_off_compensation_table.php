<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// CHANGED TO SINGULAR: CreateUserWeeklyOffCompensationTable
class CreateUserWeeklyOffCompensationTable extends Migration
{
    /**
     * Weekly Off Carry-Forward Rules:
     * 1. Every Sunday = default Weekly Off
     * 2. If user works on Sunday -> a Comp-Off is earned
     * 3. Comp-Off is valid for the FOLLOWING week only (Mon–Sat)
     */
    public function up()
    {
        Schema::create('user_weekly_off_compensations', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Matches BigInt users.id
            $table->unsignedBigInteger('user_id');

            $table->date('worked_date');
            $table->date('valid_from');
            $table->date('expires_on');  
            $table->date('used_on')->nullable();

            $table->enum('status', ['available', 'used', 'expired'])->default('available');

            $table->timestamps();

            // Constraints
            $table->unique(['user_id', 'worked_date']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_weekly_off_compensations');
    }
}