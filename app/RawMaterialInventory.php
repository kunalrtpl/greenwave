<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RawMaterialInventoryHistory;
use Auth;
class RawMaterialInventory extends Model
{
    //
	public function rm_checklists(){
		return $this->hasMany('App\RawMaterialChecklist','raw_material_id','raw_material_id')->with('checklist');
	}

	public function rminv_checklists(){
		return $this->hasMany('App\RawMaterialInventoryChecklist','raw_material_inventory_id','id')->with('checklist');
	}

	public function rawmaterial(){
		return $this->belongsto('App\RawMaterial','raw_material_id','id');
	}

	public function rm_history(){
		return $this->hasMany('App\RawMaterialInventoryHistory','raw_material_inventory_id','id');
	}

	public function rm_histories(){
		return $this->hasMany('App\RawMaterialInventoryHistory','raw_material_inventory_id','id');
	}

	public function medias(){
		return $this->hasMany('App\RawMaterialInventoryMedia','raw_material_inventory_id','id');
	}

	public function addedby(){
		return $this->belongsTo('App\User','added_by','id')->select('id','name');
	}

    public static function createRMInventory($data){
    	$lastRMI = RawMaterialInventory::orderby('id','DESC')->select('serial_no','batch_no')->first();
    	if(isset($lastRMI->serial_no)){
    		$explodeSRNO = explode('RM-', $lastRMI->serial_no);
    		$srno = $explodeSRNO[1] + 1;
    		$srno = 'RM-'.$srno;
    	}else{
    		$srno = 'RM-1000';
    	}
        if(isset($lastRMI->batch_no) && !empty($lastRMI->batch_no)) {
            $explodeBNO = explode('BN-RM-', $lastRMI->batch_no);
            $batch_no = $explodeBNO[1] + 1;
            $batch_no = 'BN-RM-'.$batch_no;
        }else{
            $batch_no = 'BN-RM-1000';
        }
    	$createRmInv = new RawMaterialInventory;
		$createRmInv->incoming_date = $data['incoming_date'];
		$createRmInv->raw_material_id = $data['raw_material_id'];
		$createRmInv->serial_no    = $srno;
		$createRmInv->batch_no    = $batch_no;
		//$createRmInv->no_of_packs    = $data['no_of_packs'];
		$createRmInv->stock    = $data['stock'];
		$createRmInv->remaining_stock    = $data['stock'];
		$createRmInv->remarks  = $data['remarks'];
		$createRmInv->supplier_batch_no = $data['supplier_batch_no'];
		//$createRmInv->price    = $data['raw_material_price'];
		$createRmInv->added_by = Auth::user()->id;
		$createRmInv->status   = "Incoming Material";
		$createRmInv->save();
		$rminvid = $createRmInv->id;
		$historyData['raw_material_inventory_id'] = $rminvid;
		$historyData['status'] = 'Incoming Material';
		$historyData['remarks'] = $data['remarks'];
		RawMaterialInventoryHistory::createHistory($historyData);
		return $rminvid;
    }

    public static function approvedRms($rmid){
    	$approvedRms = RawMaterialInventory::where('raw_material_id',$rmid)->where('remaining_stock','>',0)->where('status','QC Approved')->get()->toArray();
    	return $approvedRms;
    }
}
