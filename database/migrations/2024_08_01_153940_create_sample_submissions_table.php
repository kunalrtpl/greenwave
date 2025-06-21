<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSampleSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sample_submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedbigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedbigInteger('dealer_id')->index()->nullable();
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('cascade');
            $table->date('submission_date');
            $table->unsignedbigInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unsignedbigInteger('product_id')->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('purpose');
            $table->string('submission_type');
            $table->integer('complaint_id')->index();
            $table->decimal('qty',8,3);
            $table->string('from')->default('Free Samples');
            $table->decimal('sample_value',8,3);
            $table->string('creation_by');
            $table->string('status')->default('Pending Feedback');
            $table->string('feedback_date');
            $table->string('feedback');
            $table->string('feedback_remarks');
            $table->tinyInteger('is_close')->default(0);
            $table->text('close_reason');
            $table->tinyInteger('is_returned')->default(0);
            $table->decimal('return_qty',8,3);
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
        Schema::dropIfExists('sample_submissions');
    }
}
