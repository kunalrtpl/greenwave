<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Stock an employee has offered to another employee and cannot use meanwhile.
 *
 * Raising a transfer moves the qty out of stock_in_hand and into here, so the
 * same material cannot be offered twice over. It leaves again the moment the
 * transfer is settled: to the receiver's stock_in_hand when it is accepted,
 * back to the sender's when it is declined or cancelled.
 */
class AddBlockedStockToFreeSamplingStocksTable extends Migration
{
    public function up()
    {
        Schema::table('free_sampling_stocks', function (Blueprint $table) {
            $table->decimal('blocked_stock', 10, 3)->default(0)->after('stock_in_hand')
                ->comment('Offered on a pending stock transfer, so not usable');
        });
    }

    public function down()
    {
        Schema::table('free_sampling_stocks', function (Blueprint $table) {
            $table->dropColumn('blocked_stock');
        });
    }
}
