<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * The query thread between the admins and the employee who filed a DVR — the
 * same shape as user_expenses_queries and sampling_request_queries.
 *
 * Only an admin can start a thread. The employee can answer from the app once
 * an admin has written, but cannot open one on their own. Any number of admins
 * may take part; each of them is emailed when the employee answers.
 *
 * Read state is tracked per side: `admin_read` is set when an admin opens the
 * thread, `user_read` when the app does.
 */
class CreateUserDvrQueriesTable extends Migration
{
    public function up()
    {
        Schema::create('user_dvr_queries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_dvr_id');
            $table->unsignedBigInteger('sender_id')->nullable()->comment('users.id — null if the account was removed');
            $table->enum('sender_type', ['admin', 'employee']);
            $table->text('message');
            $table->boolean('admin_read')->default(0)->comment('1 once an admin has opened the thread');
            $table->boolean('user_read')->default(0)->comment('1 once the app has opened the thread');
            $table->timestamps();

            $table->index('user_dvr_id');
            $table->index(['user_dvr_id', 'sender_type', 'admin_read'], 'dvr_queries_admin_unread');
            $table->index(['user_dvr_id', 'sender_type', 'user_read'], 'dvr_queries_user_unread');

            $table->foreign('user_dvr_id')
                  ->references('id')->on('user_dvrs')
                  ->onDelete('cascade');

            $table->foreign('sender_id')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_dvr_queries');
    }
}
