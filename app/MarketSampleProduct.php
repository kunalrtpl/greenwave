<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketSampleProduct extends Model
{
    //
    public function productinfo(){
        return $this->belongsTo('App\Product','product_id')->select('id','product_name','product_code','hsn_code','keywords');
    }
}
