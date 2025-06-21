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
use Validator;

class CountryController extends Controller
{
    //
    public function countries(Request $Request){
        Session::put('active','countries'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Country::query();
            if(!empty($data['country_name'])){
                $querys = $querys->where('country_name','like','%'.$data['country_name'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('countries.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $country){
                $id= base64_encode(convert_uuencode($country['id'])); 
                $checked='';
                if($country['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-country/'.$country['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $country['id'],
                    $country['country_name'],
                    $country['sort'],
                    '<div  id="'.$country['id'].'" rel="categories" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Countries";
        return View::make('admin.countries.countries')->with(compact('title'));
    }

    public function addEditCountry(Request $request,$countryid=NULL){
    	if(!empty($countryid)){
    		$countrydata = Country::where('id',$countryid)->first();
    		$title ="Edit Country";
    	}else{
    		$title ="Add Country";
	    	$countrydata =array();
    	}
    	return view('admin.countries.add-edit-country')->with(compact('title','countrydata'));
    }

    public function saveCountry(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['countryid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'name' => 'bail|required',
                        'sort' => 'bail|required|integer|min:1'
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $country = new Country; 
                    }else{
                        $country = Country::find($data['countryid']); 
                    }
                    $country->country_name = $data['name'];
                    $country->sort = $data['sort'];
                    $country->status = 1;
                    $country->save();
                    $redirectTo = url('/admin/countries?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('password'=>$e->getMessage())]);
        }
    }
}
