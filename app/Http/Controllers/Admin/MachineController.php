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
use App\Machine;
use Validator;
class MachineController extends Controller
{
    //
    public function machines(Request $Request){
        Session::put('active','machines'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Machine::query();
            if(!empty($data['machine_number'])){
                $querys = $querys->where('machine_number','like','%'.$data['machine_number'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('machines.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $machine){ 
                $checked='';
                if($machine['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit Machine" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-machine/'.$machine['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a onclick="return ConfirmDelete()"  title="Delete Machine" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-machine/'.$machine['id']).'"> <i class="fa fa-times"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $machine['id'],
                    $machine['machine_number'],
                    $machine['capacity'],
                    '<div  id="'.$machine['id'].'" rel="machines" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Machines";
        return View::make('admin.machines.machines')->with(compact('title'));
    }

    public function addEditMachine(Request $request,$machineid=NULL){
        if(!empty($machineid)){
            $machinedata = Machine::where('id',$machineid)->first();
            $title ="Edit Machine";
        }else{
            $title ="Add Machine";
            $machinedata =array();
        }
        return view('admin.machines.add-edit-machine')->with(compact('title','machinedata'));
    }

    public function saveMachine(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['machineid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'machine_number' => 'bail|required',
                        'capacity' => 'bail|required',
                        'status' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $machine = new Machine; 
                    }else{
                        $machine = Machine::find($data['machineid']); 
                    }
                    $machine->machine_number = $data['machine_number'];
                    $machine->capacity = $data['capacity'];
                    $machine->status = $data['status'];
                    $machine->save();
                    $redirectTo = url('/admin/machines');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('size'=>$e->getMessage())]);
        }
    }

    public function deleteMachine($machineid){
        Machine::where('id',$machineid)->delete();
        return redirect::to('/admin/machines')->with('flash_message_success','Machine has been deleted successfully');
    }
}
