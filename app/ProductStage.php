<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductStage extends Model
{
    //
    public static function checkprostage($proid,$stage){
    	$details = ProductStage::where('product_id',$proid)->where('stage',$stage)->first();
    	$details = json_decode(json_encode($details),true);
    	return $details; 
    }

    public static function getCurretStage($proid){
    	$details = ProductStage::where('product_id',$proid)->orderby('id','DESC')->first();
    	$details = json_decode(json_encode($details),true);
    	if(!empty($details)){
    		if($details['stage'] =="Sample Trial Stage"){
    			return 'Bulk Trial Stage';
    		}elseif($details['stage'] =="Bulk Trial Stage"){
    			return 'Listed Product';
    		}elseif($details['stage'] =="Listed Product"){
    			return 'Listed Product';
    		}elseif($details['stage'] =="Discontinued Product"){
    			return 'Discontinued Product';
    		}elseif($details['stage'] =="Re-Introduced Product"){
    			return 'Re-Introduced Product';
    		}
    	}else{
    		return 'Sample Trial Stage';
    	}	
    }
}
