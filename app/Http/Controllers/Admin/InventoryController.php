<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Notification;
use App\PackingType;
use App\RawMaterialInventory;
use App\RawMaterialInventoryHistory;
use App\PackingSizeInventory;
use App\ProductInventory;
use App\PackingLabelInventory;
use App\ProductInventoryHistory;
use App\RawMaterialInventoryMedia;
use App\ProductInventoryMedia;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Validator;
use Auth;
use DB;
use Session;
use Redirect;
use PDF;
class InventoryController extends Controller
{
    public function addIncomingMaterial(Request $request){
    	Session::put('active','add-materials'); 
    	if($request->ajax()){	
    		$data = $request->all();
    		$validator = Validator::make($request->all(), [
                        'type' => 'bail|required',
                        'stock' => 'bail|required|numeric',
                        'raw_material_id' => 'bail|required_if:type,==,RM|nullable|exists:raw_materials,id',
                        /*'raw_material_price' => 'bail|required_if:type,==,RM|nullable|regex:/^\d+(\.\d{1,2})?$/'*/
                    ]
                );
                if($validator->passes()) {
                	if($data['type'] =="RM"){
                		DB::beginTransaction();
                		$rminvid = RawMaterialInventory::createRMInventory($data);
                		DB::commit();
                		$message = "Incoming Raw Material request has been added successfully";
                        $rmdetails = RawMaterialInventory::find($rminvid);
                        $rmdetails = json_decode(json_encode($rmdetails),true);
                        $this->sendPushAlertNotification('rm','Incoming Material',$rmdetails);
                		$redirectTo = url('/admin/inventory/rm');
                        Session::flash('flash_message_success',$message);
                    	return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                	}elseif($data['type'] =="PM"){
                        DB::beginTransaction();
                        PackingSizeInventory::createPMInventory($data);
                        DB::commit();
                        $message = "Packing Material been added successfully";
                        $redirectTo = url('/admin/inventory/pm');
                        return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                    }elseif($data['type'] =="PL"){
                        DB::beginTransaction();
                        PackingLabelInventory::createPLInventory($data);
                        DB::commit();
                        $message = "Packing Label been added successfully";
                        $redirectTo = url('/admin/entry/labels');
                        return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                    }elseif($data['type'] =="SRM" || $data['type'] =="RFDM"){
                        if($data['type'] =="RFDM"){
                            $packing_type_info = PackingType::find($data['final_packing_type']);
                            if($packing_type_info['stock'] <$data['packs_consumed']){
                                return response()->json([
                                    'status' => false,
                                    'errors' => array('final_packing_errors'=>$packing_type_info['name'] ." doesnot have sufficent stock")
                                ]);
                            }
                            if($data['final_no_of_packs'] < $data['packs_consumed']){
                                return response()->json([
                                    'status' => false,
                                    'errors' => array('final_packing_errors'=>$packing_type_info['name']." consumed packs should not be greater then No. of packs")
                                ]);
                            }
                            $totalMaterialFilled = $data['final_no_of_packs'] * $data['final_net_fill_size'];
                            if($data['stock'] != $totalMaterialFilled){
                                return response()->json([
                                    'status' => false,
                                    'type' =>'issueQty',
                                    'errors' => array('final_packing_errors'=>"Total Material filled should be ".$data['stock']."kg")
                                ]);
                            }
                        }
                        DB::beginTransaction();
                        $ospinvid = ProductInventory::createOSPInventory($data);
                        $this->uploadReports($ospinvid,$request);
                        DB::commit();
                        
                        if($data['type'] =="SRM"){
                            $redirectTo = url('admin/inventory/osp');
                            $message = "Sale Return Material request has been added successfully";
                            Session::flash('flash_message_success',$message);
                        }else{
                            $redirectTo = url('/admin/entry/rfdm');
                        }
                        /*$ospdetails = ProductInventory::find($ospinvid);
                        $ospdetails = json_decode(json_encode($ospdetails),true);
                        $this->sendPushAlertNotification('osp','Incoming Material (OSP)',$ospdetails);*/
                        return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                    } 
                }else{
                	return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
    	}else{
    		$title = "Add Incoming Material";
    		return view('admin.inventories.add-incoming-material')->with(compact('title'));
    	}
    }

    public function uploadReports($ospinvid,$request){
        $createOSPinv = ProductInventory::find($ospinvid);
        if($request->hasFile('coa')){
            if (Input::file('coa')->isValid()) {
                $file = Input::file('coa');
                $destination = 'InventoryMedias/';
                $ext= $file->getClientOriginalExtension();
                $mainFilename = "coa-".uniqid().date('h-i-s').".".$ext;
                $file->move($destination, $mainFilename);
                $createOSPinv->coa = $mainFilename;
            }
        }
        if($request->hasFile('qc_report')){
            if (Input::file('qc_report')->isValid()) {
                $file = Input::file('qc_report');
                $destination = 'InventoryMedias/';
                $ext= $file->getClientOriginalExtension();
                $mainFilename = "qc_report-".uniqid().date('h-i-s').".".$ext;
                $file->move($destination, $mainFilename);
                $createOSPinv->qc_report = $mainFilename;
            }
        }
        $createOSPinv->save();
    }

    public function appendMaterialDetails(Request $request){
    	if($request->ajax()){
    		$data = $request->all();  
    		return response()->json([
                'view' => (String)View::make('admin.inventories.append-material-details')->with(compact('data')),
            ]);
    	}
    }

    public function rawMaterials(Request $Request){
        Session::put('active','inventory-rm'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = RawMaterialInventory::with('addedby')->join('raw_materials','raw_materials.id','=','raw_material_inventories.raw_material_id')->select('raw_material_inventories.*','raw_materials.name','raw_materials.price as rm_price','raw_materials.coding','raw_materials.shelf_life');
            if(Auth::user()->type=="employee"){
                $querys = $querys->wherein('raw_material_inventories.status',getInventoryAccess('view'));
            }
            if(!empty($data['serial_no'])){
                $querys = $querys->where('raw_material_inventories.serial_no','like','%'.$data['serial_no'].'%');
            }
            if(!empty($data['name'])){
                $querys = $querys->where('raw_materials.coding','like','%'.$data['name'].'%');
            }
            if(!empty($data['status'])){
                $querys = $querys->where('raw_material_inventories.status',$data['status']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('raw_material_inventories.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $rawMaterial){ 
                if($rawMaterial['status'] =="QC Approved" || $rawMaterial['status'] =="QC Rejected"){
                    //Nothing to do
                    $actionValues ='';
                }else{
                    $actionValues = '<a title="Update Status" class="btn btn-sm green margin-top-10" href="'.url('/admin/inventory/update-rm-status/'.$rawMaterial['id']).'"> <i class="fa fa-clock-o"></i>
                        </a>';
                    if(Auth::user()->type!="admin"){
                        $updateAccessResp = getRMInventoryUpdateAccess($rawMaterial['status']);
                        if(!$updateAccessResp){
                            $actionValues = "";
                        }
                    }
                }
                if($rawMaterial['status'] =="QC Approved"){
                    $actionValues .= '<a title="Media Section" class="btn btn-sm blue margin-top-10" href="'.url('/admin/rm-inventory-media/'.$rawMaterial['id']).'"> <i class="fa fa-image"></i>
                        </a>';
                }
                if($rawMaterial['status'] =="QC Rejected"){
                    $status = '<a data-rminvid="'.$rawMaterial['id'].'" href="javascript:;" class="rmtracking"><span class="badge badge-danger">'.$rawMaterial['status'].'</span></a>';
                }else{
                    $status = '<a data-rminvid="'.$rawMaterial['id'].'" href="javascript:;" class="rmtracking"><span class="badge badge-success">'.$rawMaterial['status'].'</span></a>';
                }
                $deletBtn = "";
                if($rawMaterial['status'] == "Incoming Material"){
                    $deletBtn = '<a onclick="return ConfirmDelete()"  title="Delete" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-rm-entry/'.$rawMaterial['id']).'"> <i class="fa fa-times"></i>
                    </a>';
                }
                $rawMaterialInfo = '<b>Price :- </b>'. $rawMaterial['rm_price'].'<br> <b>Coding :- </b>'.$rawMaterial['coding'].'<br> <b>Shelf life :- </b>'.$rawMaterial['shelf_life'];
                $num = ++$i;
                $records["data"][] = array(  
                	date('d F Y',strtotime($rawMaterial['incoming_date'])),    
                    $rawMaterial['serial_no'],    
                    $rawMaterial['name'],
                    $rawMaterial['supplier_batch_no'],
                    $rawMaterial['stock'],
                    $rawMaterial['addedby']['name'],
                    $status,
                    $actionValues.$deletBtn
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Incoming Raw Materials";
        return View::make('admin.inventories.rm.rm')->with(compact('title'));
    }

    public function rawMaterialTracking(Request $request){
    	if($request->ajax()){
    		$data = $request->all();
    		$rawMaterialInventoryDetails = RawMaterialInventory::with('addedby')->join('raw_materials','raw_materials.id','=','raw_material_inventories.raw_material_id')->select('raw_material_inventories.*','raw_materials.name','raw_materials.price as rm_price','raw_materials.coding','raw_materials.shelf_life')->where('raw_material_inventories.id',$data['rminvid'])->first();
    		$materialStatus = materialStatus();
            if($rawMaterialInventoryDetails['status'] =="QC Approved" || $rawMaterialInventoryDetails['status'] =="QC Rejected"){
                array_push($materialStatus,$rawMaterialInventoryDetails['status']);
            }
    		$trackingInfo = '<figure>
							    	<img src="https://media.istockphoto.com/vectors/delivery-truck-search-icon-shipment-finder-shipment-information-vector-id1126906820" alt="">
							   		<figcaption>
							    	<h4>Details</h4>
							    	<p>Raw Material :- '.$rawMaterialInventoryDetails['name'].'</p>
							      	<p>Supplier Batch Number:- '.$rawMaterialInventoryDetails['supplier_batch_no'].'</php>
							    	</figcaption>
							  	</figure>
						  	<div class="order-track">';
            //echo "<pre>"; print_r($materialStatus);
    		foreach($materialStatus as $status){
    			$details = RawMaterialInventoryHistory::with('updateby')->where('raw_material_inventory_id',$data['rminvid'])->where('status',$status)->first();
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
                                    <p class="order-track-text-stat">'.$status.'<a target="_blank" href='.url('/admin/inventory/rm-samples/'.$rawMaterialInventoryDetails['id']).'> (View Stickers)</a></p>';
                }elseif($status == 'QC Approved'){
                    $trackingInfo .= '</div>
                                    <div class="order-track-text">
                                    <p class="order-track-text-stat">'.$status.'<a target="_blank" href='.url('/admin/inventory/rm-packing-labels/'.$rawMaterialInventoryDetails['id']).'> (View Packing Labels)</a> --- <a target="_blank" href='.url('/admin/inventory/rm-pdf/'.$rawMaterialInventoryDetails['id'].'/filled').'> (Download Report)</a></p>';
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

    public function updateRmStatus(Request $request,$rminvid){
        $rmdetails = RawMaterialInventory::with(['rm_checklists','rawmaterial','rm_history'=>function($query){
            $query->with('updateby')->where('status','Sample Sent to Lab');
        }])->where('id',$rminvid)->first();
        $rmdetails = json_decode(json_encode($rmdetails),true);
        //echo "<pre>"; print_r($rmdetails); die;
        if(Auth::user()->type!="admin"){
            $updateAccessResp = getRMInventoryUpdateAccess($rmdetails['status']);
            if(!$updateAccessResp){
                return redirect::to('admin/dashboard')->with('flash_message_error','You have no right to update the status of this RAW Material');
            }
        }
        if($request->ajax()){
            $data = $request->all();
            DB::beginTransaction();
            $historydata['status'] = $data['status'];
            $historydata['raw_material_inventory_id'] = $rminvid;
            $historydata['remarks'] = $data['remarks'];
            if($data['status']=="Sample Sent to Lab"){
                $checkStock = PackingType::where('id',$data['packing_type_id'])->first();
                if($checkStock->stock < $data['no_of_samples']){
                    return response()->json([
                        'status' => false,
                        'errors' => array('packing_type_id'=>"Insufficent Stock of selected packing type")
                    ]);
                }
                $historydata['packing_type_id'] = $data['packing_type_id'];
                $historydata['no_of_samples'] = $data['no_of_samples'];
                RawMaterialInventoryHistory::createHistory($historydata);
            }elseif($data['status']=="Sample Received by Lab"){
                RawMaterialInventoryHistory::createHistory($historydata);
            }elseif($data['status']=="QC Process Initiated"){
                RawMaterialInventoryHistory::createHistory($historydata);
            }else{
                //Approved or Rejected History
                $historydata['status'] = $data['qc_status'];
                $historydata['request_data'] = $data;
                $historydata['rm_details'] = $rmdetails;
                RawMaterialInventoryHistory::createHistory($historydata);
            }
            DB::commit();
            $this->sendPushAlertNotification('rm',$data['status'],$rmdetails);
            $message = "Status has been updated successfully";
            $redirectTo = url('/admin/inventory/rm/?s='.$message);
            return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
        }
        //echo "<pre>"; print_r($rmdetails); die;
        $title = "Update Status";
        return view('admin.inventories.rm.update-rm-status')->with(compact('title','rmdetails'));
    }

    public function downloadRMpdf($rminvid,$type){
        $rmdetails = RawMaterialInventory::with(['rm_checklists','rawmaterial','rm_history'=>function($query){
            $query->with('updateby')->where('status','Sample Sent to Lab');
        },'rminv_checklists'])->where('id',$rminvid)->first();
        $rmdetails = json_decode(json_encode($rmdetails),true);
        //echo "<pre>"; print_r($rmdetails); die;
        $fileName = $rmdetails['serial_no']."-".time().".pdf";
        PDF::loadView('admin.inventories.rm.rm-qc-pdf',compact('rmdetails','type'))->save('RawMaterialMedia/'.$fileName);
        $file_path = public_path('RawMaterialMedia/'.$fileName);
        return response()->download($file_path);
    }


    public function packingMaterials(Request $Request){
        Session::put('active','inventory-pm'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = PackingSizeInventory::with('addedby')->join('packing_types','packing_types.id','=','packing_size_inventories.packing_type_id')->select('packing_size_inventories.*','packing_types.name');
            /*if(!empty($data['name'])){
                $querys = $querys->where('raw_materials.name','like','%'.$data['name'].'%');
            }
            if(!empty($data['status'])){
                $querys = $querys->where('raw_material_inventories.status','like','%'.$data['status'].'%');
            }*/
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('packing_size_inventories.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $packingsizeInv){ 
                $actionValues ='';
                $num = ++$i;
                $records["data"][] = array(  
                    $num,    
                    $packingsizeInv['name'],
                    $packingsizeInv['stock'],
                    $packingsizeInv['remarks'],
                    $packingsizeInv['addedby']['name'],
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Incoming Packing Materials";
        return View::make('admin.inventories.pm.pm')->with(compact('title'));
    }

    public function rmSamples($rminvid){
        $title = "Raw Material Samples";
        $rminvDetails = RawMaterialInventory::with(['rm_history'=>function($query){
            $query->where('status','Sample Sent to Lab');
        },'rawmaterial'])->where('id',$rminvid)->first();
        $rminvDetails = json_decode(json_encode($rminvDetails),true);
        //echo "<pre>"; print_r($rminvDetails); die;
        return view('admin.inventories.rm.samples')->with(compact('title','rminvDetails'));
    }

    public function rmPackingLabel($rminvid){
        $title = "Raw Material Samples";
        $rminvDetails = RawMaterialInventory::with(['rm_history'=>function($query){
            $query->where('status','QC Approved');
        },'rawmaterial'])->where('id',$rminvid)->first();
        $rminvDetails = json_decode(json_encode($rminvDetails),true);
        return view('admin.inventories.rm.packing-labels')->with(compact('title','rminvDetails'));
    }

    public function sendPushAlertNotification($type,$status,$details){
        $tokens  = DB::table('users')->whereRaw('FIND_IN_SET("'.$status.'",view_inventory_access)')->where('notification_token','!=','')->pluck('notification_token')->toArray();
        if($type =="rm"){
            if(!empty($tokens)){
                $messageDetails['title'] = "RM Status Update";
                $messageDetails['body'] = "Dear User, ".$details['serial_no']." status has been updated to ".$status;
                if($status =="QC Process Initiated"){
                    $messageDetails['click_action'] = url('admin/inventory/rm');
                }else{
                    $messageDetails['click_action'] = url('admin/inventory/update-rm-status/'.$details['id']);
                }
                Notification::sendPushNotification($tokens,$messageDetails);
            }
        }else{
            if(!empty($tokens)){
                $messageDetails['title'] = "OSP Status Update";
                $messageDetails['body'] = "Dear User, ".$details['serial_no']." status has been updated to ".$status;
                if($status =="Packing & Labelling"){
                    $messageDetails['click_action'] = url('admin/inventory/osp');
                }else{
                    $messageDetails['click_action'] = url('admin/inventory/update-osp-status/'.$details['id']);
                }
                Notification::sendPushNotification($tokens,$messageDetails);
            }
        }
    }

    public function ospProducts(Request $Request){
        Session::put('active','inventory-osp'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = ProductInventory::with('addedby')->join('products','products.id','=','product_inventories.product_id')->select('product_inventories.*','products.product_name','products.product_price','products.product_code')->where('type','SRM');
            if(Auth::user()->type=="employee"){
                $querys = $querys->wherein('product_inventories.status',getOSPAccess('view'));
            }
            if(!empty($data['serial_no'])){
                $querys = $querys->where('product_inventories.serial_no',$data['serial_no']);
            }
            if(!empty($data['name'])){
                $querys = $querys->where('products.product_name','like','%'.$data['name'].'%');
            }
            if(!empty($data['status'])){
                $querys = $querys->where('product_inventories.status','like','%'.$data['status'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('product_inventories.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $ospProduct){ 
                if($ospProduct['status'] =="QC Rejected" || $ospProduct['status'] =="QC Approved" || $ospProduct['status'] =="Re-Process Advised"){
                    //Nothing to do
                    $actionValues ='';
                }else{
                    $actionValues = '<a title="Update Status" class="btn btn-sm green margin-top-10" href="'.url('/admin/inventory/update-osp-status/'.$ospProduct['id']).'"> <i class="fa fa-clock-o"></i>
                        </a>';
                    if(Auth::user()->type!="admin"){
                        $updateAccessResp = getOSPInventoryUpdateAccess($ospProduct['status']);
                        if(!$updateAccessResp){
                            $actionValues = "";
                        }
                    }
                }
                if($ospProduct['status'] =="QC Approved"){
                    $actionValues .= '<a title="Media Section" class="btn btn-sm blue margin-top-10" href="'.url('/admin/osp-media/'.$ospProduct['id']).'"> <i class="fa fa-image"></i>
                        </a>';
                }
                $deletBtn = '';
                if($ospProduct['status'] == "Incoming Material"){
                    $deletBtn = '<a onclick="return ConfirmDelete()" title="Delete" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-osp-entry/'.$ospProduct['id']).'"> <i class="fa fa-times"></i>
                        </a>';
                    /*$delBtnShow = false;
                    if(Auth::user()->type=="admin"){
                        $delBtnShow = true;
                    }else{
                        $days = getDiffDays($ospProduct['incoming_date'],date('Y-m-d'));
                        if($days <=2){
                            $delBtnShow = true;
                        }
                    }
                    if($delBtnShow){
                        
                    }*/
                }
                if($ospProduct['status'] =="Rejected"){
                    $status = '<a data-ospinvid="'.$ospProduct['id'].'" href="javascript:;" class="OSPtracking"><span class="badge badge-danger">'.$ospProduct['status'].'</span></a>';
                }else{
                    $status = '<a data-ospinvid="'.$ospProduct['id'].'" href="javascript:;" class="OSPtracking"><span class="badge badge-success">'.$ospProduct['status'].'</span></a>';
                }
                $ospProductInfo = '<b>Price :- </b>'. $ospProduct['product_price'].'<br> <b>Code :- </b>'.$ospProduct['product_code'];
                $num = ++$i;
                $records["data"][] = array(  
                    date('d F Y',strtotime($ospProduct['incoming_date'])), 
                    $ospProduct['serial_no'],    
                    $ospProduct['product_name'],    
                    $ospProduct['supplier_batch_no'],   
                    $ospProduct['stock'],
                    $status,
                    $actionValues.$deletBtn
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Incoming Outsource Product Material";
        return View::make('admin.inventories.osp.osp')->with(compact('title'));
    }

    public function updateOspStatus(Request $request,$ospinvid){
        $ospdetails = ProductInventory::with(['osp_checklists','product','osp_history'=>function($query){
            $query->with('updateby')->where('status','Sample Sent to Lab');
        }])->where('id',$ospinvid)->first();
        $ospdetails = json_decode(json_encode($ospdetails),true);
        //echo "<pre>"; print_r($ospdetails); die;
        if(Auth::user()->type!="admin"){
            $updateAccessResp = getOSPInventoryUpdateAccess($ospdetails['status']);
            if(!$updateAccessResp){
                return redirect::to('admin/dashboard')->with('flash_message_error','You have no right to update the status of this RAW Material');
            }
        }
        if($request->ajax()){
            $data = $request->all();
            DB::beginTransaction();
            $historydata['status'] = $data['status'];
            $historydata['product_inventory_id'] = $ospinvid;
            $historydata['remarks'] = $data['remarks'];
            if($data['status']=="Sample Sent to Lab"){
                $checkStock = PackingType::where('id',$data['packing_type_id'])->first();
                if($checkStock->stock < $data['no_of_samples']){
                    return response()->json([
                        'status' => false,
                        'errors' => array('packing_type_id'=>"Insufficent Stock of selected packing type")
                    ]);
                }
                $historydata['packing_type_id'] = $data['packing_type_id'];
                $historydata['no_of_samples'] = $data['no_of_samples'];
                ProductInventoryHistory::createHistory($historydata);
            }elseif($data['status']=="Sample Received by Lab"){
                ProductInventoryHistory::createHistory($historydata);
            }elseif($data['status']=="QC Process Initiated"){
                ProductInventoryHistory::createHistory($historydata);
            }else{
                ProductInventoryHistory::createHistory($historydata);
                //Approved or Rejected History
                $historydata['request_data'] = $data;
                $historydata['osp_details'] = $ospdetails;
                ProductInventoryHistory::createHistory($historydata);
            }/*elseif($data['status']=="Packing & Labelling"){
                $historydata['packing_info'] = $data;
                $historydata['packing_info']['osp_details'] = $ospdetails;
                ProductInventoryHistory::createHistory($historydata);
                //Material Ready for Dispatch
                $historydata['status'] = 'Material Ready for Dispatch';
                ProductInventoryHistory::createHistory($historydata);
            }*/
            DB::commit();
            $this->sendPushAlertNotification('osp',$data['status'],$ospdetails);
            $message = "Status has been updated successfully";
            $redirectTo = url('/admin/inventory/osp/?s='.$message);
            return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
        }
        $title = "Update Status";
        return view('admin.inventories.osp.update-osp-status')->with(compact('title','ospdetails'));
    }

    public function downloadOSPpdf($rminvid,$type){
        $ospdetails = ProductInventory::with(['osp_checklists','product','osp_history'=>function($query){
            $query->with('updateby')->where('status','Sample Sent to Lab');
        },'ospinv_checklists'])->where('id',$rminvid)->first();
        $ospdetails = json_decode(json_encode($ospdetails),true);
        //echo "<pre>"; print_r($ospdetails); die;
        $fileName = $ospdetails['serial_no']."-".time().".pdf";
        PDF::loadView('admin.inventories.osp.osp-qc-pdf',compact('ospdetails','type'))->save('OSPMedia/'.$fileName);
        $file_path = public_path('OSPMedia/'.$fileName);
        return response()->download($file_path);
    }

    public function ospTracking(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $OSPInventoryDetails = ProductInventory::with('addedby')->join('products','products.id','=','product_inventories.product_id')->select('product_inventories.*','products.product_name','products.product_price','products.product_code')->where('product_inventories.id',$data['ospinvid'])->first();
            $materialStatus = materialStatus();
            if($OSPInventoryDetails['status'] =="QC Approved"){
                array_push($materialStatus,'QC Approved');
                /*array_push($materialStatus,'Packing & Labelling');
                array_push($materialStatus,'Material Ready for Dispatch');*/
            }elseif($OSPInventoryDetails['status'] =="Re-Process Advised"){
                array_push($materialStatus,$OSPInventoryDetails['status']);
            }elseif($OSPInventoryDetails['status'] =="QC Rejected"){
                array_push($materialStatus,$OSPInventoryDetails['status']);
            }
            $trackingInfo = '<figure>
                                    <img src="https://media.istockphoto.com/vectors/delivery-truck-search-icon-shipment-finder-shipment-information-vector-id1126906820" alt="">
                                    <figcaption>
                                    <h4>Details</h4>
                                    <p>Product Code :- '.$OSPInventoryDetails['product_code'].'</p>
                                    <p>Supplier Batch Number:- '.$OSPInventoryDetails['supplier_batch_no'].'</php>
                                    </figcaption>
                                </figure>
                            <div class="order-track">';
            //echo "<pre>"; print_r($materialStatus);
            foreach($materialStatus as $status){
                $details = ProductInventoryHistory::with('updateby')->where('product_inventory_id',$data['ospinvid'])->where('status',$status)->first();
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
                                    <p class="order-track-text-stat">'.$status.'<a target="_blank" href='.url('/admin/inventory/osp-samples/'.$OSPInventoryDetails['id']).'> (View Stickers)</a></p>';
                }elseif($status == 'QC Approved'){
                    $trackingInfo .= '</div>
                                    <div class="order-track-text">
                                    <p class="order-track-text-stat">'.$status.'<a target="_blank" href='.url('admin/inventory/osp-packing-labels/'.$OSPInventoryDetails['id']).'> (View Packing Labels)</a> ---- <a target="_blank" href='.url('/admin/inventory/osp-pdf/'.$OSPInventoryDetails['id'].'/filled').'> (Download Report)</a></p>';
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

    public function ospSamples($ospinvid){
        $title = "Outsourced Product Samples";
        $ospinvDetails = ProductInventory::with(['osp_history'=>function($query){
            $query->where('status','Sample Sent to Lab');
        },'product'])->where('id',$ospinvid)->first();
        $ospinvDetails = json_decode(json_encode($ospinvDetails),true);
        //echo "<pre>"; print_r($ospinvDetails); die;
        return view('admin.inventories.osp.samples')->with(compact('title','ospinvDetails'));
    }

    public function ospPackingLabel($ospinvid){
        $title = "Outsourced Product Packing & Labelling";
        $ospinvDetails = ProductInventory::with(['osp_history'=>function($query){
            $query->where('status','QC Approved');
        },'product'])->where('id',$ospinvid)->first();
        $ospinvDetails = json_decode(json_encode($ospinvDetails),true);
        return view('admin.inventories.osp.packing-labels')->with(compact('title','ospinvDetails'));
    }

    public function rmInventoryMedia($rminvid){
        $details = RawMaterialInventory::with(['rawmaterial','medias'])->where('id',$rminvid)->first();
        $details = json_decode(json_encode($details),true);
        ///echo "<pre>"; print_r($details); die;
        $title = "Upload Media";
        return view('admin.inventories.rm.rm-inventory-media')->with(compact('title','details')); 
    }

    public function saveRmInventoryMedia(Request $request){
        if($request->ajax()){
            $data = $request->all();
            if($request->hasFile('media')){
                if (Input::file('media')->isValid()) {
                    $rminvmedia = new RawMaterialInventoryMedia;
                    $rminvmedia->raw_material_inventory_id = $data['raw_material_inventory_id'];
                    $file = Input::file('media');
                    $destination = 'InventoryMedias/';
                    $ext= $file->getClientOriginalExtension();
                    $mainFilename = "rawmaterial-media-".uniqid().date('h-i-s').".".$ext;
                    $file->move($destination, $mainFilename);
                    $rminvmedia->file = $mainFilename;
                    $rminvmedia->save();
                }
            }
            $redirectTo = url('/admin/rm-inventory-media/'.$data['raw_material_inventory_id']);
            return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
        }
    }

    public function deleteRmInventoryMedia($mediaid){
        $image = RawMaterialInventoryMedia::find($mediaid);
        $file_path = public_path('/InventoryMedias/'.$image->file);
        unlink($file_path);
        $image->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function ospMedia($rminvid){
        $details = ProductInventory::with(['product','medias'])->where('id',$rminvid)->first();
        $details = json_decode(json_encode($details),true);
        ///echo "<pre>"; print_r($details); die;
        $title = "Upload Media";
        return view('admin.inventories.osp.osp-media')->with(compact('title','details')); 
    }

    public function saveOspMedia(Request $request){
        if($request->ajax()){
            $data = $request->all();
            if($request->hasFile('media')){
                if (Input::file('media')->isValid()) {
                    $rminvmedia = new ProductInventoryMedia;
                    $rminvmedia->product_inventory_id = $data['product_inventory_id'];
                    $file = Input::file('media');
                    $destination = 'InventoryMedias/';
                    $ext= $file->getClientOriginalExtension();
                    $mainFilename = "osp-media-".uniqid().date('h-i-s').".".$ext;
                    $file->move($destination, $mainFilename);
                    $rminvmedia->file = $mainFilename;
                    $rminvmedia->save();
                }
            }
            $redirectTo = url('/admin/osp-media/'.$data['product_inventory_id']);
            return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
        }
    }

    public function deleteOspMedia($mediaid){
        $image = ProductInventoryMedia::find($mediaid);
        $file_path = public_path('/InventoryMedias/'.$image->file);
        unlink($file_path);
        $image->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function getProductLabels(Request $request){
        $data = $request->all();
        $product = \App\Product::find($data['product_id']);
        $findproductfortype = $product->is_trader_product;
        $lables = \App\Label::where('status',1)->whereRaw('FIND_IN_SET("'.$findproductfortype.'", for_product_types)')->get()->toarray();
        $appendLabels = '<option value>Please Select</option>';
        foreach($lables as $label){
            $appendLabels .= '<option value="'.$label['id'].'">'.$label['label_type'].'</option>';
        }
        return $appendLabels;
    }
}
