<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Every movement of product_stocks, as credit / debit lines.
 *
 * Nothing touches product_stocks without writing a line here, so the balance
 * can always be rebuilt from this table and every change says where it came
 * from, which document it belongs to and who did it.
 */
class CreateProductStockLedgersTable extends Migration
{
    public function up()
    {
        Schema::create('product_stock_ledgers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_stock_id')->nullable();

            // credit = stock came in, debit = stock went out
            $table->string('entry_type', 10);
            // which bucket moved: production | incoming
            $table->string('stock_type', 20);
            $table->decimal('qty', 15, 3)->default(0);

            // balance of that bucket, and of the total, around this line
            $table->decimal('stock_before', 15, 3)->default(0);
            $table->decimal('stock_after', 15, 3)->default(0);
            $table->decimal('total_before', 15, 3)->default(0);
            $table->decimal('total_after', 15, 3)->default(0);

            // where it came from
            $table->string('source', 50)->nullable();          // incoming_material, production, ...
            $table->string('source_label', 100)->nullable();   // human name of that screen / action
            $table->string('reference_type', 100)->nullable(); // model class of the document
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no', 100)->nullable();   // serial no of that document
            $table->string('batch_no', 100)->nullable();

            $table->text('remarks')->nullable();               // auto written sentence
            $table->text('notes')->nullable();                 // whatever the user typed on the form

            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name', 191)->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index(['reference_type', 'reference_id'], 'psl_reference_index');
            $table->index('batch_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stock_ledgers');
    }
}
