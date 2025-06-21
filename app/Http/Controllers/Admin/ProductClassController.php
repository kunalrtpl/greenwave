<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\Support\Facades\Route;
use App\ProductClass;
use DB;
use Cookie;
use Session;
use Crypt;
use Illuminate\Support\Facades\Mail;
use Auth;
use Image;
use Validator;
class ProductClassController extends Controller
{
    //
     public function productClass(Request $Request){
        Session::put('active','produtClass'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = ProductClass::query();
            if(!empty($data['class_name'])){
                $querys = $querys->where('class_name','like','%'.$data['class_name'].'%');
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
            foreach($querys as $productclass){
                $checked='';
                if($productclass['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-product-class/'.$productclass['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a onclick="return ConfirmDelete()"  title="Delete Product Class" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-product-class/'.$productclass['id']).'"> <i class="fa fa-times"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $productclass['id'],
                    $productclass['class_name'],
                    $productclass['from'].'%',
                    $productclass['to'].'%',
                    $productclass['standard'].'%',
                    $productclass['er'],
                    '<div  id="'.$productclass['id'].'" rel="product_classes" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Product Class";
        return View::make('admin.products.product-class')->with(compact('title'));
    }

    public function addEditProductClass(Request $request,$productclassid=NULL){
    	if(!empty($productclassid)){
    		$productclassdata = ProductClass::where('id',$productclassid)->first();
    		$title ="Edit Product Class";
    	}else{
    		$title ="Add Product Class";
	    	$productclassdata =array();
    	}
    	return view('admin.products.add-edit-product-class')->with(compact('title','productclassdata'));
    }

    public function saveProductClass(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['productclassid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'class_name' => 'bail|required',
                        'from' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                        'to' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                        'standard' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                        'status' => 'bail|required'
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $productclass = new ProductClass; 
                    }else{
                        $productclass = ProductClass::find($data['productclassid']); 
                    }
                    $productclass->class_name = $data['class_name'];
                    $productclass->from = $data['from'];
                    $productclass->to = $data['to'];
                    $productclass->standard = $data['standard'];
                    $productclass->er = $data['er'];
                    $productclass->save();
                    $redirectTo = url('/admin/product-class?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('name'=>$e->getMessage())]);
        }
    }

    public function deleteProductClass($classid){
        ProductClass::where('id',$classid)->delete();
        return redirect::to('/admin/product-class')->with('flash_message_success','Product class has been deleted successfully');
    }
}
