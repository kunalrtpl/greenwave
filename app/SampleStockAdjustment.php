<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleStockAdjustment extends Model
{
    //
    public function product(){
        return $this->belongsto('App\Product','product_id')->select('id','product_name');
    }
}
