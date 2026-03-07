<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserExpensesTable extends Migration
{
    public function up()
    {
        Schema::create('user_expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->integer('category_id')->unsigned()->index();
            $table->date('expense_date');
            
            $table->decimal('requested_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->default(0.00);
            
            // Travel Specific Fields
            $table->decimal('travel_km', 10, 2)->nullable();
            $table->decimal('charge_per_km', 10, 2)->nullable();
            $table->boolean('is_intercity')->default(0); // Yes/No for Cross Cities
            $table->text('intercity_route')->nullable(); // e.g. Ludhiana > Baddi
            
            $table->text('remarks')->nullable();
            $table->string('image')->nullable();
            $table->string('status'); // requested, approved
            
            // New Approval & Verification Columns
            $table->unsignedBigInteger('verified_by')->nullable()->index();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down() 
    { 
        Schema::dropIfExists('user_expenses'); 
    }
}