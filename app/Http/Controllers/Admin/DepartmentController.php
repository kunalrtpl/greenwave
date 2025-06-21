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
use App\Department;
use Validator;
class DepartmentController extends Controller
{
    //
    public function departments(Request $Request){
        Session::put('active','departments'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Department::query();
            if(!empty($data['department'])){
                $querys = $querys->where('department','like','%'.$data['department'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('departments.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $department){
                $id= base64_encode(convert_uuencode($department['id'])); 
                $checked='';
                if($department['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-department/'.$department['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $num,
                    $department['department'],
                    '<div  id="'.$department['id'].'" rel="departments" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Departments";
        return View::make('admin.departments.departments')->with(compact('title'));
    }

    public function addEditDepartment(Request $request,$departmentid=NULL){
    	if(!empty($departmentid)){
    		$departmentdata = Department::where('id',$departmentid)->first();
    		$title ="Edit Department";
    	}else{
    		$title ="Add Department";
	    	$departmentdata =array();
    	}
    	return view('admin.departments.add-edit-department')->with(compact('title','departmentdata'));
    }

    public function saveDepartment(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['departmentid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'department' => 'bail|required'
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $department = new Department; 
                    }else{
                        $department = Department::find($data['departmentid']); 
                    }
                    $department->department = $data['department'];
                    $department->status = 1;
                    $department->save();
                    $redirectTo = url('/admin/departments?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('department'=>$e->getMessage())]);
        }
    }
}
