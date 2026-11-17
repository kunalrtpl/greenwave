<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One line per movement of product_stocks — credit when stock comes in,
 * debit when it goes out. Written only by App\Services\ProductStockService.
 */
class ProductStockLedger extends Model
{
    const ENTRY_CREDIT = 'credit';
    const ENTRY_DEBIT  = 'debit';

    protected $table = 'product_stock_ledgers';

    protected $fillable = [
        'product_id', 'product_stock_id', 'entry_type', 'stock_type', 'qty',
        'stock_before', 'stock_after', 'total_before', 'total_after',
        'source', 'source_label', 'reference_type', 'reference_id', 'reference_no',
        'batch_no', 'remarks', 'notes', 'created_by', 'created_by_name',
    ];

    protected $casts = [
        'qty'          => 'float',
        'stock_before' => 'float',
        'stock_after'  => 'float',
        'total_before' => 'float',
        'total_after'  => 'float',
    ];

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id', 'id');
    }

    public function addedby()
    {
        return $this->belongsTo('App\User', 'created_by', 'id')->select('id', 'name');
    }

    public function scopeCredits($query)
    {
        return $query->where('entry_type', self::ENTRY_CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('entry_type', self::ENTRY_DEBIT);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /** Lines belonging to one document, e.g. a ProductInventory row. */
    public function scopeForReference($query, $model)
    {
        return $query->where('reference_type', get_class($model))
                     ->where('reference_id', $model->getKey());
    }

    public function getIsCreditAttribute()
    {
        return $this->entry_type === self::ENTRY_CREDIT;
    }

    /** "+500 Kg" / "-500 Kg", ready to print. */
    public function getSignedQtyAttribute()
    {
        return ($this->is_credit ? '+' : '-') . self::formatQty($this->qty) . ' Kg';
    }

    public static function formatQty($qty)
    {
        return rtrim(rtrim(number_format((float) $qty, 3, '.', ''), '0'), '.');
    }
}
