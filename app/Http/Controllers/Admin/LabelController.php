<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use DB;
use Session;
use App\Label;
use App\Product;
use Validator;
class LabelController extends Controller
{
    //

	public function labels(Request $Request){
        Session::put('active','labels'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Label::query();
            if(!empty($data['label_type'])){
                $querys = $querys->where('label_type','like','%'.$data['label_type'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('labels.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $label){ 
                $checked='';
                if($label['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit Label" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-label/'.$label['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $label['id'],
                    $label['label_type'],
                    "Rs.".$label['price'],
                    '<div  id="'.$label['id'].'" rel="labels" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Labels";
        return View::make('admin.labels.labels')->with(compact('title'));
    }

    public function addEditLabel(Request $request,$labelid=NULL){
        $selLabels = array(); 
    	if(!empty($labelid)){
    		$labeldata = Label::where('id',$labelid)->first();
    		$title ="Edit Label";
            $selLabels = explode(',',$labeldata['for_product_types']);
    	}else{
    		$title ="Add Label";
	    	$labeldata =array();
    	}
    	return view('admin.labels.add-edit-label')->with(compact('title','labeldata','selLabels'));
    }

    public function saveLabel(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['labelid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'label_type' => 'bail|required',
                        'price' => 'bail|required|numeric',

                        'status' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $label = new Label; 
                    }else{
                        $label = Label::find($data['labelid']); 
                    }
                    $label->label_type = $data['label_type'];
                    $label->height = $data['height'];
                    $label->width = $data['width'];
                    $label->price = $data['price'];
                    $label->for_product_types = implode(',',$data['for_product_types']);
                    $label->status = $data['status'];
                    $label->save();
                    if($type =="update"){
                        $this->syncProductPackingCost($label->id);
                    }
                    $redirectTo = url('/admin/labels');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('size'=>$e->getMessage())]);
        }
    }

    public function syncProductPackingCost($lablelId){
        $products = Product::where('label_id',$lablelId)->get()->toArray();
        foreach($products as $product){
            $data = array();
            $data['packing_type_id'] = $product['packing_type_id'];
            $data['additional_packing_type_id'] = $product['additional_packing_type_id'];
            $data['packing_size_id'] = $product['packing_size_id'];
            $data['standard_fill_size'] = $product['standard_fill_size'];
            $data['label_id'] = $product['label_id'];
            $response = productPackingCost($data);
            $info = Product::find($product['id']);
            $info->basic_packing_material_cost = $response['basic_packing_material_cost'];
            $info->additional_packing_material_cost = $response['additional_packing_material_cost'];
            $info->label_cost = $response['label_cost'];
            $info->facilitation_cost = $response['facilitation_cost'];
            $info->packing_cost = $response['packing_cost'];
            $info->save();
        }
    }
}
