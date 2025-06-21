<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QtyDiscount extends Model
{
    //
    public function product(){
    	return $this->belongsto('App\Product','product_id','id')->select('id','product_name');
    }

    public static function get_discounts($proid){
    	$discounts = QtyDiscount::join('products','products.id','=','qty_discounts.product_id')->select('qty_discounts.*','products.product_name')->where('qty_discounts.product_id',$proid)->get();
    	$discounts = json_decode(json_encode($discounts),true);
    	return $discounts;
    }
}
