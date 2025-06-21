<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    public function cities(){
    	return $this->hasMany('App\CustomerCity');
    }

    public function discounts(){
    	return $this->hasMany('App\CustomerDiscount');
    }

    public function employees(){
    	return $this->hasMany('App\CustomerEmployee')->where('is_delete',0);
    }

    public function corporate_discount(){
        return $this->hasOne('App\CustomerDiscount')->where('discount_type','Corporate');
    }

    public function product_discounts(){
        return $this->hasMany('App\CustomerDiscount')->where('discount_type','Product Base');
    } 

    public function user_customer_shares(){
        return $this->hasMany('App\UserCustomerShare')->with('user');
    }

    public function dealer(){
        return $this->belongsto('App\Dealer','dealer_id','id')->with(['contact_persons','linked_products']);
    }

    public function link_dealer(){
        return $this->belongsto('App\Dealer','dealer_id','id');
    }
}
