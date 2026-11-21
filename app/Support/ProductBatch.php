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

    /**
     * What this batch may be used for, by route: ['sample' => bool, ...].
     *
     * A source says so only if its book records it. Anything a source does
     * not mention is allowed, so a source with no such notion keeps behaving
     * as it always has and allows() stays safe to call anywhere.
     *
     * @var array
     */
    public $allowedFor = [];

    public function __construct(ProductBatchSource $source, $model, array $attributes)
    {
        $this->source       = $source;
        $this->model        = $model;
        $this->id           = $attributes['id'];
        $this->productId    = isset($attributes['product_id']) ? $attributes['product_id'] : null;
        $this->batchNo      = isset($attributes['batch_no']) ? $attributes['batch_no'] : null;
        $this->remainingQty = round((float) $attributes['remaining_qty'], 3);
        $this->receivedOn   = isset($attributes['received_on']) ? $attributes['received_on'] : null;
        $this->allowedFor   = isset($attributes['allowed_for']) ? $attributes['allowed_for'] : [];
    }

    /**
     * May this batch go out by this route? Unknown routes are allowed.
     *
     * @param  string $route e.g. 'sample'
     * @return bool
     */
    public function allows($route)
    {
        return !array_key_exists($route, $this->allowedFor) || (bool) $this->allowedFor[$route];
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

    /**
     * How a batch reads in a dropdown: "85 Kg (1001)".
     *
     * The qty leads because that is what the desk is choosing on — whether
     * this batch holds enough — with the batch number after it to identify
     * which one it is.
     */
    public function label()
    {
        return ProductInventory::qty($this->remainingQty) . ' Kg (' . $this->batchNo . ')';
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
