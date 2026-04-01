<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHolidayListsTable extends Migration
{
    public function up()
    {
        Schema::create('holiday_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');                   // Diwali, Holi, Lohri, etc.
            $table->date('date');

            // NULL = applies to ALL cities (national)
            // Filled = city-specific (matches users.base_city)
            $table->string('city')->nullable();

            // true  = national holiday shown for everyone
            // false = city-specific (use city column)
            $table->boolean('is_national')->default(false);
            $table->boolean('is_recurring')->default(false)->after('is_national');
            $table->string('type')->nullable();       // Optional: public, restricted, optional
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['date', 'is_active']);
            $table->index(['city', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('holiday_lists');
    }
}