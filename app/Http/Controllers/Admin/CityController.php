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
use App\Country;
use App\State;
use App\City;
use Validator;

class CityController extends Controller
{
    //
    public function cities(Request $Request){
        Session::put('active','cities'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = City::query();
            if(!empty($data['city_name'])){
                $querys = $querys->where('city_name','like','%'.$data['city_name'].'%');
            }
            if(!empty($data['state_name'])){
                $querys = $querys->where('state_name','like','%'.$data['state_name'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('cities.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $city){ 
                $checked='';
                if($city['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-city/'.$city['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $city['id'],
                    $city['city_name'],
                    $city['state_name'],
                    $city['country_name'],
                    '<div  id="'.$city['id'].'" rel="cities" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Cities";
        return View::make('admin.cities.cities')->with(compact('title'));
    }

    public function addEditCity(Request $request,$cityid=NULL){
    	if(!empty($cityid)){
    		$citydata = City::where('id',$cityid)->first();
    		$title ="Edit City";
    	}else{
    		$title ="Add City";
	    	$citydata =array();
    	}
    	return view('admin.cities.add-edit-city')->with(compact('title','citydata'));
    }

    public function saveCity(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['cityid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'country_name' => 'bail|required',
                        'state_name' => 'bail|required',
                        'city_name' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $city = new City; 
                    }else{
                        $city = City::find($data['cityid']); 
                    }
                    $city->country_name = $data['country_name'];
                    $city->state_name = $data['state_name'];
                    $city->city_name = $data['city_name'];
                    $city->status = 1;
                    $city->save();
                    $redirectTo = url('/admin/cities?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('city_name'=>$e->getMessage())]);
        }
    }

    public function getCities(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $cities = cities($data['state']);
            $appenCities = '<option value>Please Select</option>';
            foreach($cities as $city){
                $appenCities .= '<option value="'.$city->city_name.'">'.$city->city_name.'</option>';
            }
            return $appenCities;
        }
    }
}
