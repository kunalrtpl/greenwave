<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use App\PackingType;
use App\Label;
use App\Product;
class ProductInventory extends Model
{
    //
	public function osp_checklists(){
		return $this->hasMany('App\ProductChecklist','product_id','product_id')->with('checklist');
	}

	public function product(){
		return $this->belongsto('App\Product','product_id','id');
	}

	public function osp_history(){
		return $this->hasMany('App\ProductInventoryHistory','product_inventory_id','id');
	}

	public function osp_histories(){
		return $this->hasMany('App\ProductInventoryHistory','product_inventory_id','id');
	}

	public function addedby(){
		return $this->belongsTo('App\User','added_by','id')->select('id','name');
	}

	public function medias(){
		return $this->hasMany('App\ProductInventoryMedia','product_inventory_id','id');
	}

	public function ospinv_checklists(){
		return $this->hasMany('App\ProductInventoryChecklist','product_id','product_id')->with('checklist');
	}

    public static function createOSPInventory($data){
    	$srno ="";
    	if($data['type']=="SRM"){
    		$lastRMI = ProductInventory::where('type','SRM')->orderby('id','DESC')->select('serial_no')->first();
	    	if(isset($lastRMI->serial_no)){
	    		$explodeSRNO = explode('SRM-', $lastRMI->serial_no);
	    		$srno = $explodeSRNO[1] + 1;
	    		$srno = 'SRM-'.$srno;
	    	}else{
	    		$srno = 'SRM-1000';
	    	}
    	}
    	$createOSPinv = new ProductInventory;
    	$createOSPinv->type = $data['type'];
    	$createOSPinv->incoming_date = $data['incoming_date'];
		$createOSPinv->product_id = $data['product_id'];
		$createOSPinv->serial_no    = $srno;
		//$createOSPinv->no_of_packs    = $data['no_of_packs'];
		$createOSPinv->stock    = $data['stock'];
		$createOSPinv->remaining_stock    = $data['stock'];
		$createOSPinv->remarks  = $data['remarks'];
		$createOSPinv->supplier_batch_no = $data['supplier_batch_no'];
		//$createOSPinv->price    = $data['raw_material_price'];
		$createOSPinv->added_by = Auth::user()->id;
		$createOSPinv->status   = "Incoming Material";
		if($data['type']=="RFDM"){
			$createOSPinv->packing_type_id = $data['final_packing_type'];
            $createOSPinv->no_of_packs = $data['final_no_of_packs'];
            $createOSPinv->net_fill_size = $data['final_net_fill_size'];
            $createOSPinv->material_fill = $data['final_no_of_packs'] * $data['final_net_fill_size'];
            $createOSPinv->packs_consumed = $data['packs_consumed'];
            $createOSPinv->label_id = $data['label_id'];
            $createOSPinv->labels_consumed = $data['labels_consumed'];
            if($data['packs_consumed'] >0){
                PackingType::where('id',$data['final_packing_type'])->decrement('stock',$data['packs_consumed']);
            }
            if($data['labels_consumed'] >0){
                Label::where('id',$data['label_id'])->decrement('stock',$data['labels_consumed']);
            }
		}
		$createOSPinv->save();
		$ospinvid = $createOSPinv->id;
		if($data['type']=="SRM"){
			$historyData['product_inventory_id'] = $ospinvid;
			$historyData['status'] = 'Incoming Material';
			$historyData['remarks'] = $data['remarks'];
			ProductInventoryHistory::createHistory($historyData);
		}else{
			DB::table('products')->where('id',$data['product_id'])->increment('current_stock',$data['stock']);
		}
		return $ospinvid;
    }
}
