<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    public static function get_monthly_discounts(){
    	$start_date = Discount::orderby('start_date','DESC')->groupby('start_date')->limit(1)->pluck('start_date');
    	$discounts= array();
    	if(isset($start_date[0])){
    		$discounts = Discount::where('start_date',$start_date[0])->orderby('id','ASC')->get();
    		$discounts = json_decode(json_encode($discounts),true);
    	}
    	return $discounts;
    }

    public static function get_discounts($date){
    	$discounts = Discount::whereDate('start_date',$date)->orderby('id','ASC')->get();
    	$discounts = json_decode(json_encode($discounts),true);
    	return $discounts;
    }
}
