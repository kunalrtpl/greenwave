<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    //

    public function subcats(){
    	return $this->hasMany('App\ProductDetail','parent_id','id');
    }
}
