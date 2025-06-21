<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Notification;
use App\ProductRawMaterial;
use App\BatchSheetMaterial;
use App\BatchSheet;
use App\Label;
use App\BatchSheetHistory;
use App\RawMaterialInventory;
use App\ProductInventory;
use App\ProductChecklist;
use App\BatchSheetMaterialLog;
use App\RawMaterial;
use App\BatchSheetProductChecklist;
use App\BatchSheetConsumption;
use App\Product;
use App\PackingSize;
use App\PackingType;
use App\BatchSheetMedia;
use Illuminate\Support\Facades\View;
use Validator;
use Auth;
use DB;
use Session;
use Redirect;
use PDF;
use DateTime;
use App\BatchSheetRequirement;
use Illuminate\Support\Facades\Input;
class BatchSheetController extends Controller
{
    //
    public function createBatchSheet(Request $request){
        Session::put('active','createBatchSheet');
    	if($request->ajax()){
    		$data = $request->all();
            //echo "<pre>"; print_r($data);
    		$validator = Validator::make($request->all(), [
                    'product_id' => 'bail|required|exists:products,id',
                    'batch_size' => 'bail|required|numeric',
                    'machine_number' => 'bail|required',
                    'machine_capacity' => 'bail|required',
                    'operator_name' => 'bail|required',
                ]
            );
            if($validator->passes()) {
                $packingInfo = DB::table('packing_sizes')->where('id',$data['packing_size_id'])->where('current_stock','>=',$data['no_packings_required'])->count();
                if($packingInfo == 0){
                    return response()->json([
                        'status' => false,
                        'errors' => array('no_packings_required'=> 'Out of stock Currently')
                    ]);
                }
                foreach($data['rm_ids'] as $rmkey => $rmid){
                    if($data['current_stock'][$rmkey] <= $data['qtys'][$rmkey]){
                        return response()->json([
                            'status' => false,
                            'errors' => array('rm_requirements'=> 'Some of the raw material is out of stock')
                        ]);
                    }
                }
            	DB::beginTransaction();
            	$batchSheet = new BatchSheet;
                $LastBs = BatchSheet::orderby('id','DESC')->select('serial_no')->first();
                if($LastBs && isset($LastBs->serial_no) && !empty($LastBs->serial_no)) {
                    $explodeSRNO = explode('ISP-', $LastBs->serial_no);
                    $srno = $explodeSRNO[1] + 1;
                    $srno = 'ISP-'.$srno;
                }else{
                    $srno = 'ISP-1000';
                }
            	$batchSheet->serial_no = $srno;
                $batchSheet->product_id = $data['product_id'];
            	$batchSheet->batch_size = $data['batch_size'];
                $batchSheet->remaining_stock = $data['batch_size'];
            	$batchSheet->machine_number = $data['machine_number'];
            	$batchSheet->machine_capacity = $data['machine_capacity'];
            	$batchSheet->operator_name = $data['operator_name'];
            	$batchSheet->status = "RM Requested";
                $batchSheet->created_by = Auth::user()->id;
                $batchSheet->remarks = $data['remarks'];
            	$batchSheet->save();
            	foreach($data['rm_ids'] as $rmkey => $rmid){
            		$batchSheetRm = new BatchSheetMaterial;
            		$batchSheetRm->batch_sheet_id = $batchSheet->id;
            		$batchSheetRm->raw_material_id = $rmid;
            		$batchSheetRm->qty = $data['qtys'][$rmkey];
            		$batchSheetRm->percentage_included = $data['percentages'][$rmkey];
            		$batchSheetRm->save();
            	}
                $params['batch_sheet_id'] = $batchSheet->id;
                $params['status'] = 'RM Requested';
                $params['remarks'] = $data['remarks'];
                BatchSheetHistory::createBatchHistory($params);
            	DB::commit();
            	$redirectTo = url('admin/create-batch-sheet?m=Batch sheet has been created successfullly!');
            	return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
            }else{
            	return response()->json(['status'=>false,'errors'=>$validator->messages()]);
            }
    	}
    	$title = "Create Batch Sheet";
    	return view('admin.batchsheets.create-batch-sheet')->with(compact('title'));
    }

