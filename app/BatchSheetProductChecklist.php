<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BatchSheetProductChecklist extends Model
{
    //
    public static function createProductChecklist($data,$batchDetails){
        foreach($data['checklist_ids'] as $key => $checklistid){
            $batchprochecklist = new BatchSheetProductChecklist;
            //$batchprochecklist->sample_no = $sampleInfo;
            $batchprochecklist->batch_sheet_id = $batchDetails['id'];
            $batchprochecklist->product_id = $batchDetails['product_id'];
            $batchprochecklist->checklist_id = $checklistid;
            $batchprochecklist->product_range = $data['product_ranges'][$key];
            $batchprochecklist->range = $data['ranges'][$key];
            $batchprochecklist->remarks = $data['qc_remarks'][$key];
            $batchprochecklist->save();
        }
    	/*for($i=1; $i<= 1; $i++){
    		$sampleInfo = $i;
            foreach($data['ranges'][$sampleInfo] as $rkey => $range){
                $batchprochecklist = new BatchSheetProductChecklist;
                $batchprochecklist->sample_no = $sampleInfo;
                $batchprochecklist->batch_sheet_id = $batchDetails['id'];
                $batchprochecklist->product_id = $batchDetails['product_id'];
                $batchprochecklist->checklist_id = $data['checklist_ids'][$sampleInfo][$rkey];
                $batchprochecklist->product_range = $data['product_ranges'][$sampleInfo][$rkey];
                $batchprochecklist->range = $range;
                $batchprochecklist->remarks = $data['qc_remarks'][$sampleInfo][$rkey];
                $batchprochecklist->save();
            }
    	}*/
    }
}
