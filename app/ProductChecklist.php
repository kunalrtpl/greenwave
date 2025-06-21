<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class ProductChecklist extends Model
{
    //
    public function checklist(){
		return $this->belongsTo('App\Checklist');
	}
	
    public static function getprochecklist($proid,$checklistid){
    	$details = DB::table('product_checklists')->where('product_id',$proid)->where('checklist_id',$checklistid)->first();
    	$details = json_decode(json_encode($details),true);
    	return $details;
    }
}
