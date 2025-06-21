<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserIncentive extends Model
{
    //
    public static function get_user_months($userid){
    	$user_months = UserIncentive::where('user_id',$userid)->orderby('start_date','DESC')->groupby('start_date')->pluck('start_date')->toArray();
    	//echo "<pre>"; print_r($user_months); die;
    	return $user_months;
    }

    public static function get_incentives($date,$userid){
    	$incentive = UserIncentive::whereDate('start_date',$date)->where('user_id',$userid)->orderby('id','ASC')->get();
    	$incentive = json_decode(json_encode($incentive),true);
    	return $incentive;
    }
}
