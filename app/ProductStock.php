<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Running stock of a finished product, in Kgs.
 *
 * Two buckets that always add up to total_stock:
 *   incoming_stock   — stock received from outside (Add Incoming Material)
 *   production_stock — stock made in house (wired up later)
 *
 * Nothing writes to this table directly: every change goes through
 * App\Services\ProductStockService so a ledger line is always written with it.
 */
class ProductStock extends Model
{
    const BUCKET_PRODUCTION = 'production';
    const BUCKET_INCOMING   = 'incoming';

    protected $table = 'product_stocks';

    protected $fillable = [
        'product_id', 'production_stock', 'incoming_stock', 'total_stock',
    ];

    protected $casts = [
        'production_stock' => 'float',
        'incoming_stock'   => 'float',
        'total_stock'      => 'float',
    ];

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id', 'id');
    }

    public function ledgers()
    {
        return $this->hasMany('App\ProductStockLedger', 'product_id', 'product_id');
    }

    /** The two buckets this table keeps, for validation and labels. */
    public static function buckets()
    {
        return [
            self::BUCKET_PRODUCTION => 'Production Stock',
            self::BUCKET_INCOMING   => 'Incoming Stock',
        ];
    }

    /** Column that holds a bucket, or null when the bucket is not one of ours. */
    public static function columnFor($bucket)
    {
        $map = [
            self::BUCKET_PRODUCTION => 'production_stock',
            self::BUCKET_INCOMING   => 'incoming_stock',
        ];

        return isset($map[$bucket]) ? $map[$bucket] : null;
    }

    /** Row for this product, created at zero the first time it is needed. */
    public static function forProduct($productId)
    {
        return static::firstOrCreate(
            ['product_id' => $productId],
            ['production_stock' => 0, 'incoming_stock' => 0, 'total_stock' => 0]
        );
    }

    /** Keeps total_stock honest — it is never set by hand. */
    public function recalculateTotal()
    {
        $this->total_stock = round($this->production_stock + $this->incoming_stock, 3);

        return $this;
    }
}
