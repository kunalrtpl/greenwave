<?php

namespace App\Services;

use App\FreeSamplingStock;
use App\StockTransfer;
use App\Support\ProductBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * What an employee is holding, batch by batch.
 *
 * free_sampling_stocks says how much of a product an employee has, but not
 * which batches it came out of. That is worked out here from the movements
 * that do name a batch:
 *
 *   + material dispatched to them and confirmed received
 *   + stock transfers from another employee that they accepted
 *   − stock transfers they sent that were accepted
 *   − stock transfers they have raised and are still waiting on (blocked)
 *   − samples they submitted to a customer, less anything returned
 *   − stock they wrote off against a batch (expired, leaked, damaged)
 *   − product they used up running a trial
 *
 * The totals are then held to free_sampling_stocks, which stays the one
 * authority on how much an employee has: if a product has gone out through a
 * path that does not name a batch yet, the difference is reported as
 * unbatched_qty rather than quietly disagreeing with the headline number.
 */
class EmployeeBatchStockService
{
    /**
     * @param  int   $userId
     * @param  array $productIds limit to these products, or all of theirs
     * @return array [productId => ['batches' => [...], 'batched_qty' => float, 'unbatched_qty' => float]]
     */
    public function holdings($userId, array $productIds = [])
    {
        $moves = $this->movements($userId, $productIds);

        if (!$moves) {
            return [];
        }

        $batches = app(ProductBatchService::class)->find(array_keys($moves['refs']));
        $held    = $this->availableQty($userId, $productIds);
        $out     = [];

        foreach ($moves['byProduct'] as $productId => $rows) {
            // Oldest batch first: what came in first is what goes out first.
            uasort($rows, function ($a, $b) {
                return $a['seq'] - $b['seq'];
            });

            $available = isset($held[$productId]) ? $held[$productId] : 0;
            $lines     = [];
            $total     = 0;

            foreach ($rows as $ref => $row) {
                $qty = round($row['qty'], 3);

                if ($qty <= 0) {
                    continue;
                }

                // Never offer more of a batch than the employee has left in
                // total — the oldest batches are filled first.
                $qty = min($qty, round($available - $total, 3));

                if ($qty <= 0) {
                    continue;
                }

                $batch = isset($batches[$ref]) ? $batches[$ref] : null;

                $lines[] = [
                    'batch_ref'     => $ref,
                    'batch_no'      => $row['batch_no'],
                    'available_qty' => $qty,
                    'received_on'   => $batch ? $batch->receivedOn : null,
                    'source'        => $batch ? $batch->source->key() : head(explode(':', $ref)),
                    'source_label'  => $batch ? $batch->source->label() : null,
                    'label'         => $row['batch_no'] . ' · ' . $this->trim($qty) . ' Kg',
                ];

                $total = round($total + $qty, 3);
            }

            $out[$productId] = [
                'batches'       => $lines,
                'batched_qty'   => $total,
                // Stock they hold that no batch can be put against — material
                // that moved before batches were recorded, or out through a
                // path that does not name one yet.
                'unbatched_qty' => max(0, round($available - $total, 3)),
            ];
        }

        return $out;
    }

    /** Holdings for one product, batches only. */
    public function batchesFor($userId, $productId)
    {
        $all = $this->holdings($userId, [$productId]);

        return isset($all[$productId]) ? $all[$productId]['batches'] : [];
    }

    /** What the employee has left in one batch of one product. */
    public function qtyInBatch($userId, $productId, $batchRef)
    {
        foreach ($this->batchesFor($userId, $productId) as $batch) {
            if ($batch['batch_ref'] === $batchRef) {
                return $batch['available_qty'];
            }
        }

        return 0;
    }

    /* ──────────────────── Checks shared by the screens ──────────────────── */

    /**
     * The batch a posted line names, locked and checked against the product.
     *
     * Mandatory everywhere stock leaves an employee, so that every movement
     * can be traced to the material it came from.
     *
     * @param  array  $data     the posted payload, or one line of it
     * @param  int    $productId
     * @param  string $key      which field of $data holds the reference
     * @param  string $errorKey which field to blame in the error, when that is
     *                          not $key — a line of a list is read as
     *                          batch_ref but blamed as products.0.batch_ref
     * @return \App\Support\ProductBatch
     * @throws ValidationException
     */
    public function requireBatch(array $data, $productId, $key = 'batch_ref', $errorKey = null)
    {
        $ref      = isset($data[$key]) ? trim($data[$key]) : null;
        $errorKey = $errorKey ?: $key;

        if (!$ref) {
            throw ValidationException::withMessages([$errorKey => 'Select the batch this stock is from.']);
        }

        $batches = app(ProductBatchService::class)->lock([$ref]);

        if (!isset($batches[$ref])) {
            throw ValidationException::withMessages([$errorKey => 'That batch no longer exists.']);
        }

        $batch = $batches[$ref];

        if ((int) $batch->productId !== (int) $productId) {
            throw ValidationException::withMessages([
                $errorKey => 'Batch ' . $batch->batchNo . ' belongs to a different product.',
            ]);
        }

        return $batch;
    }

