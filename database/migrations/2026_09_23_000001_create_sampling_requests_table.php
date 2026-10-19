<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Sample requests — one row per requested PRODUCT.
 *
 * A single API post that carries four products creates four rows sharing the
 * same request_group_no, referenced SR-1.1 … SR-1.4. Each row then moves
 * through its own lifecycle (pending → approved → closed / declined), so an
 * admin can approve two products of a request and decline the other two.
 *
 * The request-level context (purpose, competitor block, dispatch-to) is
 * repeated on every row on purpose: the client asked for a single flat table
 * so every screen and export reads one row without a join.
 */
class CreateSamplingRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('sampling_requests', function (Blueprint $table) {
            $table->bigIncrements('id');

            /* ── Reference ───────────────────────────────────────────── */
            $table->unsignedInteger('request_group_no')->comment('The "1" in SR-1.2 — one running counter, never reset');
            $table->unsignedInteger('line_no')->comment('The "2" in SR-1.2 — product position inside the request');
            $table->string('reference_no', 50)->comment('SR-1.2');
            $table->string('financial_year', 20)->comment('Recorded for reporting only; it is not part of the reference');

            /* ── Who raised it ───────────────────────────────────────── */
            $table->string('raised_by', 20)->default('user')->comment('user | dealer — dealer requests are planned, not live yet');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('dealer_id')->nullable()->comment('Reserved: set when a dealer raises the request');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('Customer the sample is meant for');

            $table->string('sample_type', 20)->default('free');
            $table->boolean('is_voluntarily')->default(0)->comment('1 when the admin raised it at dispatch time with no request from the field');
            $table->string('status', 20)->default('pending')->comment('pending | approved | closed | declined');
            $table->date('request_date');

            /* ── Product: what was asked for ─────────────────────────
             | These four hold the request exactly as the app sent it and are
             | never edited afterwards, so the detail screen can always show
             | "asked for X" next to "approved as Y".
             */
            $table->unsignedBigInteger('requested_product_id')->nullable()->comment('Product the field user asked for — never overwritten');
            $table->string('pack_size_info', 100)->nullable();
            $table->decimal('pack_size', 10, 3)->nullable()->comment('Requested pack size in kg');
            $table->unsignedInteger('no_of_packs')->nullable()->comment('Requested no of packs');
            $table->decimal('requested_qty', 12, 3)->default(0)->comment('pack_size x no_of_packs as requested');
            $table->text('item_remarks')->nullable()->comment('Per-product remarks sent with the request');

            /* ── Product: what was approved ──────────────────────────
             | The admin may swap the product and change the packing. Qty is
             | always approved_pack_size x approved_no_of_packs — it is never
             | typed in directly, which is how the old screen worked too.
             */
            $table->unsignedBigInteger('product_id')->nullable()->comment('Product finally approved — admin may swap it');
            $table->unsignedBigInteger('product_detail_id')->nullable();
            $table->decimal('approved_pack_size', 10, 3)->nullable();
            $table->unsignedInteger('approved_no_of_packs')->nullable();
            $table->decimal('approved_qty', 12, 3)->nullable()->comment('Derived: approved_pack_size x approved_no_of_packs');
            $table->decimal('dealer_price', 12, 2)->nullable()->comment('Dealer price snapshot at approval');
            $table->decimal('approved_value', 14, 2)->nullable()->comment('Derived: approved_qty x dealer_price');
            $table->decimal('total_dispatched_qty', 12, 3)->default(0);

            /* ── Request context (same on every line of a request) ───── */
            $table->text('purpose_reason_for_request')->nullable();
            $table->text('application_plan')->nullable();
            $table->decimal('gst', 5, 2)->nullable();
            $table->boolean('is_specific_customer')->default(0);
            $table->boolean('product_known')->default(0);
            $table->boolean('target_competitor')->default(0);
            $table->string('competitor_category', 255)->nullable();
            $table->string('competitor_product_name', 255)->nullable();
            $table->string('competitor_make', 255)->nullable();
            $table->decimal('competitor_dealer_price', 12, 2)->nullable();
            $table->decimal('competitor_customer_price', 12, 2)->nullable();
            $table->string('competitor_dosage', 100)->nullable();
            $table->decimal('competitor_monthly_requirement_kg', 12, 3)->nullable();
            $table->text('competitor_specialty')->nullable();
            $table->text('competitor_application_details')->nullable();
            $table->text('competitor_expectation')->nullable();

            /* ── Where it should go (defaults for the dispatch screen) ─ */
            $table->string('dispatch_to', 255)->nullable();
            $table->unsignedBigInteger('dispatch_dealer_id')->nullable();
            $table->unsignedBigInteger('dispatch_customer_id')->nullable();
            $table->unsignedBigInteger('dispatch_user_id')->nullable()->comment('Falls back to the requesting user when no dealer/customer is given');
            $table->text('dispatch_address')->nullable();

            /* ── Approve / decline ───────────────────────────────────── */
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->text('approval_remarks')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->unsignedBigInteger('declined_by_user_id')->nullable();
            $table->text('decline_reason')->nullable();

            /* ── Ready for dispatch ──────────────────────────────────── */
            $table->boolean('is_ready')->default(0);
            $table->timestamp('ready_at')->nullable();
            $table->unsignedBigInteger('ready_by_user_id')->nullable();
            $table->text('ready_remarks')->nullable();

            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_ready']);
            // Makes a race between two simultaneous requests fail loudly
            // instead of silently handing out the same SR-n.m twice.
            $table->unique(['request_group_no', 'line_no'], 'sampling_requests_reference_unique');
            $table->index('reference_no');
            $table->index('financial_year');
            $table->index('user_id');
            $table->index('product_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('requested_product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('dispatch_dealer_id')->references('id')->on('dealers')->onDelete('set null');
            $table->foreign('dispatch_customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('dispatch_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sampling_requests');
    }
}
