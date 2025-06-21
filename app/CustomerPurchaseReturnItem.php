<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerPurchaseReturnItem extends Model
{
    //
    public function product(){
    	return $this->belongsto('App\Product','product_id')->select('id','product_name','product_detail_info');
    }
}
