<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDvrProduct extends Model
{
    protected $table = 'user_dvr_products';
    protected $guarded = [];

    public function productinfo(){
        return $this->belongsTo('App\Product','product_id')->select('id','product_name','product_code','hsn_code','keywords');
    }
    
    public function dvr()
    {
        return $this->belongsTo(UserDvr::class);
    }
}
