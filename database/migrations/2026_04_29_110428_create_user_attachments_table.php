<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::create('user_attachments', function (Blueprint $table) {
            $table->bigincrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('label', 100);          // e.g. "Offer Letter"
            $table->string('original_name', 255);  // original filename user uploaded
            $table->string('file_path', 255);      // stored filename (original + random suffix)
            $table->boolean('show_in_app')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_attachments');
    }
}