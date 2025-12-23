<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDvr extends Model
{
    protected $table = 'user_dvrs';
    protected $guarded = [];

    public function trials()
    {
        return $this->hasMany(UserDvrTrial::class)->with(['complaint_info','other_team_member_info']);
    }

    public function attachments()
    {
        return $this->hasMany(UserDvrAttachment::class);
    }

    public function products()
    {
        return $this->hasMany(UserDvrProduct::class)->with('productinfo');
    }

    public function customerContacts()
    {
        return $this->hasMany(UserDvrCustomerContact::class, 'user_dvr_id')->with('customerContact');
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

    public function customer_contact_info(){
        return $this->belongsto('App\CustomerContact','customer_contact_id','id')->select('id','name','designation','mobile_number');
    }
}
