<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('user_attendances', function (Blueprint $table) {
            $table->bigIncrements('id'); // Use bigIncrements for consistency
            
            // This MUST be unsignedBigInteger to match the users table id
            $table->unsignedBigInteger('user_id');

            // ─── IN DATA ─────────────────────────────────────────────────
            $table->date('in_date');
            $table->time('in_time')->nullable();
            $table->decimal('in_latitude', 10, 7)->nullable();
            $table->decimal('in_longitude', 10, 7)->nullable();
            $table->string('in_latitude_longitude_address')->nullable();
            $table->string('in_place_of_attendance')->nullable();
            $table->text('in_other')->nullable();
            
            // If customers/dealers tables also use bigIncrements, 
            // these should be changed to unsignedBigInteger too.
            $table->unsignedInteger('in_customer_id')->nullable();
            $table->unsignedInteger('in_customer_register_request_id')->nullable();
            $table->unsignedInteger('in_dealer_id')->nullable();

            // ─── OUT DATA ─────────────────────────────────────────────────
            $table->date('out_date')->nullable();
            $table->time('out_time')->nullable();
            $table->decimal('out_latitude', 10, 7)->nullable();
            $table->decimal('out_longitude', 10, 7)->nullable();
            $table->string('out_latitude_longitude_address')->nullable();
            $table->string('out_place_of_attendance')->nullable();
            $table->text('out_other')->nullable();
            $table->unsignedInteger('out_customer_id')->nullable();
            $table->unsignedInteger('out_customer_register_request_id')->nullable();
            $table->unsignedInteger('out_dealer_id')->nullable();
            $table->boolean('missed')->default(false);

            // ─── STATUS ───────────────────────────────────────────────────
            $table->string('status')->nullable();

            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Index for fast lookup by user + date
            $table->index(['user_id', 'in_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_attendances');
    }
}