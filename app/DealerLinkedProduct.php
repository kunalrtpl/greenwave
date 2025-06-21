<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerLinkedProduct extends Model
{
    //
    public function product(){
    	return $this->belongsto('App\Product')->with('pricings');
    }
}
