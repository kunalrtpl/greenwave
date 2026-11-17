<?php

namespace Tests\Concerns;

use App\ProductInventory;

/**
 * Material can only be dispatched out of a batch that exists and has stock, so
 * most dispatch tests need one. product_inventories is an old table with a
 * long list of NOT NULL columns and no defaults, which is why this is a helper
 * rather than a factory call at each site.
 */
trait MakesRfdBatches
{
    /** An RFD batch of this product with qty available to dispatch. */
    protected function rfdBatch($productId, $qty = 1000, $batchNo = null)
    {
        $batch = new ProductInventory();

        $batch->type                 = ProductInventory::TYPE_RFD;
        $batch->product_id           = $productId;
        $batch->incoming_date        = date('Y-m-d');
        $batch->serial_no            = '';
        $batch->batch_no             = '';
        $batch->supplier_batch_no    = $batchNo ?: ('TB-' . uniqid());
        $batch->no_of_packs          = 0;
        $batch->no_of_samples        = 0;
        $batch->no_of_packs_required = 0;
        $batch->stock                = $qty;
        $batch->remaining_stock      = $qty;
        $batch->price                = 0;
        $batch->material_fill        = 0;
        $batch->net_fill_size        = 0;
        $batch->change_required      = '';
        $batch->coa                  = '';
        $batch->qc_report            = '';
        $batch->remarks              = 'Test batch';
        $batch->status               = 'Incoming Material';
        $batch->save();

        return $batch;
    }

    /** How a batch is addressed on the dispatch form: "<source>:<id>". */
    protected function batchRef(ProductInventory $batch)
    {
        return 'inventory:' . $batch->id;
    }

    /** A batch for this product plus its reference, in one go. */
    protected function batchRefFor($productId, $qty = 1000)
    {
        return $this->batchRef($this->rfdBatch($productId, $qty));
    }
}
