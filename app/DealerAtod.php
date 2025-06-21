<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerAtod extends Model
{
    //
    public static function get_discounts($financial){
    	$discounts = DealerAtod::where('financial_year',$financial)->orderby('id','ASC')->get();
    	$discounts = json_decode(json_encode($discounts),true);
    	return $discounts;
    }
}
