<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerSpecialDiscount extends Model
{
    //
    public function product(){
    	return $this->belongsto('App\Product','product_id','id')->select('id','product_name');
    }
    
    public static function getSpecialDis($productid,$dealerid){
    	$getdetails = DealerSpecialDiscount::where('product_id',$productid)->where('dealer_id',$dealerid)->first();
    	return $getdetails;
    }
}