    /**
     * An employee may not give out more of a batch than they are holding.
     *
     * $allowance is qty this very record already took out and is therefore its
     * own to use again, which is what makes editing one work.
     *
     * Stands aside only when some of their stock cannot be put against a batch
     * — material that moved before batches were recorded. An empty batch list
     * with nothing unbatched is not the same thing: it means they have used up
     * everything they held, and the line must still be refused.
     *
     * @throws ValidationException
     */
    public function assertHolds($userId, $productId, ProductBatch $batch, $qty, $allowance = 0, $key = 'batch_ref', $verb = 'given out')
    {
        $holdings = $this->holdings($userId, [$productId]);
        $held     = isset($holdings[$productId]) ? $holdings[$productId] : null;

        if (!$held || $held['unbatched_qty'] > 0) {
            return;
        }

        $available = $allowance;
        foreach ($held['batches'] as $row) {
            if ($row['batch_ref'] === $batch->ref()) {
                $available = round($row['available_qty'] + $allowance, 3);
                break;
            }
        }

        if ($qty > $available + 0.0001) {
            throw ValidationException::withMessages([
                $key => 'You are holding ' . $this->trim($available) . ' Kg of batch '
                    . $batch->batchNo . ' — ' . $this->trim($qty) . ' Kg cannot be ' . $verb . ' from it.',
            ]);
        }
    }

    /* ─────────────────────────── Internals ─────────────────────────── */

    /** Every batch-named movement of this employee's stock, netted per batch. */
    private function movements($userId, array $productIds)
    {
        $byProduct = [];
        $refs      = [];
        $seq       = 0;

        $add = function ($productId, $ref, $batchNo, $qty) use (&$byProduct, &$refs, &$seq) {
            if (!$ref || !$productId) {
                return;
            }

            $refs[$ref] = true;

            if (!isset($byProduct[$productId][$ref])) {
                $byProduct[$productId][$ref] = ['qty' => 0, 'batch_no' => $batchNo, 'seq' => $seq++];
            }

            $byProduct[$productId][$ref]['qty'] += $qty;

            if (!$byProduct[$productId][$ref]['batch_no'] && $batchNo) {
                $byProduct[$productId][$ref]['batch_no'] = $batchNo;
            }
        };

        foreach ($this->receivedDispatches($userId, $productIds) as $row) {
            $add($row->product_id, $row->batch_ref, $row->batch_no, (float) $row->qty);
        }

        foreach ($this->transferItems($userId, $productIds) as $row) {
            $add($row->product_id, $row->batch_ref, $row->batch_no, (float) $row->qty * $row->direction);
        }

        foreach ($this->sampleSubmissions($userId, $productIds) as $row) {
            $add($row->product_id, $row->batch_ref, $row->batch_no, -1 * (float) $row->qty);
        }

        foreach ($this->stockAdjustments($userId, $productIds) as $row) {
            // A write-off is a positive qty going out; a negative one is stock
            // coming back, so the sign is simply reversed.
            $add($row->product_id, $row->batch_ref, $row->batch_no, -1 * (float) $row->qty);
        }

        foreach ($this->trialProducts($userId, $productIds) as $row) {
            $add($row->product_id, $row->batch_ref, $row->batch_no, -1 * (float) $row->qty);
        }

        return $byProduct ? ['byProduct' => $byProduct, 'refs' => $refs] : [];
    }

    /** Material dispatched to them that they have confirmed receiving. */
    private function receivedDispatches($userId, array $productIds)
    {
        $query = DB::table('sampling_dispatches as d')
            ->leftJoin('sampling_requests as r', 'r.id', '=', 'd.sampling_request_id')
            // The owner of dispatched stock is the employee the request
            // belongs to, exactly as SamplingDispatchService credits it.
            ->whereRaw('COALESCE(r.user_id, d.dispatch_user_id) = ?', [$userId])
            ->where('d.is_received', 1)
            ->whereNotNull('d.batch_ref')
            ->groupBy('d.product_id', 'd.batch_ref', 'd.batch_no')
            ->orderBy('d.batch_ref')
            ->select('d.product_id', 'd.batch_ref', 'd.batch_no', DB::raw('SUM(d.dispatch_qty) as qty'));

        if ($productIds) {
            $query->whereIn('d.product_id', $productIds);
        }

        return $query->get();
    }

