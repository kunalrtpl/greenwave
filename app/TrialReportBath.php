<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrialReportBath extends Model
{
    //

    public function products(){
        return $this->hasMany('App\TrialReportBathProduct','trial_report_bath_id','id')->with('productinfo');
    }
}
