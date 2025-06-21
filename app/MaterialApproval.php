<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MaterialApproval extends Model
{
    //
    public function product(){
        return $this->belongsTo('App\Product','product_id','id');
    }

    public function customer(){
        return $this->belongsto('App\Customer','customer_id','id');
    }

    public function dealer(){
        return $this->belongsto('App\Dealer','created_by','id');
    }
}
