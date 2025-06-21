<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerIncentive extends Model
{
    //
    public static function get_incentives($date){
    	$incentive = DealerIncentive::whereDate('start_date',$date)->orderby('id','ASC')->get();
    	$incentive = json_decode(json_encode($incentive),true);
    	return $incentive;
    }
}
