<?php

namespace App\Services\BatchSources;

use App\Contracts\ProductBatchSource;
use App\ProductInventory;
use App\Support\ProductBatch;

/**
 * RFD batches received through Add Incoming Material — product_inventories
 * rows of type RFDM that still have remaining_stock.
 *
 * Add Incoming Material records what each batch may be used for. batchesFor()
 * offers only the ones marked for sampling, so an unticked batch never reaches
 * the Sample Dispatch dropdown. find() and lock() deliberately do NOT filter:
 * a batch already dispatched has to stay resolvable by its reference, or
 * unticking it would break the history and the stock that came out of it.
 */
class ProductInventoryBatchSource implements ProductBatchSource
{
    public function key()
    {
        return 'inventory';
    }

    public function label()
    {
        return 'Incoming Material (RFD)';
    }

    public function batchesFor(array $productIds)
    {
        if (!$productIds) {
            return [];
        }

        return ProductInventory::rfd()->withStock()
            ->whereIn('product_id', $productIds)
            // Only what the entry desk marked as usable for sampling.
            ->where('used_in_sample', 1)
            // Oldest first, so the oldest batch empties before a newer one opens.
            ->orderBy('incoming_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->groupBy('product_id')
            ->map(function ($rows) {
                return $rows->map(function ($row) {
                    return $this->wrap($row);
                })->values()->all();
            })
            ->toArray();
    }

    public function find(array $ids)
    {
        return $this->rows(ProductInventory::whereIn('id', $ids), $ids);
    }

    public function lock(array $ids)
    {
        return $this->rows(ProductInventory::whereIn('id', $ids)->lockForUpdate(), $ids);
    }

    private function rows($query, array $ids)
    {
        if (!$ids) {
            return [];
        }

        $batches = [];

        foreach ($query->get() as $row) {
            // Only RFD material is dispatchable; anything else is not a batch
            // this source owns, and is left out so it reads as "not found".
            if ($row->type === ProductInventory::TYPE_RFD) {
                $batches[$row->id] = $this->wrap($row);
            }
        }

        return $batches;
    }

    public function consume($model, $qty)
    {
        $model->remaining_stock = round((float) $model->remaining_stock - $qty, 3);
        $model->save();
    }

    private function wrap(ProductInventory $row)
    {
        return new ProductBatch($this, $row, [
            'id'            => $row->id,
            'product_id'    => $row->product_id,
            'batch_no'      => $row->supplier_batch_no,
            'remaining_qty' => $row->remaining_stock,
            'received_on'   => $row->incoming_date ? date('d M Y', strtotime($row->incoming_date)) : null,
            'allowed_for'   => [
                'sample'         => (bool) $row->used_in_sample,
                'dispatch_order' => (bool) $row->used_in_dispatch_order,
            ],
        ]);
    }
}
