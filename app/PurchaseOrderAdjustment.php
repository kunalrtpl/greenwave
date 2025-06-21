<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderAdjustment extends Model
{
    //
    public function orderitem(){
    	return $this->belongsTo('App\PurchaseOrderItem','purchase_order_item_id','id')->select('id','purchase_order_id','product_id','qty','inherit_type','batch_out_duration','dealer_markup','market_price','packing_size_id')->with(['product']);
    }
}
