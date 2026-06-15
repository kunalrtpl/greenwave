<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealerEvaluationsTable extends Migration
{
    public function up()
    {
        // ── 1. New columns on dealers ─────────────────────────────────────────
        Schema::table('dealers', function (Blueprint $table) {
            $table->string('source_of_lead', 100)->nullable()->after('email');
            $table->unsignedInteger('created_by')->nullable()->after('source_of_lead');
        });

        // ── 2. One row per form submission ────────────────────────────────────
        Schema::create('dealer_evaluations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('dealer_id');
            $table->unsignedInteger('submitted_by');
            $table->timestamps();

            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
        });

        // ── 3. Fully dynamic answers ──────────────────────────────────────────
        // Every question = one row. No hardcoded columns for any question.
        //
        // section_key       : 'B' | 'C' | 'D' | 'E'
        // section_name      : 'Business Profile' etc  (human label)
        // question_key      : unique slug  e.g. 'years_in_business'
        // question_text     : full question label as shown in the form
        // question_type     : 'radio' | 'checkbox' | 'text'
        // available_options : JSON array of all options  →  [] for text questions
        // selected_options  : JSON array of what user picked → [] for text questions
        // custom_answer     : free text answer for type=text, null for radio/checkbox
        //
        Schema::create('dealer_evaluation_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('evaluation_id');
            $table->string('section_key', 5);
            $table->string('section_name', 100);
            $table->string('question_key', 100);
            $table->text('question_text');
            $table->string('question_type', 20);
            $table->json('available_options');
            $table->json('selected_options');
            $table->text('custom_answer')->nullable();
            $table->timestamps();

            $table->foreign('evaluation_id')
                  ->references('id')->on('dealer_evaluations')
                  ->onDelete('cascade');
        });

        // ── 4. Attachments ────────────────────────────────────────────────────
        Schema::create('dealer_evaluation_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('evaluation_id');
            $table->string('file', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->timestamps();

            $table->foreign('evaluation_id')
                  ->references('id')->on('dealer_evaluations')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dealer_evaluation_attachments');
        Schema::dropIfExists('dealer_evaluation_answers');
        Schema::dropIfExists('dealer_evaluations');

        Schema::table('dealers', function (Blueprint $table) {
            $table->dropColumn(['source_of_lead', 'created_by']);
        });
    }
}