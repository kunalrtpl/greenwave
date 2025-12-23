<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDvrTrial extends Model
{
    protected $table = 'user_dvr_trials';
    protected $guarded = [];

    public function dvr()
    {
        return $this->belongsTo(UserDvr::class, 'user_dvr_id');
    }

    public function attachments()
    {
        return $this->hasMany(UserDvrAttachment::class);
    }

    public function complaint_info(){
        return $this->belongsto('App\Feedback','complaint_id','id')->with(['customer','customer_employee','product','replies']);
    }

    public function other_team_member_info(){
        return $this->belongsto('App\User','other_team_member_id','id');
    }
}
