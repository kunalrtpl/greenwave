<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Designation;
use App\Region;
use App\User;
use App\Product;
use App\Department;
use DB;
class UserDepartment extends Model
{
    //
    public function department(){
        return $this->belongsto('App\Department');
    }

	public function subregions(){
		return $this->hasMany('App\UserDepartmentRegion');
	}

    public function products(){
        return $this->hasMany('App\UserDepartmentProduct');
    }

    public static function userdeptinfo($data){
    	//$designationInfo = Designation::with('department')->where('id',$data['designation_id'])->first()->toArray();
        $departmentInfo = Department::where('id',$data['department'])->first()->toArray();
        //echo "<pre>"; print_r($departmentInfo); die;
        $reportToInfo = User::where('id',$data['report_to'])->select('id','name')->first()->toArray();
        $subRegions = array();
        $customers = array();
        if(isset($data['subregions'])){
            $subRegions = Region::with('parent_region')->wherein('id',$data['subregions'])->get()->toArray(); 
        }
        $products = array();
        if(isset($data['products']) && !empty($data['products'])){
            $products = Product::wherein('id',$data['products'])->select('id','product_code','product_name')->get()->toArray();
        }
        if(isset($data['customers']) && !empty($data['customers'])){
            $customers = Customer::wherein('id',$data['customers'])->select('id','name')->get()->toArray();
        }
        //return array('designationInfo'=>$designationInfo,'reportToInfo'=>$reportToInfo,'subRegions'=>$subRegions,'products'=>$products,'customers'=>$customers);
        return array('departmentInfo'=>$departmentInfo,'reportToInfo'=>$reportToInfo,'subRegions'=>$subRegions,'products'=>$products,'customers'=>$customers);
    }

    public static function custids($desgid,$userid){
        $getCustids = UserCustomer::where(['designation_id'=>$desgid,'user_id'=>$userid])->pluck('customer_id')->toArray();
        return $getCustids;
    }

    public static function getDesignInfo($userid){
        $details = UserDepartment::join('designations','designations.id','=','user_departments.designation_id')->where('user_departments.user_id',$userid)->pluck('designations.designation')->toArray();
        $details = implode(',',$details);
        return $details;
    }
}
