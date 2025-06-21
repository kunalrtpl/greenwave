<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Notification;
use App\ProductRawMaterial;
use App\BatchSheetMaterial;
use App\BatchSheet;
use App\ProductInventory;
use App\BatchSheetHistory;
use App\RawMaterialInventory;
use App\ProductChecklist;
use App\BatchSheetMaterialLog;
use App\RawMaterial;
use App\BatchSheetProductChecklist;
use App\BatchSheetRequirement;
use App\Product;
use App\PackingSize;
use App\Machine;
use Illuminate\Support\Facades\View;
use Validator;
use Auth;
use DB;
use Session;
use Redirect;
use PDF;
use DateTime;
class JobCardController extends Controller
{
    //
    public function createProductionJobCard(Request $request){
        $LastBs = BatchSheet::orderby('id','DESC')->select('serial_no','batch_no')->first();
        if($LastBs && isset($LastBs->serial_no) && !empty($LastBs->serial_no)) {
            $explodeSRNO = explode('FP-', $LastBs->serial_no);
            $srno = $explodeSRNO[1] + 1;
            $srno = 'FP-'.$srno;
            $batch_no = $LastBs->batch_no + 1;
        }else{
            $srno = 'FP-1000';
            $batch_no = 1000;
        }
        if($request->ajax()){
            $data = $request->all();
            $validator = Validator::make($request->all(), [
                    'job_card_type' => 'bail|required',
                    'job_card_date' => 'bail|required',
                    'product_id' => 'bail|required|exists:products,id',
                    'batch_size' => 'bail|required|numeric',
                    'machine_number' => 'bail|required',
                    'machine_capacity' => 'bail|required',
                    'operator_name' => 'bail|required',
                ]
            );
            if($validator->passes()) {
                $totalRmQtyPer =0; $totalSrmQtyPer = 0;
                if(isset($data['rm_qty'])  || isset($data['srm_qty'])){
                    if(isset($data['rm_qty'])){
                        $totalRmQtyPer = array_sum($data['rm_qty']);
                        foreach($data['rm_qty'] as $batchid=> $rmqty_per){
                            if(!empty($rmqty_per)){
                                $rmBatch = RawMaterialInventory::find($batchid);
                                $qtyInKg = ($data['batch_size'] * $rmqty_per ) /100;
                                if($rmBatch->remaining_stock < $qtyInKg){
                                    return response()->json([
                                        'status' => false,
                                        'errors' => array('rm_errors'=> $rmBatch->supplier_batch_no. ' (Batch No.) qty can not be greater then '.$rmBatch->remaining_stock." kg")
                                    ]);
                                }
                            }
                        }
                    }
                    if(isset($data['srm_qty'])){
                        $totalSrmQtyPer = array_sum($data['srm_qty']);
                        foreach($data['srm_qty'] as $batchid=> $srmqty_per){
                            if(!empty($srmqty_per)){
                                $srmBatch = ProductInventory::find($batchid);
                                $qtyInKg = ($data['batch_size'] * $srmqty_per ) /100;
                                if($srmBatch->remaining_stock < $qtyInKg){
                                    return response()->json([
                                        'status' => false,
                                        'errors' => array('rm_errors'=> $srmBatch->supplier_batch_no. ' (Batch No.) qty can not be greater then '.$srmBatch->remaining_stock." kg")
                                    ]);
                                }
                            }
                        }
                    }
                }else{
                    return response()->json([
                        'status' => false,
                        'errors' => array('rm_errors'=> 'Please add rm requirements')
                    ]);
                }
                $totalQty =$totalSrmQtyPer + $totalRmQtyPer;
                if($totalQty !=100){
                    return response()->json([
                        'status' => false,
                        'errors' => array('rm_errors'=> 'Qty(%) should be 100%. Your current Qty(%) is '.$totalQty.'%')
                    ]);
                }
                
                $batchSheet = new BatchSheet;
                
                $product = Product::find($data['product_id']);
                $batchSheet->serial_no = $srno;
                $batchSheet->product_id = $data['product_id'];
                $batchSheet->standard_fill_size = $product->standard_fill_size;
                $batchSheet->standard_packing_type_id = $product->packing_type_id;
                $batchSheet->batch_size = $data['batch_size'];
                $batchSheet->no_of_packing_required = $batchSheet->batch_size/$batchSheet->standard_fill_size;
                $batchSheet->batch_no = $batch_no;
                $batchSheet->batch_no_str = $batch_no;
                $batchSheet->remaining_stock = $data['batch_size'];
                $batchSheet->machine_number = $data['machine_number'];
                $batchSheet->machine_capacity = $data['machine_capacity'];
                $batchSheet->operator_name = $data['operator_name'];
                $batchSheet->status = "RM Requested";
                $batchSheet->created_by = Auth::user()->id;
                $batchSheet->remarks = $data['remarks'];
                $batchSheet->save();
                $params['batch_sheet_id'] = $batchSheet->id;
                $params['status'] = 'RM Requested';
                $params['remarks'] = $data['remarks'];
                BatchSheetHistory::createBatchHistory($params);
                if(isset($data['rm_qty'])){
                    foreach($data['rm_qty'] as $batchid=> $rmqty_per){
                        if(!empty($rmqty_per)){
                            $rmBatch = RawMaterialInventory::find($batchid);
                            $qtyInKg = ($data['batch_size'] * $rmqty_per ) /100;
                            $bsReq = new BatchSheetRequirement;
                            $bsReq->batch_sheet_id = $batchSheet->id;
                            $bsReq->raw_material_inventory_id = $batchid;
                            $bsReq->qty_per = $rmqty_per;
                            $bsReq->qty = $qtyInKg;
                            $bsReq->save();
                        }
                    }
                }
                if(isset($data['srm_qty'])){
                    foreach($data['srm_qty'] as $batchid=> $srmqty_per){
                        if(!empty($srmqty_per)){
                            $rmBatch = ProductInventory::find($batchid);
                            $qtyInKg = ($data['batch_size'] * $srmqty_per ) /100;
                            $bsReq = new BatchSheetRequirement;
                            $bsReq->batch_sheet_id = $batchSheet->id;
                            $bsReq->product_inventory_id = $batchid;
                            $bsReq->qty_per = $srmqty_per;
                            $bsReq->qty = $qtyInKg;
                            $bsReq->save();
                        }
                    }
                }
                DB::commit();
                //echo "<pre>"; print_r($data); die;
                $redirectTo = url('admin/batch-sheets');
                return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
            }else{
                return response()->json(['status'=>false,'errors'=>$validator->messages()]);
            } 
        }
    	$title = "Create Production Job Card";
    	$products = jobcardProducts();
        $machines = Machine::where('status',1)->get();
        $machines = json_decode(json_encode($machines),true);
    	//echo "<pre>"; print_r($products); die;
    	return view('admin.job_cards.create_job_card')->with(compact('title','products','batch_no','machines'));  
    }

