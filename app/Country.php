<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    //
    public static function countries($checkStatus){
    	$countries = Country::query();
    	if($checkStatus =='yes'){
    		$countries->where('status',1)->orderby('sort','ASC'); 
    	}
    	$countries = $countries->orderBy('sort','ASC')->get()->toArray();
    	return $countries;
    }
}
