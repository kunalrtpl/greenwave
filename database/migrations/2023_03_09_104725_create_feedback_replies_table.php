<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback_replies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('feedback_id')->index()->nullable();
            $table->foreign('feedback_id')->references('id')->on('feedback')->onDelete('cascade');
            $table->string('reply');
            $table->unsignedbigInteger('created_by')->index()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('feedback_replies');
    }
}
