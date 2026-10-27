<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Copies feedback.product_id + feedback.batch_no into the feedback_products table.
 *
 * Safe to run more than once: feedback that already has rows in
 * feedback_products (backfilled earlier, or saved through the new API) is skipped.
 */
class BackfillFeedbackProducts extends Command
{
    protected $signature = 'feedback:backfill-products {--dry-run : Show what would be inserted without writing}';

    protected $description = 'Backfill feedback_products from the old feedback.product_id and batch_no columns';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $inserted = 0;
        $alreadyLinked = 0;
        $missingProduct = 0;

        // Feedback without a product (product_id 0 / null) is skipped.
        DB::table('feedback')
            ->where('product_id', '>', 0)
            ->select('id', 'product_id', 'batch_no', 'created_at', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($dryRun, &$inserted, &$alreadyLinked, &$missingProduct) {
                $productIds = DB::table('products')
                    ->whereIn('id', $rows->pluck('product_id')->unique())
                    ->pluck('id')
                    ->flip();

                $linkedFeedbackIds = DB::table('feedback_products')
                    ->whereIn('feedback_id', $rows->pluck('id'))
                    ->distinct()
                    ->pluck('feedback_id')
                    ->flip();

                $toInsert = [];

                foreach ($rows as $row) {
                    if (isset($linkedFeedbackIds[$row->id])) {
                        $alreadyLinked++;
                        continue;
                    }

                    if (!isset($productIds[$row->product_id])) {
                        $missingProduct++;
                        $this->warn("Feedback #{$row->id}: product #{$row->product_id} does not exist, skipped");
                        continue;
                    }

                    $batchNo = trim((string) $row->batch_no);

                    // Keep the original feedback timestamps.
                    $toInsert[] = [
                        'feedback_id' => $row->id,
                        'product_id'  => $row->product_id,
                        'batch_no'    => $batchNo === '' ? null : $batchNo,
                        'created_at'  => $row->created_at,
                        'updated_at'  => $row->updated_at,
                    ];
                }

                if (!$dryRun && $toInsert) {
                    DB::table('feedback_products')->insert($toInsert);
                }

                $inserted += count($toInsert);
            });

        $this->info(($dryRun ? '[dry run] Would insert: ' : 'Inserted: ') . $inserted);
        $this->info("Already linked: {$alreadyLinked}");
        $this->info("Skipped (product not found): {$missingProduct}");

        return 0;
    }
}
