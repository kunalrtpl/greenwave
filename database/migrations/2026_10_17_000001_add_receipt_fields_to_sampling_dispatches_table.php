<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Receipt confirmation on a dispatched line.
 *
 * Dispatching only puts the material in transit; the employee confirms from
 * the app that it actually arrived. Until then the line sits in their
 * "to be received" list, which is what `is_received = 0` means.
 *
 * Confirming is also what turns their in-transit stock into stock in hand, the
 * same as the old sampling_sale_invoices.is_delivered flag used to do.
 */
class AddReceiptFieldsToSamplingDispatchesTable extends Migration
{
    public function up()
    {
        Schema::table('sampling_dispatches', function (Blueprint $table) {
            $table->boolean('is_received')->default(0)->after('remarks')
                ->comment('1 once the employee confirms the material arrived');
            $table->timestamp('received_at')->nullable()->after('is_received');

            // The app's pending list is "mine and not received yet".
            $table->index(['dispatch_user_id', 'is_received'], 'sd_user_received_idx');
        });
    }

    public function down()
    {
        Schema::table('sampling_dispatches', function (Blueprint $table) {
            $table->dropIndex('sd_user_received_idx');
            $table->dropColumn(['is_received', 'received_at']);
        });
    }
}
