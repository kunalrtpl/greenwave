<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dvr extends Model
{
    //

	protected $casts = [
        'trial_details' => 'array'
    ];

    public function customer(){
    	return $this->belongsto('App\Customer','customer_id','id');
    } 

    public function user(){
        return $this->belongsto('App\User','user_id','id');
    }

    public function trial_report_info(){
        return $this->belongsto('App\TrialReport','trial_report_id','id')->with(['customer','customer_register_request','other_team_member_info','feedback_info','baths']);
    }

    public function customer_register_request(){
        return $this->belongsto('App\CustomerRegisterRequest','customer_register_request_id','id')->with(['dealer','linkedExecutive']);
    }

    public function complaint_info(){
        return $this->belongsto('App\Feedback','complaint_id','id')->with(['customer','customer_employee','product','replies']);
    }

    public function query_info(){
        return $this->belongsto('App\Feedback','query','id')->with(['customer','customer_employee','product','replies']);
    }

    public function other_team_member_info(){
        return $this->belongsto('App\User','other_team_member_id','id');
    }

    public function products(){
    	return $this->hasMany('App\DvrProduct','dvr_id','id')->with('productinfo');
    }

    public function trial_reports(){
        return $this->hasMany('App\DvrTrialReport','dvr_id','id')->with('trial_report_info');
    }

    public function complaint_sample(){
        return $this->belongsto('App\ComplaintSample','complaint_sample_id','id')->with('productinfo');
    }

    public function market_sample(){
        return $this->belongsto('App\MarketSample','market_sample_id','id');
    }

    public function sample_submission(){
        return $this->belongsto('App\SampleSubmission','sample_submission_id','id')->with('product');
    }

    public function user_scheduler(){
        return $this->belongsto('App\UserScheduler','user_scheduler_id','id')->with(['customer','customer_register_request','previous_scheduler','next_scheduler']);
    }

}
