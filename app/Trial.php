<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trial extends Model
{
    protected $table = 'trials';
    protected $guarded = [];

    public function dvrs()
    {
        return $this->belongsToMany(
            UserDvr::class,
            'user_dvr_trial_links',
            'trial_id',
            'user_dvr_id'
        );
    }

    public function products()
    {
        return $this->hasMany(UserDvrProduct::class, 'trial_id')
            ->with('productinfo');
    }

    public function attachments()
    {
        return $this->hasMany(UserDvrAttachment::class, 'trial_id');
    }

    public function complaint_info()
    {
        return $this->belongsTo(
            'App\Feedback',
            'complaint_id',
            'id'
        )->with(['customer','customer_employee','product','replies']);
    }

    public function other_team_member_info()
    {
        return $this->belongsTo('App\User', 'other_team_member_id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
