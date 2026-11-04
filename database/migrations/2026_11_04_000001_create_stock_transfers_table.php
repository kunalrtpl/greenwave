<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Free-sample stock moving from one marketing employee to another.
 *
 * One employee offers stock they are holding; the other accepts or refuses it.
 * Nothing moves until it is accepted, but the qty leaves the sender's
 * stock_in_hand the moment the request goes out (it sits in blocked_stock
 * instead) so the same material cannot be offered to two people at once.
 *
 * Lifecycle, driven entirely by `status`:
 *
 *   pending    waiting for the receiving employee
 *   approved   accepted — the qty is now the receiver's stock in hand
 *   declined   refused, with a reason; the qty goes back to the sender
 *   cancelled  withdrawn by the sender before it was decided
 */
class CreateStockTransfersTable extends Migration
{
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('transfer_no')->comment('Plain running number, never resets');
            $table->string('reference_no', 50)->comment('ST-1');

            $table->unsignedBigInteger('from_user_id')->comment('The employee giving the stock');
            $table->unsignedBigInteger('to_user_id')->comment('The employee being offered it');

            $table->string('status', 20)->default('pending');
            $table->date('transfer_date');
            $table->text('remarks')->nullable()->comment('Why the sender is transferring it');

            /* ── Decided by the receiving employee ────────────────────── */
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();

            /* ── Withdrawn by the sender ──────────────────────────────── */
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->unique('transfer_no');
            $table->index(['to_user_id', 'status'], 'st_to_user_status_idx');
            $table->index(['from_user_id', 'status'], 'st_from_user_status_idx');

            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfers');
    }
}
