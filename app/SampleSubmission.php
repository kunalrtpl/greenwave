<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleSubmission extends Model
{
    //
    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    public function complaint_info(){
        return $this->belongsto('App\Feedback','complaint_id','id')->with(['customer','customer_employee','product','replies']);
    }

    public function product(){
        return $this->belongsto('App\Product','product_id','id');
    }

    public function dealer(){
        return $this->belongsto('App\Dealer','dealer_id','id');
    }

    public function user(){
        return $this->belongsto('App\User','user_id','id');
    }
}
