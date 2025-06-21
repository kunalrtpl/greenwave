<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DvrTrialReport extends Model
{
    //
    public function trial_report_info(){
        return $this->belongsto('App\TrialReport','trial_report_id','id')->with(['customer','customer_register_request','other_team_member_info','feedback_info','baths']);
    }
}
