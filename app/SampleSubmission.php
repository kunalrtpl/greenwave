<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleSubmission extends Model
{
    //
    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    public function customer_register_request(){
        return $this->belongsto('App\CustomerRegisterRequest','customer_register_request_id','id')->with(['dealer','linkedExecutive']);
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

    public function feedbackUser()
    {
        return $this->belongsTo('App\User', 'feedback_user_id', 'id')
                    ->select('id', 'name');
    }

    public function feedbackDealer()
    {
        return $this->belongsTo('App\Dealer', 'feedback_dealer_id', 'id')
                    ->select('id', 'business_name', 'short_name', 'name', 'designation');
    }

    public function feedbackCloseUser()
    {
        return $this->belongsTo('App\User', 'feedback_close_user_id', 'id')
                    ->select('id', 'name');
    }

    public function feedbackCloseDealer()
    {
        return $this->belongsTo('App\Dealer', 'feedback_close_dealer_id', 'id')
                    ->select('id', 'business_name', 'short_name', 'name', 'designation');
    }

}
