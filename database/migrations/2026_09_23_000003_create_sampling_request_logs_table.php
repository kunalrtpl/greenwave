<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Audit trail for a sample request line.
 *
 * The request columns hold the *current* value; this table holds what it was
 * before and who changed it, so "what did the field user actually ask for and
 * what did the admin change it to" is answerable even after several edits.
 *
 * `changes` is a JSON map of field => ['from' => …, 'to' => …].
 */
class CreateSamplingRequestLogsTable extends Migration
{
    public function up()
    {
        Schema::create('sampling_request_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sampling_request_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Admin who performed the action; null for API-created rows');
            $table->string('action', 30)->comment('created | updated | approved | declined | marked_ready | dispatched | closed');
            $table->text('changes')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('sampling_request_id');

            $table->foreign('sampling_request_id')->references('id')->on('sampling_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sampling_request_logs');
    }
}
