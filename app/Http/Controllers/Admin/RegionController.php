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
use App\Region;
use App\RegionState;
use App\RegionCity;
use Validator;
class RegionController extends Controller
{
    //
    public function regions(Request $Request){
        Session::put('active','regions'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Region::query();
            if(!empty($data['region'])){
                $querys = $querys->where('regions.region','like','%'.$data['region'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('regions.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $region){ 
                $checked='';
                if($region['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-region/'.$region['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $region['id'],
                    $region['region'],
                    getRegionParent($region['parent_id']),
                    '<div  id="'.$region['id'].'" rel="regions" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Regions";
        return View::make('admin.regions.regions')->with(compact('title'));
    }

    public function addEditRegion(Request $request,$regionid=NULL){
    	$SelStates = array();
    	$selCities = array();
    	$cities    = array();
    	if(!empty($regionid)){
    		$regiondata = Region::with(['states','cities'])->where('id',$regionid)->first();
    		$regiondata = json_decode(json_encode($regiondata),true);
    		$SelStates  = array_column($regiondata['states'], 'state');
    		$cities     = DB::table('cities')->wherein('state_name',$SelStates)->get();
    		$cities = json_decode(json_encode($cities),true);
    		$selCities = array_column($regiondata['cities'], 'city');
    		$title ="Edit Region";
    	}else{
    		$title ="Add Region";
	    	$regiondata =array();
    	}
    	return view('admin.regions.add-edit-region')->with(compact('title','regiondata','SelStates','selCities','cities'));
    }

    public function saveRegion(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['regionid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'region' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $region = new Region; 
                    }else{
                        $region = Region::find($data['regionid']); 
                    }
                    $region->parent_id = $data['parent_id'];
                    $region->region = $data['region'];
                    $region->status = 1;
                    $region->save();
                    if($data['parent_id'] !="ROOT"){
                    	DB::table('region_states')->where('region_id',$region->id)->delete();
                    	foreach ($data['states'] as $key => $state) {
                    		$regionState =  new RegionState;
                    		$regionState->region_id = $region->id;
                    		$regionState->state = $state;
                    		$regionState->save();
                    	}
                    }
                    if($data['parent_id'] !="ROOT"){
                    	DB::table('region_cities')->where('region_id',$region->id)->delete();
                    	foreach ($data['cities'] as $key => $city){
                    		$regionCity =  new RegionCity;
                    		$regionCity->region_id = $region->id;
                    		$regionCity->city = $city;
                    		$regionCity->state = getStateName($city);
                    		$regionCity->save();
                    	}
                    }
                    $redirectTo = url('/admin/regions?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('region'=>$e->getMessage())]);
        }
    }

    public function getStateCities(Request $request){
    if ($request->ajax()) {
        $data = $request->all();
        $cities = [];

        if (!empty($data['states'])) {
            $cities = DB::table('cities')
                        ->whereIn('state_name', $data['states'])
                        ->get();
            $cities = json_decode(json_encode($cities), true);
        }

        $selCities = $data['selCities'] ?? [];

        return response()->json([
            'view' => (string) view('admin.regions.region-cities')->with(compact('cities', 'selCities')),
        ]);
    }
}

}
