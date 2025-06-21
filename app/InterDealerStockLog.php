<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InterDealerStockLog extends Model
{
    //
    public function product(){
    	return $this->belongsto('App\Product');
    }

    public function to_dealer(){
    	return $this->belongsto('App\Dealer','to_dealer_id','id');
    }

    public function from_dealer(){
    	return $this->belongsto('App\Dealer','from_dealer_id','id');
    }
}
