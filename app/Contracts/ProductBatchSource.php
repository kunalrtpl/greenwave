<?php

namespace App\Contracts;

/**
 * Somewhere a dispatchable batch of finished product can come from.
 *
 * Today the only source is RFD material received through Add Incoming
 * Material, but batches will also come from production and from other books.
 * Each source answers the same three questions — what have you got, give me
 * these rows locked, and take this qty out of one — so a new source is a new
 * class in App\Services\BatchSources plus one line in
 * App\Services\ProductBatchService::sources().
 */
interface ProductBatchSource
{
    /** Short key used in a batch reference, e.g. "inventory" in "inventory:45". */
    public function key();

    /** Human name of this source, shown next to the batch when more than one exists. */
    public function label();

    /**
     * Batches with stock left, keyed by product id.
     *
     * @param  array $productIds
     * @return array [productId => [App\Support\ProductBatch, ...]]
     */
    public function batchesFor(array $productIds);

    /**
     * These batches by id, without locking — for reading.
     *
     * @param  array $ids ids within this source
     * @return array [id => App\Support\ProductBatch]
     */
    public function find(array $ids);

    /**
     * The same batches, row-locked for the rest of the transaction.
     *
     * @param  array $ids ids within this source
     * @return array [id => App\Support\ProductBatch]
     */
    public function lock(array $ids);

    /**
     * Take qty out of one batch. The caller has already checked there is
     * enough and holds the lock.
     *
     * @param  mixed $model the row this source handed back
     * @param  float $qty
     */
    public function consume($model, $qty);
}
