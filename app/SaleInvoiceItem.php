<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleInvoiceItem extends Model
{
    //
    public function purchase_order_item(){
    	return $this->belongsTo('App\PurchaseOrderItem','purchase_order_item_id');
    }

    public function productinfo(){
    	return $this->belongsTo('App\Product','product_id')->select('id','product_name','product_code','hsn_code','keywords','product_detail_info','packing_size_id')->with('productpacking');
    }
}
