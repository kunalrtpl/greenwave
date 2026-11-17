<?php

use App\ProductStock;
use App\Services\ProductStockService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Opening balance for product_stocks.
 *
 * RFD batches already in product_inventories were received before this table
 * existed, so their remaining stock is missing from it. Sample dispatch is
 * about to start subtracting from both, which only works if they agree today.
 *
 * For every product holding RFD stock this brings incoming_stock up to the sum
 * of its batches' remaining_stock, writing the difference as an ordinary
 * ledger line so the opening balance is as traceable as everything after it.
 */
class BackfillProductStocksFromRfdBatches extends Migration
{
    public function up()
    {
        $batches = DB::table('product_inventories')
            ->where('type', 'RFDM')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->select('product_id', DB::raw('SUM(remaining_stock) as qty'))
            ->get();

        foreach ($batches as $row) {
            $target  = round((float) $row->qty, 3);
            $current = round((float) ProductStock::forProduct($row->product_id)->incoming_stock, 3);
            $diff    = round($target - $current, 3);

            if ($diff == 0) {
                continue;
            }

            $options = [
                'bucket'  => ProductStock::BUCKET_INCOMING,
                'source'  => ProductStockService::SOURCE_OPENING,
                'remarks' => 'Opening balance · ' . abs($diff) . ' Kg ' . ($diff > 0 ? 'added to' : 'removed from')
                             . ' Incoming Stock to match the RFD batches already in inventory · by System',
            ];

            $diff > 0
                ? ProductStockService::credit($row->product_id, $diff, $options)
                : ProductStockService::debit($row->product_id, abs($diff), $options + ['allow_negative' => true]);
        }
    }

    public function down()
    {
        DB::table('product_stock_ledgers')->where('source', ProductStockService::SOURCE_OPENING)->delete();
    }
}
