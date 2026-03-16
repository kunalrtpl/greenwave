<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserExpenseQueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_expenses_queries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('expense_id');
            $table->unsignedBigInteger('sender_id');          // who sent this message
            $table->enum('sender_type', ['admin', 'employee']); // which side
            $table->text('message');
            $table->timestamps();
 
            $table->foreign('expense_id')
                  ->references('id')->on('user_expenses')
                  ->onDelete('cascade');
 
            $table->foreign('sender_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
 
            $table->index('expense_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_expense_queries');
    }
}
