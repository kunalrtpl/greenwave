<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserScheduler extends Model
{
    //
    public function customer(){
        return $this->belongsto('App\Customer','customer_id','id');
    }

    public function customer_register_request(){
        return $this->belongsto('App\CustomerRegisterRequest','customer_register_request_id','id')->with(['dealer','linkedExecutive']);
    }

    public function previous_scheduler(){
        return $this->belongsto('App\UserScheduler','previous_scheduler_id','id')->with(['customer','customer_register_request']);
    }

    public function next_scheduler(){
        return $this->belongsto('App\UserScheduler','next_scheduler_id','id')->with(['customer','customer_register_request']);
    }
}
