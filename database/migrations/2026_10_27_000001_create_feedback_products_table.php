<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackProductsTable extends Migration
{
    /**
     * Products attached to a feedback. Replaces the single feedback.product_id
     * column so one feedback can carry multiple products.
     *
     * Existing rows are copied over with: php artisan feedback:backfill-products
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback_products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('feedback_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('batch_no', 191)->nullable();

            $table->timestamps();

            $table->foreign('feedback_id')
                  ->references('id')
                  ->on('feedback')
                  ->onDelete('cascade');

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');

            // The same product may appear twice on one feedback with different batches.
            $table->unique(['feedback_id', 'product_id', 'batch_no'], 'feedback_products_unique');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback_products');
    }
}
