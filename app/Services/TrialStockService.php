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
 * The batch is what decides whether stock moves. A line that names one has
 * its qty taken out of it; a line that names none is recorded against the
 * trial and moves nothing, which is how a product the employee is out of
 * stock of still gets reported. Those lines are written with own_stock = 0 and
 * no batch_ref, so restore() knows not to give back a qty that never left.
 *
 * No batch, no movement — but a batch that is named has to hold the qty. That
 * line is refused and the whole request with it, so nothing is half saved.
 * Every refusal is written to the log with the trial, employee, product, qty
 * and reason: the request itself leaves no row behind, so the log is the only
 * record that the app tried. Logging is outside the transaction that gets
 * rolled back, which is exactly why it belongs there. A line that recorded a
 * qty without a batch is logged too, since no stock moved for it either.
 * Grep the daily log for "[trial-stock]".
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
     * A line with no batch_ref is stored as it came, qty and all, and skipped
     * for every stock check: nothing is taken out, so there is nothing to
     * check it against.
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
            $qty       = round((float) (isset($product['qty']) ? $product['qty'] : 0), 3);
            $batchRef  = isset($product['batch_ref']) ? trim((string) $product['batch_ref']) : '';
            $key       = 'products.' . $index . '.batch_ref';

            if ($qty < 0) {
                $qty = 0;
            }

            $context = [
                'line'       => $index,
                'trial_id'   => $trialId,
                'user_id'    => $userId,
                'product_id' => $productId,
                'qty'        => $qty,
                'batch_ref'  => $batchRef !== '' ? $batchRef : null,
            ];

            // No batch named: the product is part of the trial but none of the
            // employee's stock went into it — typically because they had none
            // left. The row records that, with own_stock = 0 so deleting the
            // trial gives nothing back for it.
            if ($batchRef === '') {
                $rows[] = UserDvrProduct::create([
                    'user_dvr_id' => null,
                    'trial_id'    => $trialId,
                    'product_id'  => $productId,
                    'batch_ref'   => null,
                    'batch_no'    => null,
                    'qty'         => $qty,
                    'own_stock'   => 0,
                ]);

                if ($qty > 0) {
                    // A qty was reported without a batch to take it out of, so
                    // it is on the trial but not out of their stock. Worth
                    // finding later, hence a warning rather than a note.
                    Log::warning(
                        self::LOG_TAG . ' recorded ' . $this->trim($qty)
                            . ' with no batch named, no stock moved',
                        $context
                    );
                } else {
                    Log::info(
                        self::LOG_TAG . ' recorded with no qty and no batch, no stock moved',
                        $context
                    );
                }

                continue;
            }

            if ($qty <= 0) {
                // A batch was named, so this line is asking for stock to move,
                // and nothing can come out of a batch at a qty of zero.
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
     * Lines that moved no stock in the first place — no batch_ref and
     * own_stock = 0 — are left alone, so nothing is credited back that never
     * left the employee's stock.
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
