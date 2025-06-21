<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Notification;
use App\RawMaterialInventory;
use App\RawMaterialInventoryHistory;
use App\PackingSizeInventory;
use App\ProductInventory;
use App\PackingLabelInventory;
use App\ProductInventoryHistory;
use App\BatchSheet;
use Illuminate\Support\Facades\View;
use Validator;
use Auth;
use DB;
use Session;
use Redirect;
use PDF;
class IncomingentryController extends Controller
{
    //
    public function rawMaterialsEntry(Request $Request){
        Session::put('active','rm-inventory-entry'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = RawMaterialInventory::with('addedby')->join('raw_materials','raw_materials.id','=','raw_material_inventories.raw_material_id')->select('raw_material_inventories.*','raw_materials.name','raw_materials.price as rm_price','raw_materials.coding','raw_materials.shelf_life');
            if(Auth::user()->type!="admin"){
            	$querys = $querys->where('raw_material_inventories.added_by',Auth::user()->id);
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
                $num = ++$i;
                $deletBtn = '';
                if($rawMaterial['status'] == "Incoming Material"){
                	$deletBtn = '<br><a title="Delete" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-rm-entry/'.$rawMaterial['id']).'"> <i class="fa fa-times"></i>
                        </a>';
                }

                $actionValues = $rawMaterial['status'].$deletBtn;
                $records["data"][] = array(  
                	date('d F Y h:i:a',strtotime($rawMaterial['created_at'])),    
                    $rawMaterial['serial_no'],    
                    $rawMaterial['name'],
                    $rawMaterial['stock'],
                    $rawMaterial['supplier_batch_no'],
                    $rawMaterial['remarks'],
                    $rawMaterial['addedby']['name'],
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Incoming Raw Materials Entries";
        return View::make('admin.entries.rm')->with(compact('title'));
    }

    public function deleteRmEntry($id){
    	RawMaterialInventory::where('id',$id)->delete();
    	return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }
        
    public function srmProductsEntry(Request $Request){
        Session::put('active','inventory-srm-entry'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = ProductInventory::with('addedby')->join('products','products.id','=','product_inventories.product_id')->select('product_inventories.*','products.product_name','products.product_price','products.product_code')->where('type','SRM');
            if(Auth::user()->type=="employee"){
                $querys = $querys->wherein('product_inventories.added_by',Auth::user()->id);
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
                $deletBtn = '';
                if($ospProduct['status'] == "Incoming Material"){
                    $deletBtn = '<br><a title="Delete" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-osp-entry/'.$ospProduct['id']).'"> <i class="fa fa-times"></i>
                        </a>';
                }
                $actionValues = $ospProduct['status'].$deletBtn;
                $num = ++$i;
                $records["data"][] = array(  
                    date('d F Y',strtotime($ospProduct['created_at'])), 
                    $ospProduct['serial_no'],    
                    $ospProduct['product_name'],      
                    $ospProduct['stock'],
                    $ospProduct['supplier_batch_no'],
                    $ospProduct['remarks'],
                    $ospProduct['addedby']['name'],
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Sale Return Material Entries";
        return View::make('admin.entries.srm')->with(compact('title'));
    }

    public function rfdmProductsEntry(Request $Request){
        Session::put('active','inventory-rfdm-entry'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = ProductInventory::with('addedby')->join('products','products.id','=','product_inventories.product_id')->select('product_inventories.*','products.product_name','products.product_price','products.product_code')->where('type','RFDM');
            if(Auth::user()->type=="employee"){
                $querys = $querys->wherein('product_inventories.added_by',Auth::user()->id);
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
                $deletBtn = '';
                if($ospProduct['status'] == "Incoming Material"){
                    $deletBtn = '<br><a title="Delete" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-osp-entry/'.$ospProduct['id']).'"> <i class="fa fa-times"></i>
                        </a>';
                }
                $actionValues ="";
                $num = ++$i;
                $records["data"][] = array(  
                    date('d F Y',strtotime($ospProduct['created_at'])),     
                    $ospProduct['product_name'],      
                    $ospProduct['stock'],
                    $ospProduct['supplier_batch_no'],
                    $ospProduct['remarks'],
                    $ospProduct['addedby']['name'],
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Ready for Dispatch Material Entries";
        return View::make('admin.entries.rfdm')->with(compact('title'));
    }

    public function deleteOspEntry($id){
    	ProductInventory::where('id',$id)->delete();
    	return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function labelsEntry(Request $Request){
        Session::put('active','labels-entry'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = PackingLabelInventory::with('addedby')->join('labels','labels.id','=','packing_label_inventories.label_id')->select('packing_label_inventories.*','labels.label_type');
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
                        ->OrderBy('packing_label_inventories.id','DESC')
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
                    $packingsizeInv['label_type'],
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
        return View::make('admin.entries.packing_labels')->with(compact('title'));
    }

    public function ihpProductsEntry(Request $Request){
        Session::put('active','ihp-entry'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = BatchSheet::with('addedby')->join('products','products.id','=','batch_sheets.product_id')->select('batch_sheets.*','products.product_name');
            if(Auth::user()->type=="employee"){
                $querys = $querys->where('batch_sheets.created_by',Auth::user()->id);
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
            	$actionValues = "";
                $num = ++$i;
                $records["data"][] = array(  
                    date('d F Y h:i:a',strtotime($batchSheet['created_at'])), 
                    $batchSheet['batch_no_str'],  
                    $batchSheet['product_name'],    
                    $batchSheet['no_of_packing_required'],
                    $batchSheet['batch_size'],
                    $batchSheet['remarks'],
                    $batchSheet['status'],
                    $batchSheet['addedby']['name'],
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Batch Sheets";
        return View::make('admin.entries.ihp')->with(compact('title'));
    }
}
