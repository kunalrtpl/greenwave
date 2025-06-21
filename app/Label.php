<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class Label extends Model
{
    //
    public static function labels(){
    	$labels = DB::table('labels')->where('status',1)->get();
    	$labels = json_decode(json_encode($labels),true);
    	return $labels;
    }
}
