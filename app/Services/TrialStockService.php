<?php

namespace App\Services;

use App\FreeSamplingStock;
use App\Trial;
use App\UserDvrProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Product an employee uses up running a trial.
 *
 * Every V3 trial line consumes the employee's own stock out of a named batch,
 * so a trial is as traceable as a sample handed to a customer or a write-off.
 * The old own_stock flag decided whether the qty moved at all; it no longer
 * does, and the column is kept only for the rows already written with it.
 *
 * Everything that moves stock for a trial lives here, so creating one and
 * deleting it can never drift apart:
 *
 *   consume — the qty leaves the employee's stock, out of a named batch
 *   restore — deleting the trial gives every line's qty back
 *
 * Each line names the batch it came out of, which is what keeps the employee's
 * per-batch holding (App\Services\EmployeeBatchStockService) adding up to their
 * stock. The batch has to be one they are actually holding.
 *
 * No batch, no movement. A line that cannot name a batch it is holding moves
 * no stock at all and the whole request is refused, so nothing is half saved.
 * Every one of those is written to the log with the trial, employee, product,
 * qty and reason: the request itself leaves no row behind, so the log is the
 * only record that the app tried. Logging is outside the transaction that gets
 * rolled back, which is exactly why it belongs there. Grep the daily log for
 * "[trial-stock]".
 */
class TrialStockService
{
    /** Everything this service writes to the log is tagged with this. */
    const LOG_TAG = '[trial-stock]';

    /**
     * Save a trial's product lines and take each qty out of its batch.
     *
     * Lines are handled one at a time, and each row is written before the next
     * line is checked, so a payload naming the same batch twice is measured
     * against what is left after the first line — not against the opening
     * holding twice over.
     *
     * @param  array $products the normalised product lines
     * @param  int   $trialId
     * @param  int   $userId   the employee running the trial
     * @return array the UserDvrProduct rows written
     * @throws ValidationException
     */
    public function consume(array $products, $trialId, $userId)
    {
        $batches = app(EmployeeBatchStockService::class);
        $rows    = [];

        foreach ($products as $index => $product) {
            $productId = (int) $product['product_id'];
            $qty       = round((float) $product['qty'], 3);
            $key       = 'products.' . $index . '.batch_ref';

            $context = [
                'line'       => $index,
                'trial_id'   => $trialId,
                'user_id'    => $userId,
                'product_id' => $productId,
                'qty'        => $qty,
                'batch_ref'  => isset($product['batch_ref']) ? $product['batch_ref'] : null,
            ];

            if ($qty <= 0) {
                $this->logRefusal('qty is not greater than zero', $context);

                throw ValidationException::withMessages([
                    'products.' . $index . '.qty' => 'Enter a qty greater than zero.',
                ]);
            }

            try {
                // Read as batch_ref on the line, blamed as products.N.batch_ref.
                $batch = $batches->requireBatch($product, $productId, 'batch_ref', $key);

                $batches->assertHolds($userId, $productId, $batch, $qty, 0, $key, 'used in a trial');
            } catch (ValidationException $e) {
                $this->logRefusal($this->reason($e), $context);

                throw $e;
            }

            $rows[] = UserDvrProduct::create([
                'user_dvr_id' => null,
                'trial_id'    => $trialId,
                'product_id'  => $productId,
                'batch_ref'   => $batch->ref(),
                'batch_no'    => $batch->batchNo,
                'qty'         => $qty,
                // Deprecated, written as 1 so anything still reading the flag
                // sees what is now always true: the line consumed their stock.
                'own_stock'   => 1,
            ]);

            $this->moveStock($userId, $productId, -$qty);

            Log::info(
                self::LOG_TAG . ' consumed ' . $this->trim($qty) . ' from batch ' . $batch->batchNo,
                $context + ['batch_ref' => $batch->ref(), 'batch_no' => $batch->batchNo]
            );
        }

        return $rows;
    }

    /**
     * Give back everything a trial took out, before the trial is deleted.
     *
     * Covers the rows written before batches were recorded as well: those are
     * the own_stock = 1 lines the old API wrote, and they are restored exactly
     * as they were — logged as such, since there is no batch to credit them to.
     *
     * @param  Trial $trial
     * @return float the qty given back in total
     */
    public function restore(Trial $trial)
    {
        return DB::transaction(function () use ($trial) {
            $lines = UserDvrProduct::where('trial_id', $trial->id)
                ->where(function ($query) {
                    $query->whereNotNull('batch_ref')->orWhere('own_stock', 1);
                })
                ->get();

            $total = 0;

            foreach ($lines as $line) {
                $qty = round((float) $line->qty, 3);

                if ($qty == 0) {
                    continue;
                }

                $this->moveStock($trial->user_id, $line->product_id, $qty);
                $total = round($total + $qty, 3);

                $context = [
                    'trial_id'   => $trial->id,
                    'user_id'    => $trial->user_id,
                    'product_id' => $line->product_id,
                    'qty'        => $qty,
                    'batch_ref'  => $line->batch_ref,
                    'batch_no'   => $line->batch_no,
                ];

                if ($line->batch_ref) {
                    Log::info(
                        self::LOG_TAG . ' restored ' . $this->trim($qty) . ' to batch ' . $line->batch_no,
                        $context
                    );
                } else {
                    // An own_stock line from before batches were recorded: the
                    // qty goes back to their stock but no batch can be
                    // credited, so it lands in unbatched_qty.
                    Log::warning(
                        self::LOG_TAG . ' restored ' . $this->trim($qty) . ' with no batch to credit',
                        $context
                    );
                }
            }

            return $total;
        });
    }

    /* ─────────────────────────── Internals ─────────────────────────── */

    /**
     * A line that moved no stock. The request is refused, so this is the only
     * record of it — it is logged before the exception is rethrown and the
     * transaction rolled back.
     */
    private function logRefusal($why, array $context)
    {
        Log::warning(self::LOG_TAG . ' no stock moved: ' . $why, $context);
    }

    /** The validation messages, flattened into one line for the log. */
    private function reason(ValidationException $e)
    {
        $messages = [];

        foreach ($e->errors() as $errors) {
            foreach ((array) $errors as $error) {
                $messages[] = $error;
            }
        }

        return $messages ? implode(' ', $messages) : 'refused';
    }

    /**
     * Adds $delta to the employee's stock of a product — negative takes it
     * out. The row is opened if they have none yet, as the screen this
     * replaces did.
     */
    private function moveStock($userId, $productId, $delta)
    {
        if (!$productId || $delta == 0) {
            return;
        }

        $stock = FreeSamplingStock::where('user_id', $userId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            $stock = new FreeSamplingStock();
            $stock->user_id                 = $userId;
            $stock->product_id              = $productId;
            $stock->stock_in_hand           = 0;
            $stock->blocked_stock           = 0;
            $stock->in_transit              = 0;
            $stock->pending_orders          = 0;
            $stock->pending_customer_orders = 0;
        }

        $stock->stock_in_hand = round($stock->stock_in_hand + $delta, 3);
        $stock->save();
    }

    private function trim($value)
    {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.') . ' Kg';
    }
}
