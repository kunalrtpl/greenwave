<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerProduct extends Model
{
    //
    public function product(){
    	return $this->belongsto('App\Product','product_id');
    }

    public function dealer(){
        return $this->belongsto('App\Product','dealer_id');
    }

    public static function getStockInhand($productid,$dealerid){
    	$getdetails = DealerProduct::where('product_id',$productid)->where('dealer_id',$dealerid)->first();
    	return $getdetails;
    }
}
