<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerPurchaseProjection extends Model
{
    //

    public function dealer(){
        return $this->belongsTo('App\Dealer')->select('id','business_name','short_name','name','designation','department','address');
    }

    public function created_by_dealer(){
        return $this->belongsTo('App\Dealer','created_by','id')->select('id','business_name','short_name','name','designation','department','address');
    }

    public function product(){
        return $this->belongsTo('App\Product')->select('id', 'product_name', 'keywords', 'product_code', 'product_detail_info');
    }
}
