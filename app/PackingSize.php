<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class PackingSize extends Model
{
    //
    public static function sizes(){
    	$types = DB::table('packing_sizes')->groupby('type')->select('type')->get();
    	$types = json_decode(json_encode($types),true);
    	foreach ($types as $key => $type) {
    		$getSizes = DB::table('packing_sizes')->where('type',$type['type'])->get();
    		$getSizes = json_decode(json_encode($getSizes),true);
    		$types[$key]['type'] = $type['type'];
    		$types[$key]['sizes'] = $getSizes;
    	}
    	return $types;
    }

     public static function order_sizes(){
        $sizes = DB::table('packing_sizes')->where('status',1)->get();
        $sizes = json_decode(json_encode($sizes),true);
        return $sizes;
    }
}
