<?php

namespace App\Services;

use App\FreeSamplingStock;
use App\SampleStockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * An employee writing stock off — expired, leaked, damaged.
 *
 * The qty comes off a named batch, so a write-off is as traceable as a sample
 * handed to a customer. A positive qty takes stock out of the batch and is
 * checked against what the employee is holding of it; a negative qty puts
 * stock back, which is how a mistaken write-off is undone, so nothing is
 * checked there.
 */
class SampleStockAdjustmentService
{
    /**
     * @param  array $data   the posted payload
     * @param  int   $userId the employee adjusting
     * @return SampleStockAdjustment
     * @throws ValidationException
     */
    public function adjust(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $productId = (int) $data['product_id'];
            $qty       = round((float) $data['qty'], 3);

            if ($qty == 0) {
                throw ValidationException::withMessages(['qty' => 'Enter a qty to adjust.']);
            }

            $batches = app(EmployeeBatchStockService::class);
            $batch   = $batches->requireBatch($data, $productId);

            // Only stock going out is limited by what they hold.
            if ($qty > 0) {
                $batches->assertHolds($userId, $productId, $batch, $qty, 0, 'batch_ref', 'adjusted');
            }

            $adjustment = new SampleStockAdjustment();
            $adjustment->user_id         = $userId;
            $adjustment->product_id      = $productId;
            $adjustment->batch_ref       = $batch->ref();
            $adjustment->batch_no        = $batch->batchNo;
            $adjustment->qty             = $qty;
            $adjustment->amount          = isset($data['amount']) ? $data['amount'] : 0;
            $adjustment->reason          = isset($data['reason']) ? $data['reason'] : '';
            $adjustment->remarks         = isset($data['remarks']) ? $data['remarks'] : '';
            $adjustment->other           = isset($data['other']) ? $data['other'] : '';
            $adjustment->adjustment_date = $data['adjustment_date'];
            $adjustment->save();

            // A write-off takes the qty out; a negative one puts it back.
            $this->moveStock($userId, $productId, -$qty);

            return $adjustment;
        });
    }

    /**
     * Undo one — the qty goes back to the batch it came off and the record is
     * removed. Nothing calls this yet; it is here so reverting an adjustment
     * never has to repeat the arithmetic.
     */
    public function revert(SampleStockAdjustment $adjustment)
    {
        return DB::transaction(function () use ($adjustment) {
            $this->moveStock($adjustment->user_id, $adjustment->product_id, (float) $adjustment->qty);
            $adjustment->delete();

            return (float) $adjustment->qty;
        });
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
}
