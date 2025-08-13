<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesProjection extends Model
{
    //
    protected $fillable = [
        'customer_id', 'product_id', 'projected_qty', 'action', 'month_year', 'created_by'
    ];

    public function customer(){
        return $this->belongsTo('App\Customer')->select('id', 'name', 'contact_person_name', 'mobile', 'email','dealer_id')->with('dealer');
    }

    public function product(){
        return $this->belongsTo('App\Product')->select('id', 'product_name', 'keywords', 'product_code', 'product_detail_info','packing_size_id')->with('productpacking');
    }
}