    public function appendStandardRecipe(Request $request){
    	if($request->ajax()){
    		$data = $request->all();
    		$rawMaterials = ProductRawMaterial::with('rawmaterial')->where('product_id',$data['proid'])->get()->toArray();
    		return response()->json([
                'view' => (String)View::make('admin.job_cards.standard_recipe')->with(compact('data','rawMaterials')),
            ]);
    	}
    }

    public function appendRequirementList(Request $request){
    	if($request->ajax()){
    		$data = $request->all();
    		if($data['type']=="RM"){
    			$rawMaterials = RawMaterial::where('status',1)->get()->toArray();
	    		$append_html = '<select class="form-control select2" id="RequirementList">
	                    <option value="">Select Raw Material</option>';
	                foreach ($rawMaterials as $key => $rawMaterial) {
	                	$append_html .= '<option value="'.$rawMaterial['id'].'">'.$rawMaterial['name'].'</option>';	  
	                }  
	            $append_html .= '</select>';
    		}elseif($data['type']=="SRM"){
    			$proids = ProductInventory::where('type','SRM')->pluck('product_id')->toArray();
    			$products = Product::wherein('id',$proids)->where('status',1)->get()->toArray();
    			$append_html = '<select class="form-control select2" id="RequirementList">
                    <option value="">Select Product</option>';
                foreach ($products as $key => $product) {
                	$append_html .= '<option value="'.$product['id'].'">'.$product['product_name'].'</option>';	  
                }  
            	$append_html .= '</select>';
    		}elseif($data['type']=="FP"){
    			$products = Product::where('status',1)->get()->toArray();
    			$append_html = '<select class="form-control select2" id="RequirementList">
                    <option value="">Select Product</option>';
                foreach ($products as $key => $product) {
                	$append_html .= '<option value="'.$product['id'].'">'.$product['product_name'].'</option>';	  
                }  
            	$append_html .= '</select>';
    		}
    		return $append_html;
    	}
    }

    public function addRmRequirement(Request $request){
    	if($request->ajax()){
    		$data = $request->all();
    		$rmBatches = array();$rawMaterial= array();
    		$srmBatches = array(); $product = array();
            $default_order_statuses = array_reverse(array("'Incoming Material'","'Sample Sent to Lab'","'Sample Received by Lab'","'QC Process Initiated'","'QC Approved'"));
    		if($data['type']=="RM"){
    			$rmBatches = RawMaterialInventory::with(['rm_history'=> function($query){
    				$query->where('status','QC Approved');
    			},'rm_histories'])->where('raw_material_id',$data['rm_id'])->orderByRaw("field(status,".implode(',',$default_order_statuses).")")->get()->toArray();
    			//echo "<pre>"; print_r($rmBatches); die;
    			$rawMaterial = RawMaterial::where('id',$data['rm_id'])->first();
    			$rawMaterial = json_decode(json_encode($rawMaterial),true);
    		}else if($data['type']=="SRM"){
    			$srmBatches = ProductInventory::with(['osp_history'=> function($query){
    				$query->where('status','QC Approved');
    			},'osp_histories'])->where('type','SRM')->where('product_id',$data['rm_id'])->orderByRaw("field(status,".implode(',',$default_order_statuses).")")->get()->toArray();
    			//echo "<pre>"; print_r($rmBatches); die;
    			$product = Product::where('id',$data['rm_id'])->first();
    			$product = json_decode(json_encode($product),true);
    		}
    		return response()->json([
                'view' => (String)View::make('admin.job_cards.rm_requirements')->with(compact('data','rmBatches','rawMaterial','product','srmBatches')),
            ]);
    		echo "<pre>"; print_r($data); die;
    	}
    }
}
