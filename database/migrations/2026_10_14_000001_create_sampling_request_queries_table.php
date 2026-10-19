<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * The back-and-forth between the employee who raised a sample request and the
 * admin deciding on it — the same shape as user_expenses_queries.
 *
 * Read state is tracked per side rather than per message pair: `admin_read` is
 * set when the admin opens the thread, `user_read` when the app does, so each
 * side can show its own unread count against the request.
 *
 * Messages are only accepted while the request is pending or approved. Once it
 * is closed or declined the thread stays readable as history but is shut.
 */
class CreateSamplingRequestQueriesTable extends Migration
{
    public function up()
    {
        Schema::create('sampling_request_queries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sampling_request_id');
            $table->unsignedBigInteger('sender_id')->nullable()->comment('users.id — null if the account was removed');
            $table->enum('sender_type', ['admin', 'employee']);
            $table->text('message');
            $table->boolean('admin_read')->default(0)->comment('1 once the admin has opened the thread');
            $table->boolean('user_read')->default(0)->comment('1 once the app has opened the thread');
            $table->timestamps();

            $table->index('sampling_request_id');
            $table->index(['sampling_request_id', 'sender_type', 'admin_read'], 'sampling_queries_admin_unread');
            $table->index(['sampling_request_id', 'sender_type', 'user_read'], 'sampling_queries_user_unread');

            $table->foreign('sampling_request_id')
                  ->references('id')->on('sampling_requests')
                  ->onDelete('cascade');

            $table->foreign('sender_id')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sampling_request_queries');
    }
}