    /**
     * Transfers in and out. An accepted transfer has moved; one still waiting
     * counts against the sender already, because its qty is blocked.
     */
    private function transferItems($userId, array $productIds)
    {
        $query = DB::table('stock_transfer_items as i')
            ->join('stock_transfers as t', 't.id', '=', 'i.stock_transfer_id')
            ->whereNotNull('i.batch_ref')
            ->where(function ($q) use ($userId) {
                $q->where(function ($in) use ($userId) {
                    $in->where('t.to_user_id', $userId)->where('t.status', StockTransfer::STATUS_APPROVED);
                })->orWhere(function ($out) use ($userId) {
                    $out->where('t.from_user_id', $userId)
                        ->whereIn('t.status', [StockTransfer::STATUS_APPROVED, StockTransfer::STATUS_PENDING]);
                });
            })
            ->groupBy('i.product_id', 'i.batch_ref', 'i.batch_no', 't.to_user_id')
            ->select(
                'i.product_id',
                'i.batch_ref',
                'i.batch_no',
                DB::raw('SUM(i.qty) as qty'),
                DB::raw('CASE WHEN t.to_user_id = ' . (int) $userId . ' THEN 1 ELSE -1 END as direction')
            );

        if ($productIds) {
            $query->whereIn('i.product_id', $productIds);
        }

        return $query->get();
    }

    /**
     * Samples they handed to a customer. What came back is netted off, so a
     * fully returned submission takes nothing out of the batch.
     */
    private function sampleSubmissions($userId, array $productIds)
    {
        $query = DB::table('sample_submissions as s')
            ->where('s.user_id', $userId)
            ->whereNotNull('s.batch_ref')
            ->groupBy('s.product_id', 's.batch_ref', 's.batch_no')
            ->select(
                's.product_id',
                's.batch_ref',
                's.batch_no',
                DB::raw('SUM(s.qty - CASE WHEN s.is_returned = 1 THEN s.return_qty ELSE 0 END) as qty')
            );

        if ($productIds) {
            $query->whereIn('s.product_id', $productIds);
        }

        return $query->get();
    }

    /** Stock they wrote off against a batch. */
    private function stockAdjustments($userId, array $productIds)
    {
        $query = DB::table('sample_stock_adjustments as a')
            ->where('a.user_id', $userId)
            ->whereNotNull('a.batch_ref')
            ->groupBy('a.product_id', 'a.batch_ref', 'a.batch_no')
            ->select('a.product_id', 'a.batch_ref', 'a.batch_no', DB::raw('SUM(a.qty) as qty'));

        if ($productIds) {
            $query->whereIn('a.product_id', $productIds);
        }

        return $query->get();
    }

    /**
     * Product they used up running a trial.
     *
     * The line belongs to a trial rather than to a user, so the owner comes
     * from the trial. Rows written before batches were recorded have no
     * batch_ref and are left out, which is what surfaces them as unbatched_qty
     * instead of being silently attributed to a batch.
     */
    private function trialProducts($userId, array $productIds)
    {
        $query = DB::table('user_dvr_products as p')
            ->join('trials as t', 't.id', '=', 'p.trial_id')
            ->where('t.user_id', $userId)
            ->whereNotNull('p.batch_ref')
            ->groupBy('p.product_id', 'p.batch_ref', 'p.batch_no')
            ->select('p.product_id', 'p.batch_ref', 'p.batch_no', DB::raw('SUM(p.qty) as qty'));

        if ($productIds) {
            $query->whereIn('p.product_id', $productIds);
        }

        return $query->get();
    }

    /** The authority on how much they hold: free_sampling_stocks. */
    private function availableQty($userId, array $productIds)
    {
        $query = FreeSamplingStock::where('user_id', $userId);

        if ($productIds) {
            $query->whereIn('product_id', $productIds);
        }

        return $query->get(['product_id', 'stock_in_hand'])
            ->pluck('stock_in_hand', 'product_id')
            ->map(function ($qty) {
                return round((float) $qty, 3);
            })
            ->toArray();
    }

    private function trim($value)
    {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    }
}
