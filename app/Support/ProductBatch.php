<?php

namespace App\Support;

use App\Contracts\ProductBatchSource;
use App\ProductInventory;

/**
 * One batch, as the rest of the app sees it — whichever book it came out of.
 *
 * A batch is addressed by its reference, "<source>:<id>" (e.g. inventory:45),
 * so a screen or an API only ever passes one value around and a new source
 * needs no new field anywhere.
 */
class ProductBatch
{
    /** @var ProductBatchSource */
    public $source;

    /** @var mixed the row the source handed back */
    public $model;

    public $id;
    public $productId;
    public $batchNo;
    public $remainingQty;
    public $receivedOn;

    public function __construct(ProductBatchSource $source, $model, array $attributes)
    {
        $this->source       = $source;
        $this->model        = $model;
        $this->id           = $attributes['id'];
        $this->productId    = isset($attributes['product_id']) ? $attributes['product_id'] : null;
        $this->batchNo      = isset($attributes['batch_no']) ? $attributes['batch_no'] : null;
        $this->remainingQty = round((float) $attributes['remaining_qty'], 3);
        $this->receivedOn   = isset($attributes['received_on']) ? $attributes['received_on'] : null;
    }

    /** "inventory:45" */
    public function ref()
    {
        return $this->source->key() . ':' . $this->id;
    }

    /** Takes qty out of this batch in whichever book it belongs to. */
    public function consume($qty)
    {
        $this->source->consume($this->model, $qty);
        $this->remainingQty = round($this->remainingQty - $qty, 3);
    }

    public function label()
    {
        return $this->batchNo . ' · ' . ProductInventory::qty($this->remainingQty) . ' Kg left';
    }

    /** One option for a batch dropdown. */
    public function toOption()
    {
        return [
            'ref'           => $this->ref(),
            'batch_no'      => $this->batchNo,
            'product_id'    => $this->productId,
            'remaining_qty' => $this->remainingQty,
            'received_on'   => $this->receivedOn,
            'source'        => $this->source->key(),
            'source_label'  => $this->source->label(),
            'label'         => $this->label(),
        ];
    }
}