    public function batchRmRequirements(Request $request){
    	if($request->ajax()){
    		$data = $request->all();
    		$rawMaterials = ProductRawMaterial::with('rawmaterial')->where('product_id',$data['proid'])->get()->toArray();
    		$append_html = '<label class="col-md-3 control-label">Standard Recipe </label>
                                    <div class="col-md-9">
                                        <table class="table table-bordered">
                                            <thead>
                                              <tr>
                                                <th>RM</th>
                                                <th>Qty (%)</th>
                                                <th>Qty (kg.)</th>
                                                <th>Available Stock <br> (kg.)</th>
                                              </tr>
                                            </thead>
                                            <tbody>';
                    foreach($rawMaterials as $key => $rmInfo) {
                    	$qty =  ($data['batchsize'] * $rmInfo['percentage_included']/100);
                    $append_html .= '<input type="hidden" name="rm_ids[]" value="'.$rmInfo['raw_material_id'].'">
                        <input type="hidden" name="current_stock[]" value="'.$rmInfo['rawmaterial']['current_stock'].'">
                    	<input type="hidden" name="percentages[]" value="'.$rmInfo['percentage_included'].'">
                    	<input type="hidden" name="qtys[]" value="'.$qty.'">
                    					<tr>
                    					<td>'.$rmInfo['rawmaterial']['name'].'</td>
                                        <td>'.$rmInfo['percentage_included'].' %</td>
                                        <td>'.$qty.'</td>
                    					<td>'.$rmInfo['rawmaterial']['current_stock'].'</td>
                    					
                    				</tr>'; 
                    }
                    $append_html .= '<tr>
                    					<td></td>
                    					<td><b>100%</b></td>
                    					<td><b>'.$data['batchsize'].'</b></td>
                                        <td></td>
                    				</tr>';           
            	$append_html .='</tbody></table><h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-rm_requirements"></h4></div>';
            	return $append_html;
    	}
    }

