<?php

namespace Tests\Feature\Fakes;

use App\Contracts\ProductBatchSource;
use App\Support\ProductBatch;

/**
 * A second batch source that exists only in this test, standing in for the
 * books batches will come from later (production, offline batches, …).
 *
 * It is here to prove the point of the contract: a new source needs no change
 * to the dispatch screen, the form request or the dispatch service.
 */
class MadeUpBatchSource implements ProductBatchSource
{
    private $productId;

    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    public function key()
    {
        return 'madeup';
    }

    public function label()
    {
        return 'Made Up';
    }

    public function batchesFor(array $productIds)
    {
        if (!in_array($this->productId, $productIds)) {
            return [];
        }

        return [$this->productId => [$this->batch()]];
    }

    public function find(array $ids)
    {
        return in_array(1, $ids) ? [1 => $this->batch()] : [];
    }

    public function lock(array $ids)
    {
        return $this->find($ids);
    }

    public function consume($model, $qty)
    {
        // Nothing to write: this source only exists for the length of a test.
    }

    private function batch()
    {
        return new ProductBatch($this, (object) ['id' => 1], [
            'id'            => 1,
            'product_id'    => $this->productId,
            'batch_no'      => 'MADEUP-1',
            'remaining_qty' => 99,
        ]);
    }
}
