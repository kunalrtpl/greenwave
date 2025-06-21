<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketProductInfo extends Model
{
    //
    public function customer(){
    	return $this->belongsto('App\Customer','customer_id','id');
    }

    public function dealer(){
        return $this->belongsto('App\Customer','dealer_id','id');
    }

    public function product_category(){
    	return $this->belongsto('App\ProductDetail','product_category_id','id');
    }
}
