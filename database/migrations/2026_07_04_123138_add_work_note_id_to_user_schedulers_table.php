<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkNoteIdToUserSchedulersTable extends Migration
{
    public function up()
    {
        Schema::table('user_schedulers', function (Blueprint $table) {
            $table->unsignedBigInteger('work_note_id')->nullable()->after('user_dvr_id');
            $table->foreign('work_note_id')->references('id')->on('work_notes')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('user_schedulers', function (Blueprint $table) {
            $table->dropForeign(['work_note_id']);
            $table->dropColumn('work_note_id');
        });
    }
}