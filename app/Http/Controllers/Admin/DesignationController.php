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
use App\Designation;
use Validator;
class DesignationController extends Controller
{
    //
    public function designations(Request $Request){
        Session::put('active','designations'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Designation::join('departments','departments.id','=','designations.department_id')->select('designations.*','departments.department');
            if(!empty($data['designation'])){
                $querys = $querys->where('designations.designation','like','%'.$data['designation'].'%');
            }
            if(!empty($data['department'])){
                $querys = $querys->where('departments.department','like','%'.$data['department'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('designations.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $designation){ 
                $checked='';
                if($designation['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-designation/'.$designation['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $designation['id'],
                    $designation['department'],
                    getDesignationParent($designation['parent_id']),
                    $designation['designation'],
                    '<div  id="'.$designation['id'].'" rel="designations" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Designations";
        return View::make('admin.designations.designations')->with(compact('title'));
    }

    public function addEditDesignation(Request $request,$designationid=NULL){
    	if(!empty($designationid)){
    		$designationdata = Designation::where('id',$designationid)->first();
    		$title ="Edit Designation";
    	}else{
    		$title ="Add Designation";
	    	$designationdata =array();
    	}
    	return view('admin.designations.add-edit-designation')->with(compact('title','designationdata'));
    }

    public function saveDesignation(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['designationid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'department_id' => 'bail|required',
                        'designation' => 'bail|required',
                        'parent_id' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $designation = new Designation; 
                    }else{
                        $designation = Designation::find($data['designationid']); 
                    }
                    $designation->department_id = $data['department_id'];
                    $designation->parent_id = $data['parent_id'];
                    $designation->designation = $data['designation'];
                    if(isset($data['type'])){
                    	$designation->type = $data['type'];
                    }
                    $designation->multiple_region = 0;
                    $designation->incentive_applicable = 0;
                    $designation->having_customer = 0;
                    if(isset($data['multiple_region'])){
                    	$designation->multiple_region = $data['multiple_region'];
                    }
                    if(isset($data['incentive_applicable'])){
                        $designation->incentive_applicable = $data['incentive_applicable'];
                    }
                    if(isset($data['having_customer'])){
                        $designation->having_customer = $data['having_customer'];
                    }
                    $designation->multiple_sub_region = 0;
                    if(isset($data['multiple_sub_region'])){
                    	$designation->multiple_sub_region = $data['multiple_sub_region'];
                    }
                    $designation->status = 1;
                    $designation->save();
                    $redirectTo = url('/admin/designations?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('designation'=>$e->getMessage())]);
        }
    }

    public function getDeptDesignations(Request $request){
    	if($request->ajax()){
    		$designationdata = $request->all();
    		return response()->json([
                'view' => (String)View::make('admin.designations.parent-designation')->with(compact('designationdata')),
            ]); 
    	}
    }
}
