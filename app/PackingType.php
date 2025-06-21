<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class PackingType extends Model
{
    //
    public static function packing_types($filter=null){
    	if(isset($filter) && $filter =="lab_sample"){
    		$types = DB::table('packing_types')->where('status',1)->where('lab_sample',1)->get();
    	}else{
    		$types = DB::table('packing_types')->where('status',1)->get();
    	}
    	$types = json_decode(json_encode($types),true);
    	return $types;
    }
}
