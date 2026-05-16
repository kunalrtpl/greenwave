<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkNotesTable extends Migration
{
    public function up()
    {
        Schema::create('work_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->date('request_date');
            $table->string('type', 100);                    // e.g. achievement, task, meeting, other
            $table->string('type_other', 255)->nullable();  // only filled when type = 'other'
            $table->string('title', 255);
            $table->longText('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'request_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_notes');
    }
}