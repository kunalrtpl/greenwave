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
use App\State;
use Validator;

class StateController extends Controller
{
    //
    public function states(Request $Request){
        Session::put('active','states'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = State::query();
            if(!empty($data['state_name'])){
                $querys = $querys->where('state_name','like','%'.$data['state_name'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('states.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $state){
                $id= base64_encode(convert_uuencode($state['id'])); 
                $checked='';
                if($state['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-state/'.$state['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $state['id'],
                    $state['state_name'],
                    $state['country_name'],
                    '<div  id="'.$state['id'].'" rel="states" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "States";
        return View::make('admin.states.states')->with(compact('title'));
    }

    public function addEditState(Request $request,$stateid=NULL){
    	if(!empty($stateid)){
    		$statedata = State::where('id',$stateid)->first();
    		$title ="Edit State";
    	}else{
    		$title ="Add State";
	    	$statedata =array();
    	}
    	return view('admin.states.add-edit-state')->with(compact('title','statedata'));
    }

    public function saveState(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['stateid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'country' => 'bail|required',
                        'state_name' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $state = new State; 
                    }else{
                        $state = State::find($data['stateid']); 
                    }
                    $state->country_name = $data['country'];
                    $state->state_name = $data['state_name'];
                    $state->status = 1;
                    $state->save();
                    $redirectTo = url('/admin/states?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('country_name'=>$e->getMessage())]);
        }
    }

    public function getStates(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $states = states($data['country']);
            $appenStates = '<option value>Please Select</option>';
            foreach($states as $state){
                $appenStates .= '<option value="'.$state->state_name.'">'.$state->state_name.'</option>';
            }
            return $appenStates;
        }
    }
}
