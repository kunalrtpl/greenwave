<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\Support\Facades\Route;
use App\Checklist;
use DB;
use Cookie;
use Session;
use Crypt;
use Illuminate\Support\Facades\Mail;
use Auth;
use Image;
use Validator;
class ChecklistController extends Controller
{
    //
    public function checklists(Request $Request){
        Session::put('active','checklists'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Checklist::query();
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
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
            foreach($querys as $checklist){
                $checked='';
                if($checklist['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                if($checklist['parent_id'] == NULL){
                    $parentcategory = "ROOT";
                }else{
                    $parent_category = DB::table('checklists')->where('id',$checklist['parent_id'])->select('name')->first();
                    $parentcategory = $parent_category->name;
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-checklist/'.$checklist['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $checklist['id'],
                    $checklist['name'],
                    $parentcategory,
                    '<div  id="'.$checklist['id'].'" rel="checklists" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Checklists";
        return View::make('admin.checklists.checklists')->with(compact('title'));
    }

    public function addEditCheckList(Request $request,$checklistid=NULL){
    	if(!empty($checklistid)){
    		$checklistdata = Checklist::where('id',$checklistid)->first();
    		$title ="Edit Checklist";
    	}else{
    		$title ="Add Checklist";
	    	$checklistdata =array();
    	}
    	return view('admin.checklists.add-edit-checklist')->with(compact('title','checklistdata'));
    }

    public function saveChecklist(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['checklistid']==""){
                    $type ="add";
                    $seounique = "unique:categories";
                }else{ 
                    $type ="update";
                    $seounique = "unique:categories,seo_unique,".$data['checklistid'];
                }
                $validator = Validator::make($request->all(), [
                        'checklist' => 'bail|required',
                        'name' => 'bail|required',
                        /*'sort' => 'bail|required|integer|min:0',*/
                        'status' => 'bail|required'
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $checklist = new Checklist; 
                    }else{
                        $checklist = Checklist::find($data['checklistid']); 
                    }
                    $checklist->name = $data['name'];
                    if($data['checklist']=="ROOT"){
                        $checklist->parent_id = NULL;
                    }else{
                        $checklist->parent_id = $data['checklist'];
                    }
                    $checklist->save();
                    $redirectTo = url('/admin/checklists?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('name'=>$e->getMessage())]);
        }
    }
}
