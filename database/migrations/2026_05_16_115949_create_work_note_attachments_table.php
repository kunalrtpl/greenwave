<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkNoteAttachmentsTable extends Migration
{
    public function up()
    {
        Schema::create('work_note_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('work_note_id');

            /**
             * type:
             *   'voice_note'  – audio recording attached to the note
             *   'attachment'  – any other file (image, pdf, doc, etc.)
             *
             * App developers use this column to distinguish voice notes from
             * regular attachments when rendering the list.
             */
            $table->enum('type', ['voice_note', 'attachment'])->default('attachment');

            $table->string('file', 255);            // stored filename in public/work_notes/
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('duration_seconds')->default(0); // for voice notes; 0 for others
            $table->timestamps();

            $table->foreign('work_note_id')->references('id')->on('work_notes')->onDelete('cascade');
            $table->index(['work_note_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_note_attachments');
    }
}