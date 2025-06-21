<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    //
    public function product(){
    	return $this->belongsto('App\Product','product_id')->select('id','product_name');
    }

    public function dealer(){
        return $this->belongsto('App\Dealer','dealer_id');
    }
}
