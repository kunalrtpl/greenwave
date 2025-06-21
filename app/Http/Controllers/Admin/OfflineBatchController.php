<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Notification;
use App\PackingType;
use App\OfflineBatch;
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
class OfflineBatchController extends Controller
{
    //
    public function offlineBatches(Request $Request){
        Session::put('active','offlineBatches'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = OfflineBatch::query();
            if(!empty($data['batch_no'])){
                $querys = $querys->where('batch_no','like','%'.$data['batch_no'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $offlineBatch){ 
                $actionValues='
                    <a style="display:none;" title="Edit Batch" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-offline/'.$offlineBatch['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a onclick="return ConfirmDelete()"  title="Delete Batch" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-offline-batch/'.$offlineBatch['id']).'"> <i class="fa fa-times"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $offlineBatch['id'],
                    $offlineBatch['batch_no'],
                    '<a target="_blank" href="'.url('/InventoryMedias/'.$offlineBatch['coa']).'">View COA</a>',
                    '<a target="_blank" href="'.url('/InventoryMedias/'.$offlineBatch['qc_report']).'">View QC Report</a>',
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Offline Batches";
        return View::make('admin.offline_batches.batches')->with(compact('title'));
    }


    public function addEditOfflineBatch(Request $request,$offlinebatchid=NULL){
        if(!empty($offlinebatchid)){
            $batchData = OfflineBatch::where('id',$offlinebatchid)->first();
            $title ="Edit Offline Batch";
        }else{
            $title ="Add Offline Batch";
            $batchData =array();
        }
        return view('admin.offline_batches.add_edit_offline_batch')->with(compact('title','batchData'));
    }

    public function saveOfflineBatch(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['offlinebatchid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'batch_no' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $batch = new OfflineBatch; 
                    }else{
                        $batch = OfflineBatch::find($data['batch_no']); 
                    }
                    $batch->batch_no = $data['batch_no'];
                    if($request->hasFile('coa')){
                        if (Input::file('coa')->isValid()) {
                            $file = Input::file('coa');
                            $destination = 'InventoryMedias/';
                            $ext= $file->getClientOriginalExtension();
                            $mainFilename = "offline_coa-".uniqid().date('h-i-s').".".$ext;
                            $file->move($destination, $mainFilename);
                            $batch->coa = $mainFilename;
                        }
                    }
                    if($request->hasFile('qc_report')){
                        if (Input::file('qc_report')->isValid()) {
                            $file = Input::file('qc_report');
                            $destination = 'InventoryMedias/';
                            $ext= $file->getClientOriginalExtension();
                            $mainFilename = "offline_qc_report-".uniqid().date('h-i-s').".".$ext;
                            $file->move($destination, $mainFilename);
                            $batch->qc_report = $mainFilename;
                        }
                    }
                    $batch->save();
                    $redirectTo = url('/admin/offline-batches');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('size'=>$e->getMessage())]);
        }
    }

    public function deleteOfflineBatch($batchid){
        OfflineBatch::where('id',$batchid)->delete();
        return redirect::to('/admin/offline-batches')->with('flash_message_success','Offline batches has been deleted successfully');
    }
}
