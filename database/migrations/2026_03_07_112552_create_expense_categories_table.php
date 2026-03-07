<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpenseCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('details')->nullable(); // Stores (Metro/ Bus/ Cab etc.)
            $table->boolean('is_travel')->default(0); // Flag for Distance Travelled logic
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('expense_categories'); }
}