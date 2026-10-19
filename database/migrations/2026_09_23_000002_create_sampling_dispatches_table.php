<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Sample dispatches — one row per (request line, dispatch) pair.
 *
 * One save on /admin/sample-dispatch/create can dispatch several request lines
 * at once. Those rows share dispatch_no / dispatch_ref_no plus the challan and
 * transport columns (invoice_no, invoice_date, transport, lr_no), while the
 * "dispatch to" columns are per row because each request can go elsewhere.
 *
 * A request line can appear here more than once: 10 kg approved, 3 kg sent
 * today and 7 kg next week is two rows, and the line stays `approved` with a
 * pending qty until the total matches.
 */
class CreateSamplingDispatchesTable extends Migration
{
    public function up()
    {
        Schema::create('sampling_dispatches', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('dispatch_no')->comment('Running number per financial year');
            $table->string('dispatch_ref_no', 50)->comment('SD-1/2026-27 — shared by every line of one dispatch');
            $table->string('financial_year', 20);
            $table->date('dispatch_date');

            $table->unsignedBigInteger('sampling_request_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('batch_no', 100)->nullable();
            $table->decimal('dispatch_qty', 12, 3);

            /* ── Challan (common across the dispatch) ────────────────── */
            $table->string('invoice_no', 100)->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('transport', 100)->nullable();
            $table->string('lr_no', 100)->nullable();
            $table->text('remarks')->nullable();

            /* ── Dispatch to (per request line) ──────────────────────── */
            $table->string('dispatch_to', 255)->nullable();
            $table->unsignedBigInteger('dispatch_dealer_id')->nullable();
            $table->unsignedBigInteger('dispatch_customer_id')->nullable();
            $table->unsignedBigInteger('dispatch_user_id')->nullable();
            $table->text('dispatch_address')->nullable();

            $table->unsignedBigInteger('dispatched_by_user_id')->nullable()->comment('Admin who saved the dispatch');
            $table->timestamps();

            $table->index(['dispatch_no', 'financial_year']);
            $table->index('dispatch_ref_no');
            $table->index('sampling_request_id');

            $table->foreign('sampling_request_id')->references('id')->on('sampling_requests')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('dispatch_dealer_id')->references('id')->on('dealers')->onDelete('set null');
            $table->foreign('dispatch_customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('dispatch_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('dispatched_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sampling_dispatches');
    }
}
