<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerPurchaseReturn extends Model
{
    //
    public function items(){
    	return $this->hasMany('App\DealerPurchaseReturnItem','dealer_purchase_return_id','id')->with('productinfo');
    }
}
