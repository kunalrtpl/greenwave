<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** One product on a stock transfer. */
class StockTransferItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['qty' => 'float'];

    public function stock_transfer(){
        return $this->belongsTo('App\StockTransfer');
    }

    public function product(){
        return $this->belongsTo('App\Product');
    }

    public function toApiArray(){
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'product_name' => optional($this->product)->product_name,
            'product_code' => optional($this->product)->product_code,
            'qty'          => (float) $this->qty,
        ];
    }
}
