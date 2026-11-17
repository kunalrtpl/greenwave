<?php

namespace App\Services;

use App\FreeSamplingStock;
use App\SampleSubmission;
use App\Support\ProductBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Samples an employee hands to a customer.
 *
 * Everything that moves stock for a submission lives here, so submitting,
 * editing and deleting can never drift apart:
 *
 *   submit  — the qty leaves the employee's stock, out of a named batch
 *   edit    — the old qty goes back first, then the new qty leaves
 *   delete  — whatever was still out comes back
 *
 * Each submission names the batch it came out of, which is what makes the
 * employee's per-batch holding (App\Services\EmployeeBatchStockService) add up
 * to their stock. The batch has to be one they are actually holding.
 */
class SampleSubmissionService
{
    /**
     * Create or update a submission and move the stock with it.
     *
     * @param  array $data  the posted payload
     * @param  int   $userId the employee submitting
     * @return SampleSubmission
     * @throws ValidationException
     */
    public function submit(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $submission = $this->findForEdit($data, $userId);
            $editing    = $submission->exists;
            $oldQty     = $editing ? (float) $submission->qty : 0;
            $oldRef     = $editing ? $submission->batch_ref : null;
            $oldProduct = $editing ? (int) $submission->product_id : null;
            $productId  = (int) $data['product_id'];
            $qty        = round((float) $data['qty'], 3);

            if ($qty <= 0) {
                throw ValidationException::withMessages(['qty' => 'Enter a qty greater than zero.']);
            }

            $batch = app(EmployeeBatchStockService::class)->requireBatch($data, $productId);

            // Editing its own qty: what this submission already took out is
            // available to it again, so an edit of 2 Kg to 3 Kg needs 1 more
            // Kg free, not 3.
            $allowance = ($editing && $oldRef === $batch->ref() && $oldProduct === $productId)
                ? $oldQty
                : 0;

            app(EmployeeBatchStockService::class)
                ->assertHolds($userId, $productId, $batch, $qty, $allowance, 'batch_ref', 'submitted');

            $this->fill($submission, $data, $userId, $batch, $qty);
            $submission->save();

            if ($editing && $oldProduct !== $productId) {
                // Moved to another product: the first one gets its qty back
                // and the new one gives up the whole of the new qty.
                $this->moveStock($userId, $oldProduct, $oldQty, $data);
                $this->moveStock($userId, $productId, -$qty, $data);
            } else {
                // Same product: only the difference moves, so an edit from
                // 2 Kg to 3 Kg takes 1 more Kg out, not 3.
                $this->moveStock($userId, $productId, round($oldQty - $qty, 3), $data);
            }

            return $submission;
        });
    }

    /**
     * Delete a submission and give back whatever of it is still out.
     *
     * @throws ValidationException
     */
    public function delete(SampleSubmission $submission)
    {
        return DB::transaction(function () use ($submission) {
            // Qty still out = what was submitted, less anything already returned.
            $returned = $submission->is_returned ? (float) $submission->return_qty : 0;
            $restore  = round((float) $submission->qty - $returned, 3);

            if ($restore != 0 && $submission->product_id) {
                $this->moveStock($submission->user_id, $submission->product_id, $restore, []);
            }

            $submission->delete();

            return $restore;
        });
    }

    /* ─────────────────────────── Internals ─────────────────────────── */

    private function findForEdit(array $data, $userId)
    {
        if (empty($data['sample_submission_id'])) {
            return new SampleSubmission();
        }

        $submission = SampleSubmission::where('id', $data['sample_submission_id'])
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        if (!$submission) {
            throw ValidationException::withMessages([
                'sample_submission_id' => 'That sample submission was not found.',
            ]);
        }

        return $submission;
    }

    private function fill(SampleSubmission $submission, array $data, $userId, ProductBatch $batch, $qty)
    {
        $submission->user_id = $userId;

        if (!empty($data['customer_id'])) {
            $submission->customer_id = $data['customer_id'];
        }
        if (!empty($data['customer_register_request_id'])) {
            $submission->customer_register_request_id = $data['customer_register_request_id'];
        }

        $submission->submission_date = $data['submission_date'];
        $submission->purpose         = isset($data['purpose']) ? $data['purpose'] : null;
        $submission->submission_type = isset($data['submission_type']) ? $data['submission_type'] : null;
        $submission->complaint_id    = isset($data['complaint_id']) ? $data['complaint_id'] : null;
        $submission->product_id      = (int) $data['product_id'];
        $submission->batch_ref       = $batch->ref();
        $submission->batch_no        = $batch->batchNo;
        $submission->qty             = $qty;
        $submission->from            = isset($data['from']) ? $data['from'] : 'Free Samples';
        $submission->sample_value    = isset($data['sample_value']) ? $data['sample_value'] : null;
        $submission->remarks         = isset($data['remarks']) ? $data['remarks'] : '';

        if (isset($data['from_dvr'])) {
            $submission->from_dvr = $data['from_dvr'];
        }
    }

    /**
     * Adds $delta to the employee's stock of a product — negative to take it
     * out. The row is opened if they have none yet, exactly as the screen this
     * replaces did.
     */
    private function moveStock($userId, $productId, $delta, array $data)
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

            if (!empty($data['customer_id'])) {
                $stock->customer_id = $data['customer_id'];
            }
            if (!empty($data['customer_register_request_id'])) {
                $stock->customer_register_request_id = $data['customer_register_request_id'];
            }
        }

        $stock->stock_in_hand = round($stock->stock_in_hand + $delta, 3);
        $stock->save();
    }

}
