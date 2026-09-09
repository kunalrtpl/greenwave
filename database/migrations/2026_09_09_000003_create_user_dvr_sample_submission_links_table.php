<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDvrSampleSubmissionLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_dvr_sample_submission_links', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_dvr_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('sample_submission_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('user_dvr_id')
                  ->references('id')
                  ->on('user_dvrs')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('sample_submission_id')
                  ->references('id')
                  ->on('sample_submissions')
                  ->onDelete('cascade');

            $table->unique(
                ['user_dvr_id', 'sample_submission_id'],
                'user_dvr_sample_submission_links_unique'
            );
        });

        // The old single-value column stays in place for historical rows, but is
        // no longer written to / read from — the links table above replaces it.
        // (Raw statement because doctrine/dbal is not installed, so ->change()
        // is unavailable.)
        DB::statement(
            "ALTER TABLE `user_dvrs` MODIFY `sample_submission_id` BIGINT UNSIGNED NULL"
            . " COMMENT 'Deprecated - not used anymore, see user_dvr_sample_submission_links'"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "ALTER TABLE `user_dvrs` MODIFY `sample_submission_id` BIGINT UNSIGNED NULL COMMENT ''"
        );

        Schema::dropIfExists('user_dvr_sample_submission_links');
    }
}
