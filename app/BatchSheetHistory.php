<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
class BatchSheetHistory extends Model
{
    //

	public function updateby(){
        return $this->belongsTo('App\User','updated_by','id')->select('id','name');
    }

    public static function createBatchHistory($params){
    	$batchHistory = new BatchSheetHistory;
    	$batchHistory->batch_sheet_id = $params['batch_sheet_id'];
    	$batchHistory->status = $params['status'];
    	$batchHistory->remarks = $params['remarks'];
    	$batchHistory->updated_by = Auth::user()->id;
    	$batchHistory->save();
    }
}
