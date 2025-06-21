<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class RawMaterialChecklist extends Model
{
    //
	public function checklist(){
		return $this->belongsTo('App\Checklist');
	}

    public static function getrmchecklist($rmid,$checklistid){
    	$details = DB::table('raw_material_checklists')->where('raw_material_id',$rmid)->where('checklist_id',$checklistid)->first();
    	$details = json_decode(json_encode($details),true);
    	return $details;
    }
}
