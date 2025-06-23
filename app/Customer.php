<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //

    protected $appends = ['business_card_url','business_card_two_url'];

    public function getBusinessCardUrlAttribute()
    {
        if ($this->business_card) {
            return asset($this->business_card);
        }
        return null; // or default image url if you want
    }

    public function getBusinessCardTwoUrlAttribute()
    {
        if ($this->business_card_two) {
            return asset($this->business_card_two);
        }
        return null; // or default image url if you want
    }

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

    public function customer_register_request(){
        return $this->belongsto('App\CustomerRegisterRequest','customer_register_request_id','id')->with('creator');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
