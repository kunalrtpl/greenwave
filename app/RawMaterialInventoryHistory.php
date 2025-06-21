<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\RawMaterialInventoryHistory;
use App\RawMaterialInventoryChecklist;
use App\PackingType;
use App\RawMaterial;
use DB;
class RawMaterialInventoryHistory extends Model
{
    //
    public function updateby(){
        return $this->belongsTo('App\User','updated_by','id')->select('id','name');
    }

    public static function createHistory($data){
    	$createRmInvHis = new RawMaterialInventoryHistory;
    	$createRmInvHis->raw_material_inventory_id = $data['raw_material_inventory_id'];
    	$createRmInvHis->status = $data['status'];
    	if(isset($data['packing_size_id'])){
    		$createRmInvHis->packing_size_id = $data['packing_size_id'];
    	}
        if(isset($data['packing_type_id'])){
            $createRmInvHis->packing_type_id = $data['packing_type_id'];
        }
    	$createRmInvHis->remarks = $data['remarks'];
        $createRmInvHis->updated_by = Auth::user()->id;
    	$createRmInvHis->save();
        if(isset($data['packing_type_id'])){
            DB::table('raw_material_inventories')->where('id',$data['raw_material_inventory_id'])->update(['status'=>$data['status'],'packing_type_id'=>$data['packing_type_id'],'no_of_samples'=>$data['no_of_samples']]);
            PackingType::where('id',$data['packing_type_id'])->decrement('stock',$data['no_of_samples']);
            //Reduce Packing Size stock to 1
            /*DB::table('packing_sizes')->where('id',$data['packing_size_id'])->decrement('current_stock');*/
        }else{
            DB::table('raw_material_inventories')->where('id',$data['raw_material_inventory_id'])->update(['status'=>$data['status']]);
        }
        //Update Raw Materail Current Stock
        if(isset($data['rm_details']) && $data['status'] == 'QC Approved'){
            RawMaterial::where('id',$data['rm_details']['raw_material_id'])->increment('current_stock',$data['rm_details']['stock']);
            /*$lastRMI = RawMaterialInventory::orderby('id','DESC')->select('batch_no')->first();
            if(isset($lastRMI->serial_no)){
                $explodeBNO = explode('BN-RM-', $lastRMI->batch_no);
                $batch_no = $explodeBNO[1] + 1;
                $batch_no = 'BN-RM-'.$batch_no;
            }else{
                $batch_no = 'BN-RM-1000';
            }
            DB::table('raw_material_inventories')->where('id',$data['raw_material_inventory_id'])->update(['batch_no'=>$batch_no]);*/
        }
        //Create QC
        if(isset($data['request_data']['checklist_ids']) && !empty($data['request_data']['checklist_ids'])){
            foreach($data['request_data']['checklist_ids'] as $rkey => $checklistid){
                $rmInvChecklist = new RawMaterialInventoryChecklist;
                    $rmInvChecklist->sample_no = 1;
                    $rmInvChecklist->raw_material_id= $data['request_data']['rm_ids'][$rkey];
                    $rmInvChecklist->raw_material_inventory_id= $data['request_data']['rminv_ids'][$rkey];
                    $rmInvChecklist->checklist_id = $checklistid;
                    $rmInvChecklist->raw_material_range = $data['request_data']['raw_material_ranges'][$rkey];
                    $rmInvChecklist->range = $data['request_data']['ranges'][$rkey];
                    $rmInvChecklist->remarks = $data['request_data']['qc_remarks'][$rkey];
                    $rmInvChecklist->save();
            }
        }
        /*if(isset($data['request_data']['samples']) && !empty($data['request_data']['samples'])){
            foreach($data['request_data']['samples'] as $samplekey=> $sampleInfo){
                foreach($data['request_data']['ranges'][$sampleInfo] as $rkey => $range){
                    $rmInvChecklist = new RawMaterialInventoryChecklist;
                    $rmInvChecklist->sample_no = $sampleInfo;
                    $rmInvChecklist->raw_material_id= $data['request_data']['rm_ids'][$sampleInfo][$rkey];
                    $rmInvChecklist->raw_material_inventory_id= $data['request_data']['rminv_ids'][$sampleInfo][$rkey];
                    $rmInvChecklist->checklist_id = $data['request_data']['checklist_ids'][$sampleInfo][$rkey];
                    $rmInvChecklist->raw_material_range = $data['request_data']['raw_material_ranges'][$sampleInfo][$rkey];
                    $rmInvChecklist->range = $range;
                    $rmInvChecklist->remarks = $data['request_data']['qc_remarks'][$sampleInfo][$rkey];
                    $rmInvChecklist->save();
                }
            }
        }*/
    }
}