    public function batchSheets(Request $Request){
        if(isset($_GET['type']) && !empty($_GET['type'])){
            Session::put('active',$_GET['type']);
        }else{
            Session::put('active','batchSheets'); 
        }
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = BatchSheet::with('addedby')->join('products','products.id','=','batch_sheets.product_id')->select('batch_sheets.*','products.product_name');
            /*if(isset($_GET['type']) && !empty($_GET['type'])){
                $querys = $querys->where('batch_sheets.status',$_GET['type']);
            }else{
                $querys = $querys->where('batch_sheets.status','RM Requested');
            }*/
            if(Auth::user()->type=="employee"){
                $querys = $querys->wherein('batch_sheets.status',getIHPAccess('view'));
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like','%'.$data['product_name'].'%');
            }
            if(!empty($data['status'])){
                $querys = $querys->where('batch_sheets.status',$data['status']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('batch_sheets.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $batchSheet){
                if($batchSheet['status'] =="Re-Process Advised" || $batchSheet['status'] =="Ready for Dispatch" || $batchSheet['status'] =="QC Rejected"){
                    //Nothing to do
                    $actionValues ='';
                }else{
                    $actionValues = '<a title="Update Status" class="btn btn-sm green margin-top-10" href="'.url('/admin/update-batch-sheet/'.$batchSheet['id']).'"> <i class="fa fa-clock-o"></i>
                        </a>';
                    if(Auth::user()->type!="admin"){
                        $updateAccessResp = getBatchSheetUpdateAccess($batchSheet['status']);
                        if(!$updateAccessResp){
                            $actionValues = "";
                        }
                    }
                }
                $actionValues .= '<a title="Media Section" class="btn btn-sm blue margin-top-10" href="'.url('/admin/batch-sheet-media/'.$batchSheet['id']).'"> <i class="fa fa-image"></i>
                        </a>';
                $batch_no_str = '';
                if(!empty($batchSheet['batch_no_str'])){
                    $batch_no_str = '<br><b>Batch No. :-'.$batchSheet['batch_no_str'].'</b>';
                }
                if($batchSheet['status'] =="QC Rejected"){
                    $status = '<a data-batchsheetid="'.$batchSheet['id'].'" href="javascript:;" class="batchtracking"><span class="badge badge-danger">'.$batchSheet['status'].'</span></a>';
                }else{
                    $status = '<a data-batchsheetid="'.$batchSheet['id'].'" href="javascript:;" class="batchtracking"><span class="badge badge-success">'.$batchSheet['status'].'</span></a>';
                }
                $num = ++$i;
                $records["data"][] = array(  
                    date('d F Y',strtotime($batchSheet['created_at'])), 
                    $batchSheet['serial_no'],    
                    $batchSheet['product_name'],    
                    $batchSheet['batch_no_str'],
                    $batchSheet['batch_size'],
                    $batchSheet['addedby']['name'],
                    $status,
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Batch Sheets";
        return View::make('admin.batchsheets.batch-sheets')->with(compact('title'));
    }

    public function updateBatchSheet(Request $request,$batchid){
        $batchDetails = BatchSheet::with(['materials','product','batchsheet_requirements','standard_packing'])->where('id',$batchid)->first();
        $batchDetails = json_decode(json_encode($batchDetails),true);
        //echo "<pre>"; print_r($batchDetails); die;
        if($request->ajax()){
            $data = $request->all();
            if($data['status'] =="RM Issued"){
                foreach($batchDetails['batchsheet_requirements'] as $key=> $requirement){
                    if(!empty($requirement['rawmaterial_inventory'])){
                        if($requirement['rawmaterial_inventory']['remaining_stock'] <$requirement['qty']){
                            return response()->json([
                                'status' => false,
                                'type' =>'issueQty',
                                'errors' => array('requirements'=> "Batch No. (".$requirement['rawmaterial_inventory']['supplier_batch_no'].") ". "qty is not enough")
                            ]);
                        }
                    }elseif(!empty($requirement['product_inventory'])){
                        if($requirement['product_inventory']['remaining_stock'] <$requirement['qty']){
                            return response()->json([
                                'status' => false,
                                'type' =>'issueQty',
                                'errors' => array('requirements'=> "Batch No. (".$requirement['product_inventory']['supplier_batch_no'].") ". "qty is not enough")
                            ]);
                        }
                    }
                }

                foreach($batchDetails['batchsheet_requirements'] as $key=> $requirement){
                    $btachreq = BatchSheetRequirement::find($requirement['id']);
                    $btachreq->remarks = $data['remarks'][$requirement['id']];
                    $btachreq->save();
                    if(!empty($requirement['rawmaterial_inventory'])){
                        if($requirement['rawmaterial_inventory']['remaining_stock'] >=$requirement['qty']){
                            RawMaterialInventory::where('id',$requirement['rawmaterial_inventory'])->decrement('remaining_stock',$requirement['qty']);
                        }
                    }elseif(!empty($requirement['product_inventory'])){
                        if($requirement['product_inventory']['remaining_stock'] >=$requirement['qty']){
                            ProductInventory::where('id',$requirement['product_inventory'])->decrement('remaining_stock',$requirement['qty']);
                        }
                    }
                }
                /*return response()->json([
                    'status' => false,
                    'type' =>'issueQty',
                    'errors' => array('requirements'=> 'Inavlid input')
                ]);*/
                
                /*
                $batchTotalQty = 0;
                foreach($batchDetails['materials'] as $materialInfo){
                    $rmApprovedBatches = RawMaterialInventory::approvedRms($materialInfo['raw_material_id']);
                    $totalIssueQty = 0;
                    foreach($rmApprovedBatches as $approveBatch){
                        $issueQty = $data['issue_qty'][$approveBatch['id']];
                        if(empty($issueQty)){
                            $issueQty = 0;
                        }
                        $batchTotalQty +=$issueQty;
                        $totalIssueQty += $issueQty;
                        if($issueQty < 0 ||  $approveBatch['remaining_stock'] <$issueQty){
                            return response()->json(['status'=>false,'errors'=> array($approveBatch['id']=>'Please enter valid issue qty'),'type' =>'issueQty']);
                        }
                    }
                }*/
                /*if($batchDetails['batch_size'] != $batchTotalQty){
                    return response()->json(['status'=>false,'type'=>'batch_size_error','message'=> 'Entered qty is not match with batch size. Please verify it and save it again']);
                }
*/                //Saving data in database
                
                /*foreach($batchDetails['materials'] as $materialInfo){
                    $rmApprovedBatches = RawMaterialInventory::approvedRms($materialInfo['raw_material_id']);
                    $totalIssueQty = 0;
                    foreach($rmApprovedBatches as $approveBatch){
                        if(is_numeric($data['issue_qty'][$approveBatch['id']])){
                            $issueQty = $data['issue_qty'][$approveBatch['id']];
                        }else{
                           $issueQty = 0; 
                        }
                        $totalIssueQty += $issueQty;
                        //Create Log
                        $bsmLog = new BatchSheetMaterialLog;
                        $bsmLog->batch_sheet_material_id = $materialInfo['id'];
                        $bsmLog->raw_material_inventory_id = $approveBatch['id'];
                        $bsmLog->issue_qty = $issueQty;
                        $bsmLog->created_by = Auth::user()->id;
                        $bsmLog->save();
                        RawMaterialInventory::where('id',$approveBatch['id'])->decrement('remaining_stock',$issueQty);
                    }
                    RawMaterial::where('id',$materialInfo['raw_material_id'])->decrement('current_stock',$totalIssueQty);
                    BatchSheetMaterial::where('id',$materialInfo['id'])->update(['issued_qty'=>$totalIssueQty]);
                }*/
                DB::beginTransaction();
                
                BatchSheet::where('id',$batchDetails['id'])->update(['status'=>$data['status']]);
                //echo "<pre>"; print_r($data); die;
                //PackingSize::where('id',$data['packing_size_id'])->decrement('current_stock',$data['no_of_packing_required']);
                $params['batch_sheet_id'] = $batchDetails['id'];
                $params['status'] = $data['status'];
                $params['remarks'] = '';

                BatchSheetHistory::createBatchHistory($params); 
                DB::commit();
                $redirect_url = url('admin/batch-sheets');  
                return response()->json(['status'=>true,'url'=>$redirect_url]);
            }else if($data['status'] =="Sample Sent to Lab"){
                $checkStock = PackingType::where('id',$data['packing_type_id'])->first();
                if($checkStock->stock < $data['no_of_samples']){
                    return response()->json([
                        'status' => false,
                        'errors' => array('packing_type_id'=>"Insufficent Stock of selected packing type")
                    ]);
                }
                DB::beginTransaction();
                $lastBNo = BatchSheet::orderby('id','DESC')->select('batch_no')->orderby('batch_no','DESC')->where('batch_no','!=',0)->first();
                if(isset($lastBNo->batch_no)){
                    $batch_no = $lastBNo->batch_no + 1;
                    $batch_no_str = 'BN-PRD-'.$batch_no;
                }else{
                    $batch_no = 1000;
                    $batch_no_str = 'BN-PRD-1000';
                }
                BatchSheet::where('id',$batchDetails['id'])->update(['batch_no'=>$batch_no,'batch_no_str'=>$batch_no_str,'no_of_samples'=>$data['no_of_samples'],'packing_type_id'=>$data['packing_type_id'],'status'=>$data['status']]);
                $params['batch_sheet_id'] = $batchDetails['id'];
                $params['status'] = $data['status'];
                $params['remarks'] = '';
                BatchSheetHistory::createBatchHistory($params);
                PackingType::where('id',$data['packing_type_id'])->decrement('stock',$data['no_of_samples']); 
                DB::commit();
                $redirect_url = url('admin/batch-sheets');  
                return response()->json(['status'=>true,'url'=>$redirect_url]);
            }else if($data['status'] =="Sample Received by Lab"){
                DB::beginTransaction();
                $params['batch_sheet_id'] = $batchDetails['id'];
                $params['status'] = $data['status'];
                $params['remarks'] = $data['remarks'];
                BatchSheet::where('id',$batchDetails['id'])->update(['status'=>$data['status']]);
                BatchSheetHistory::createBatchHistory($params);
                DB::commit();
                $redirect_url = url('admin/batch-sheets');  
                return response()->json(['status'=>true,'url'=>$redirect_url]);
            }elseif($data['status']=="QC Process Initiated"){
                DB::beginTransaction();
                $params['batch_sheet_id'] = $batchDetails['id'];
                $params['status'] = $data['status'];
                $params['remarks'] = $data['remarks'];
                BatchSheet::where('id',$batchDetails['id'])->update(['status'=>$data['status']]);
                BatchSheetHistory::createBatchHistory($params);
                DB::commit();
                $redirect_url = url('admin/batch-sheets');  
                return response()->json(['status'=>true,'url'=>$redirect_url]);
            }elseif($data['status']=="Material Received by Packing Department"){
                DB::beginTransaction();
                $params['batch_sheet_id'] = $batchDetails['id'];
                $params['status'] = $data['status'];
                $params['remarks'] = $data['remarks'];
                BatchSheet::where('id',$batchDetails['id'])->update(['status'=>$data['status']]);
                BatchSheetHistory::createBatchHistory($params);
                DB::commit();
                $redirect_url = url('admin/batch-sheets');  
                return response()->json(['status'=>true,'url'=>$redirect_url]);
            }elseif($data['status']=="Ready for Dispatch"){
                if(isset($data['final_packing_types'])){
                    $resp = has_dupes($data['final_packing_types']);
                    if(!$resp){
                        if(!empty($data['packing_wastage'])){
                            $netBatchSize = $batchDetails['batch_size'] - $data['packing_wastage'];
                        }else{
                            $netBatchSize = $batchDetails['batch_size'];
                        }
                        $totalMaterialFilled = 0;
                        foreach($data['final_packing_types'] as $pkey=> $packingType){
                            if(empty($data['final_net_fill_size'][$pkey])){
                                $data['final_net_fill_size'][$pkey] = 0;
                            }
                            if(empty($data['final_no_of_packs'][$pkey])){
                                $data['final_no_of_packs'][$pkey] = 0;
                            }

                            $materialfilled = $data['final_no_of_packs'][$pkey] * $data['final_net_fill_size'][$pkey];
                            $totalMaterialFilled += $materialfilled;
                            $packing_type_info = PackingType::find($packingType);
                            /*if($packing_type_info['stock'] <=$data['final_no_of_packs'][$pkey]){
                                return response()->json([
                                    'status' => false,
                                    'type' =>'issueQty',
                                    'errors' => array('final_packing_errors'=>$packing_type_info['name'] ." doesnot have sufficent stock")
                                ]);
                            }*/
                            if($packing_type_info['stock'] <$data['packs_consumed'][$pkey]){
                                return response()->json([
                                    'status' => false,
                                    'type' =>'issueQty',
                                    'errors' => array('final_packing_errors'=>$packing_type_info['name'] ." doesnot have sufficent stock")
                                ]);
                            }
                            /*if($data['final_no_of_packs'][$pkey] < $data['packs_consumed'][$pkey]){
                                return response()->json([
                                    'status' => false,
                                    'type' =>'issueQty',
                                    'errors' => array('final_packing_errors'=>$packing_type_info['name']." consumed packs should not be greater then No. of packs")
                                ]);
                            }*/
                        }
                        if($netBatchSize != $totalMaterialFilled){
                            return response()->json([
                                'status' => false,
                                'type' =>'issueQty',
                                'errors' => array('final_packing_errors'=>"Total Material filled should be ".$netBatchSize."kg")
                            ]);
                        }
                    }else{
                        return response()->json([
                                'status' => false,
                                'type' =>'issueQty',
                                'errors' => array('final_packing_errors'=>'You can not select same packing type multiple times')
                            ]);
                    }
                    $totalMaterialFilled = 0;
                    foreach($data['final_packing_types'] as $pkey=> $packingType){
                        $materialfilled = $data['final_no_of_packs'][$pkey] * $data['final_net_fill_size'][$pkey];
                        $totalMaterialFilled += $materialfilled;
                        $batchcons =  new BatchSheetConsumption;
                        $batchcons->batch_sheet_id = $batchDetails['id'];
                        $batchcons->product_id = $batchDetails['product_id'];
                        if(!empty($packingType)){
                            $batchcons->packing_type_id = $packingType;
                        }
                        $batchcons->final_no_of_packs = $data['final_no_of_packs'][$pkey];
                        $batchcons->final_net_fill_size = $data['final_net_fill_size'][$pkey];
                        $batchcons->final_material_filled = $materialfilled;
                        $batchcons->packs_consumed = $data['packs_consumed'][$pkey];
                        if(!empty($packingType)) {
                            $batchcons->final_net_weight = $data['final_net_weight'][$pkey];
                            $batchcons->final_gross_weight = $data['final_gross_weight'][$pkey];
                        } 
                        if(!empty($data['labels'][$pkey])) {
                            $batchcons->label_id = $data['labels'][$pkey];
                        }
                        $batchcons->labels_consumed = $data['labels_consumed'][$pkey];
                        $batchcons->save();
                        if(!empty($packingType)  && $data['packs_consumed'][$pkey] >0){
                            PackingType::where('id',$packingType)->decrement('stock',$data['packs_consumed'][$pkey]);
                        }
                        if(!empty($data['labels'][$pkey]) && $data['labels_consumed'][$pkey] >0){
                            Label::where('id',$data['labels'][$pkey])->decrement('stock',$data['labels_consumed'][$pkey]);
                        }
                    }
                    Product::where('id',$batchDetails['product_id'])->increment('current_stock',$totalMaterialFilled);
                    $params['batch_sheet_id'] = $batchDetails['id'];
                    $params['status'] = $data['status'];
                    $params['remarks'] = $data['remarks'];
                    BatchSheetHistory::createBatchHistory($params);
                    BatchSheet::where('id',$batchDetails['id'])->update(['status'=>$params['status'],'packing_wastage'=>$data['packing_wastage'],'net_batch_size'=>$netBatchSize]);
                }
                $redirect_url = url('admin/batch-sheets');  
                return response()->json(['status'=>true,'url'=>$redirect_url]);
            }else{
                DB::beginTransaction();
                $params['batch_sheet_id'] = $batchDetails['id'];
                if($data['status']=='QC Approved'){
                    $params['status'] = "Ready for Packing";
                }else{
                    $params['status'] = $data['status'];
                }
                $params['remarks'] = $data['remarks'];
                BatchSheetHistory::createBatchHistory($params);
                //Approved or Rejected History
                $params['batch_sheet_id'] = $batchDetails['id'];
                //$params['status'] = $data['qc_status'];
                $params['remarks'] = $data['remarks'];
                BatchSheetHistory::createBatchHistory($params);
                BatchSheetProductChecklist::createProductChecklist($data,$batchDetails);
                BatchSheet::where('id',$batchDetails['id'])->update(['status'=>$params['status']]);
                if($data['status']=='QC Approved'){
                   Product::where('id',$batchDetails['product_id'])->increment('current_stock',$batchDetails['batch_size']);
                }
                DB::commit();
                $redirect_url = url('admin/batch-sheets');  
                return response()->json(['status'=>true,'url'=>$redirect_url]);
            }
        }
        $findproductfortype = $batchDetails['product']['is_trader_product'];
        $lables = \App\Label::where('status',1)->whereRaw('FIND_IN_SET("'.$findproductfortype.'", for_product_types)')->get()->toarray();
        $checklists = ProductChecklist::with('checklist')->where('product_id',$batchDetails['product_id'])->get();
        //echo "<pre>"; print_r($batchDetails); die;
        $checklists = json_decode(json_encode($checklists),true);
        $title = "Update Status";
        return view('admin.batchsheets.update-batchsheet')->with(compact('title','batchDetails','checklists','lables')); 
    }

    public function batchSheetTracking(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $batchSheetdetails = BatchSheet::with('addedby')->join('products','products.id','=','batch_sheets.product_id')->select('batch_sheets.*','products.product_name','products.product_price','products.product_code')->where('batch_sheets.id',$data['batchsheetid'])->first();
            $batchSheetStatus = batchSheetStatus();
            if($batchSheetdetails['status'] =="Ready for Packing"){
                array_push($batchSheetStatus,'Ready for Packing');
            }else if($batchSheetdetails['status'] =="Material Received by Packing Department"){
                array_push($batchSheetStatus,'Ready for Packing','Material Received by Packing Department');
            }else if($batchSheetdetails['status'] =="Ready for Dispatch"){
                array_push($batchSheetStatus,'Ready for Packing','Material Received by Packing Department','Ready for Dispatch');
            }elseif($batchSheetdetails['status'] =="QC Rejected"){
                array_push($batchSheetStatus,$batchSheetdetails['status']);
            }
            $trackingInfo = '<figure>
                                    <img src="https://cdn-icons-png.flaticon.com/128/11434/11434164.png" alt="">
                                    <figcaption>
                                    <h4>Details</h4>
                                    <p>Product Code :- '.$batchSheetdetails['product_code'].'</p>
                                    </figcaption>
                                </figure>
                            <div class="order-track">';
            //echo "<pre>"; print_r($materialStatus);
            foreach($batchSheetStatus as $status){
                $details = BatchSheetHistory::with('updateby')->where('batch_sheet_id',$data['batchsheetid'])->where('status',$status)->first();
                $details = json_decode(json_encode($details),true);
                $trackingInfo .= '<div class="order-track-step">
                                  <div class="order-track-status">';
                if(!empty($details)){               
                    $trackingInfo .= '<span class="order-track-status-dot">
                                    </span>
                                    <span class="order-track-status-line"></span>';
                }else{
                    $trackingInfo .= '<span class="order-track-status-hide-dot">
                                    </span>
                                    <span class="order-track-hide-status-line"></span>';
                }
                if($status == 'Sample Sent to Lab'){
                    $trackingInfo .= '</div>
                                    <div class="order-track-text">
                                    <p class="order-track-text-stat">'.$status.'</p>';
                }elseif($status == 'QC Approved'){
                    $trackingInfo .= '</div>
                                    <div class="order-track-text">
                                    <p class="order-track-text-stat">'.$status.'<a target="_blank" href='.url('admin/batch-packing-labels/'.$batchSheetdetails['id']).'> (View Packing Labels)</a></p>';
                }else{
                    $trackingInfo .= '</div>
                                    <div class="order-track-text">
                                    <p class="order-track-text-stat">'.$status.'</p>';
                }
                if(!empty($details)){
                    $trackingInfo .= '<span class="order-track-text-sub"><b>
                                        '.date('d F Y h:ia',strtotime($details['created_at'])).'
                                </b></span>
                                <br>
                                <span class="order-track-text-sub"><b>Update By :- 
                                        '.$details['updateby']['name'].'
                                </b></span>';
                }

                $trackingInfo .=  '</div>
                                </div>';
            } 
            $trackingInfo .= '</div>';
            return $trackingInfo;
        }
    }

    public function bactchpackingLabels($batchid){
        $title = "Packing Lables";
        $batchDetails = BatchSheet::with(['batch_history'=>function($query){
            $query->where('status','QC Approved');
        },'product'])->where('id',$batchid)->first();
        $batchDetails = json_decode(json_encode($batchDetails),true);
        return view('admin.batchsheets.packing-labels')->with(compact('title','batchDetails'));
    }

    public function batchSheetMedia($batchsheetid){
        $details = BatchSheet::with(['product','medias'])->where('id',$batchsheetid)->first();
        $details = json_decode(json_encode($details),true);
        ///echo "<pre>"; print_r($details); die;
        $title = "Upload Media";
        return view('admin.batchsheets.batch-sheet-media')->with(compact('title','details')); 
    }

    public function saveBatchSheetMedia(Request $request){
        if($request->ajax()){
            $data = $request->all();
            if($request->hasFile('media')){
                if (Input::file('media')->isValid()) {
                    $rminvmedia = new BatchSheetMedia;
                    $rminvmedia->batch_sheet_id = $data['batch_sheet_id'];
                    $file = Input::file('media');
                    $destination = 'InventoryMedias/';
                    $ext= $file->getClientOriginalExtension();
                    $mainFilename = "batchsheet-media-".uniqid().date('h-i-s').".".$ext;
                    $file->move($destination, $mainFilename);
                    $rminvmedia->file = $mainFilename;
                    $rminvmedia->save();
                }
            }
            $redirectTo = url('/admin/batch-sheet-media/'.$data['batch_sheet_id']);
            return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
        }
    }

    public function deleteBatchSheetMedia($mediaid){
        $image = BatchSheetMedia::find($mediaid);
        $file_path = public_path('/InventoryMedias/'.$image->file);
        unlink($file_path);
        $image->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }


    public function fetchProductBatchConsumptions(Request $request){
        $productId = $request->product_id;
        /*$batchConsumptions = BatchSheetConsumption::with(['batchsheet','packing_type'])
            ->where('batch_sheet_consumptions.product_id', $productId)
            ->get();*/
        $batchConsumptions = BatchSheetConsumption::with(['batchsheet','packing_type'])
        ->join('batch_sheets', 'batch_sheet_consumptions.batch_sheet_id', '=', 'batch_sheets.id')
        ->join('batch_sheet_histories', function ($join) {
            $join->on('batch_sheet_histories.batch_sheet_id', '=', 'batch_sheets.id')
                 ->on('batch_sheet_histories.status', '=', 'batch_sheets.status');
        })
        ->join('packing_types', 'batch_sheet_consumptions.packing_type_id', '=', 'packing_types.id')
        ->where('batch_sheet_consumptions.product_id', $productId)
        ->select('batch_sheet_consumptions.*','batch_sheet_histories.created_at as status_date') // Select more columns if needed
        ->get();
        //echo "<pre>"; print_r(json_decode(json_encode($batchConsumptions),true)); die;
        $html = view('admin.batchsheets.partials.product_batch_consumptions', compact('batchConsumptions'))->render();

        return response()->json(['html' => $html]);
    }
}
