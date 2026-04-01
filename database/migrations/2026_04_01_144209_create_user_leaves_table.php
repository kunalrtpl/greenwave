<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLeavesTable extends Migration
{
    public function up()
    {
        Schema::create('user_leaves', function (Blueprint $table) {
            // Using bigIncrements to match the modern Laravel standard
            $table->bigIncrements('id');

            // 1. Match 'users' table (usually bigIncrements/BigInt)
            $table->unsignedBigInteger('user_id');

            // 2. Match 'leave_types' table (Check if this is increments vs bigIncrements)
            // If this fails, change it to unsignedBigInteger
            $table->unsignedInteger('leave_type_id'); 

            // 3. Match 'user_attendances' table (usually bigIncrements/BigInt)
            $table->unsignedBigInteger('attendance_id')->nullable();

            $table->date('date');
            $table->enum('leave_duration', ['full_day', 'half_day'])->default('full_day');
            $table->enum('half_day_type', ['first_half', 'second_half'])->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('approved');
            $table->decimal('quota_deducted', 3, 1)->default(1.0);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->foreign('attendance_id')->references('id')->on('user_attendances')->onDelete('set null');

            $table->index(['user_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_leaves');
    }
}