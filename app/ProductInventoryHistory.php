<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use App\ProductInventory;
use App\ProductInventoryChecklist;
class ProductInventoryHistory extends Model
{
    //
    public function updateby(){
        return $this->belongsTo('App\User','updated_by','id')->select('id','name');
    }

    public static function createHistory($data){
    	//echo "<pre>"; print_r($data); die;
    	$createOSPInvHis = new ProductInventoryHistory;
    	$createOSPInvHis->product_inventory_id = $data['product_inventory_id'];
    	$createOSPInvHis->status = $data['status'];
    	/*if(isset($data['packing_size_id'])){
    		$createOSPInvHis->packing_size_id = $data['packing_size_id'];
    	}else if(isset($data['packing_info']['change_packing_size_id']) && $data['packing_info']['change_required'] =="yes"){
    		$createOSPInvHis->packing_size_id = $data['packing_info']['change_packing_size_id'];
    	}*/
        if(isset($data['packing_type_id'])){
            $createOSPInvHis->packing_type_id = $data['packing_type_id'];
        }
    	$createOSPInvHis->remarks = $data['remarks'];
        $createOSPInvHis->updated_by = Auth::user()->id;
    	$createOSPInvHis->save();
        if(isset($data['packing_type_id'])){
            DB::table('product_inventories')->where('id',$data['product_inventory_id'])->update(['status'=>$data['status'],'packing_type_id'=>$data['packing_type_id'],'no_of_samples'=>$data['no_of_samples']]);
            //Reduce Packing Size stock to 1
            DB::table('packing_types')->where('id',$data['packing_type_id'])->decrement('stock',$data['no_of_samples']);
        }else{
            DB::table('product_inventories')->where('id',$data['product_inventory_id'])->update(['status'=>$data['status']]);
        }
    	/*if($data['status'] =="Packing & Labelling"){
    		if($data['packing_info']['change_required'] =="yes"){
    			DB::table('product_inventories')->where('id',$data['product_inventory_id'])->update(['status'=>$data['status'],'change_packing_size_id'=>$data['packing_info']['change_packing_size_id'],'material_fill'=>$data['packing_info']['material_fill'],'change_required'=>$data['packing_info']['change_required']]);
	    		DB::table('packing_sizes')->where('id',$data['packing_info']['change_packing_size_id'])->decrement('current_stock',$data['packing_info']['no_of_packs_required']);
    		}else{
    			DB::table('product_inventories')->where('id',$data['product_inventory_id'])->update(['status'=>$data['status'],'change_required'=>$data['packing_info']['change_required']]);
    		}
    	}else{
    		if(isset($data['packing_size_id'])){
	            DB::table('product_inventories')->where('id',$data['product_inventory_id'])->update(['status'=>$data['status'],'packing_size_id'=>$data['packing_size_id'],'no_of_samples'=>$data['no_of_samples']]);
	            //Reduce Packing Size stock to 1
	            DB::table('packing_sizes')->where('id',$data['packing_size_id'])->decrement('current_stock');
	        }else{
	            DB::table('product_inventories')->where('id',$data['product_inventory_id'])->update(['status'=>$data['status']]);
	        }
    	}*/
        //Update Raw Materail Current Stock
        if(isset($data['osp_details']) && $data['status'] == 'QC Approved'){
            Product::where('id',$data['osp_details']['product_id'])->increment('current_stock',$data['osp_details']['stock']);
            $lastRMI = ProductInventory::orderby('id','DESC')->select('batch_no')->first();
            if(isset($lastRMI->serial_no)){
                $explodeBNO = explode('BN-OSP-', $lastRMI->batch_no);
                $batch_no = $explodeBNO[1] + 1;
                $batch_no = 'BN-OSP-'.$batch_no;
            }else{
                $batch_no = 'BN-OSP-1000';
            }
            DB::table('product_inventories')->where('id',$data['product_inventory_id'])->update(['batch_no'=>$batch_no]);
        }
        //Create QC
        if(isset($data['request_data']['samples']) && !empty($data['request_data']['samples'])){
            foreach($data['request_data']['samples'] as $samplekey=> $sampleInfo){
                foreach($data['request_data']['ranges'][$sampleInfo] as $rkey => $range){
                    $ospInvChecklist = new ProductInventoryChecklist;
                    $ospInvChecklist->sample_no = $sampleInfo;
                    $ospInvChecklist->product_id= $data['request_data']['osp_ids'][$sampleInfo][$rkey];
                    $ospInvChecklist->product_inventory_id = $data['request_data']['ospinv_ids'][$sampleInfo][$rkey];
                    $ospInvChecklist->checklist_id = $data['request_data']['checklist_ids'][$sampleInfo][$rkey];
                    $ospInvChecklist->product_range = $data['request_data']['product_ranges'][$sampleInfo][$rkey];
                    $ospInvChecklist->range = $range;
                    $ospInvChecklist->remarks = $data['request_data']['qc_remarks'][$sampleInfo][$rkey];
                    $ospInvChecklist->save();
                }
            }
        }
    }
}
