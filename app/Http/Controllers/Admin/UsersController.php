<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\Support\Facades\Route;
use App\User;
use App\Designation;
use App\Region;
use App\CustomerRegisterRequest;
use App\UserDepartmentRegion;
use App\UserDepartment;
use App\UserDepartmentProduct;
use App\UserCustomer;
use App\Customer;
use DB;
use Cookie;
use Session;
use Crypt;
use Illuminate\Support\Facades\Mail;
use Validator;
use Image;
use App\UserIncentive;
use App\UserRole;
use App\Module;
class UsersController extends Controller
{
    //
    public function users(Request $Request){
        Session::put('active','users'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = User::with(['shares','departments'=>function($query){
                $query->with('department');
            }])->where('type','!=','admin');
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            if(!empty($data['company_name'])){
                $querys = $querys->where('company_name','like','%'.$data['company_name'].'%');
            }
            if(!empty($data['email'])){
                $querys = $querys->where('email','like','%'.$data['email'].'%');
            }
            if (!empty($data['department'])) {
                $querys->whereHas('departments.department', function ($q) use ($data) {
                    $q->where('department', 'like', '%' . $data['department'] . '%');
                });
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                	->skip($iDisplayStart)->take($iDisplayLength)
                	->OrderBy('id','Desc')
                	->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            $rolesExtraPermission = \App\UserRole::checkExtraPermission(4,'roles');
            foreach($querys as $user){ 
                $departmentNames = [];
                if (!empty($user['departments'])) {
                    foreach ($user['departments'] as $dep) {
                        if (!empty($dep['department']['department'])) {
                            $departmentNames[] = $dep['department']['department'];
                        }
                    }
                }

                $departmentsCommaSeparated = implode(', ', $departmentNames);

                $checked='';
                if($user['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit User" class="btn btn-sm green margin-top-10" href="'.url('admin/add-edit-user/'.$user['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                if($rolesExtraPermission && $user['web_access'] =="Yes"){
                    $actionValues .='
                    <a title="Update Role" class="btn btn-sm yellow margin-top-10" href="'.url('admin/update-role/'.$user['id']).'"> <i class="fa fa-clock-o"></i>
                    </a>';
                }
                $num = ++$i;
                $records["data"][] = array(     
                    $num,
                    $user['name'],
                    $user['email'],
                    $departmentsCommaSeparated,
                    '<div style="text-align:center;">'.count($user['shares']).'</div>',
                     // Web Access column
                    '<div style="text-align:center; font-size: 20px;">' . 
                        ($user['web_access'] == 'Yes' 
                            ? '<span style="color:green;">&#10004;</span>' 
                            : '<span style="color:red;">&#10006;</span>') . 
                    '</div>',

                    // App Access column
                    '<div style="text-align:center; font-size: 20px;">' . 
                        ($user['app_access'] == 'Yes' 
                            ? '<span style="color:green;">&#10004;</span>' 
                            : '<span style="color:red;">&#10006;</span>') . 
                    '</div>',
                    '<div  id='.$user['id'].' rel=users class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Employees";
        return View::make('admin.users.users')->with(compact('title'));
    }

    public function addEditUser(Request $request,$userid=NULL){
        $selCusts =array();
        $selAppRoles = array();
        if(!empty($userid)){
            $empdata = User::with(['departments','shares'])->where('id',$userid)->first();
            $empdata = json_decode(json_encode($empdata),true);
            //echo "<pre>"; print_r($empdata); die;
            $selCusts = UserCustomer::where('user_id',$empdata['id'])->pluck('customer_id')->toArray(); 
            //echo "<pre>"; print_r($selCusts); die;
            $selProductTypes = explode(',',$empdata['product_types']);
            /*if(empty($empdata['product_types'])){
                $selProductTypes = array_filter($selProductTypes);
            }*/
            if(!empty($empdata['app_roles'])){
                $selAppRoles = explode(',',$empdata['app_roles']);
            }
            $title ="Edit User";
        }else{
            $title ="Add User";
            $empdata =array();
            $selProductTypes = array();
        }
        //echo "<pre>"; print_r($selProductTypes); die;
        return view('admin.users.add-edit-user')->with(compact('title','empdata','selCusts','selProductTypes','selAppRoles'));
    }

    public function saveUser(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                /*echo "<pre>"; print_r($data); die;*/
                if($data['employeeid']==""){
                    $type ="add";
                    $emailunique = "unique:users,email";
                    $mobileunique = "unique:users,mobile";
                    $pwdValidation = "bail|required|min:6";
                }else{ 
                    $type ="update";
                    $emailunique = "unique:users,email,".$data['employeeid'];
                    $mobileunique = "unique:users,mobile,".$data['employeeid'];
                    $pwdValidation = "bail|min:6";
                }
                $validator = Validator::make($request->all(), [
                        'name'    => 'bail|required',
                        'email'   => 'bail|required|email|'.$emailunique,
                        'mobile'  => 'bail|required|digits:10|numeric|'.$mobileunique,
                        'emergency_contact_person_mobile'  => 'bail|digits:10|numeric',
                        //'gender'  => 'bail|required',
                        //'correspondence_address'  => 'bail|required',
                        //'permanent_address'  => 'bail|required',
                        //'aadhar'  => 'bail|required',
                        //'salary_account_no'  => 'bail|required',
                        'dob'     => 'bail|required|date_format:Y-m-d',
                        //'joining_date'     => 'bail|required|date_format:Y-m-d',
                        //'joining_type' =>'bail|required',
                        //'permanent_from'      => 'required_if:joining_type,==,Permanent|nullable|date_format:Y-m-d',
                        'password' => $pwdValidation
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    //echo "<pre>"; print_r($data); die;
                    if(!isset($data['user_depts'])){
                        return response()->json(['status'=>false,'errors'=>array('user_depts'=> ['Please add atleast one user department'])]);
                    }
                    DB::beginTransaction();
                    if($type =="add"){
                        $user = new User; 
                    }else{
                        $user = User::find($data['employeeid']); 
                    }
                    $user->product_types = implode(',',$data['product_types']);
                    $user->name = $data['name'];
                    $user->email = $data['email'];
                    $user->designation = $data['designation'];
                    $user->type = "employee";
                    $user->correspondence_address = $data['correspondence_address'];
                    $user->permanent_address = $data['permanent_address'];
                    $user->aadhar = $data['aadhar'];
                    $user->emergency_contact_person = $data['emergency_contact_person'];
                    $user->home_landline_no = $data['home_landline_no'];
                    $user->aadhar = $data['aadhar'];
                    $user->emergency_contact_person_mobile = $data['emergency_contact_person_mobile'];
                    //$user->joining_type = $data['joining_type'];
                    $user->gender = $data['gender'];
                    $user->dob = $data['dob'];
                    $user->joining_date = $data['joining_date'];
                    /*if(!empty($data['permanent_from'])){
                        $user->permanent_from = $data['permanent_from'];
                    }*/
                    $user->mobile = $data['mobile'];
                    $user->status = $data['status'];
                    $user->pan = $data['pan'];
                    $user->show_class = $data['show_class'];
                    $user->show_weightage = $data['show_weightage'];
                    $user->driving_license = $data['driving_license'];
                    $user->salary_account_no = $data['salary_account_no'];
                    $user->web_access = $data['web_access'];
                    $user->app_access = $data['app_access'];
                    if(!empty($data['password'])){
                        $user->password = bcrypt($data['password']);
                    }
                    if($request->hasFile('image')){
                        $file = $request->file('image');
                        $img = Image::make($file);
                        $destination = public_path('/images/AdminImages/');
                        $ext = $file->getClientOriginalExtension();
                        $mainFilename = "profile".uniqid().time().".".$ext;
                        $img->save($destination.$mainFilename);
                        $user->image = $mainFilename;
                    }else{
                        if($data['gender'] =="Male"){
                            $user->image = 'male.jpg';
                        }else{
                            $user->image = 'female.png';
                        }
                    }

                    $proofsArr = array('aadhar_proof','driving_license_proof','pan_proof');
                    foreach ($proofsArr as $key => $proofFile) {
                        if($request->hasFile($proofFile)){
                            $file = $request->file($proofFile);
                            $img = Image::make($file);
                            $destination = public_path('/images/UserProofs/');
                            $ext = $file->getClientOriginalExtension();
                            $mainFilename = $proofFile.uniqid().time().".".$ext;
                            $img->save($destination.$mainFilename);
                            $user->$proofFile = $mainFilename;
                        }
                    }
                    $user->app_roles = "";
                    if(isset($data['app_roles']) && !empty($data['app_roles'])){
                        $user->app_roles = implode(',',$data['app_roles']);
                    }
                    $user->save();
                    //Delete User Depts
                    DB::table('user_departments')->where('user_id',$user->id)->delete();
                    DB::table('user_customers')->where('user_id',$user->id)->delete();
                    foreach ($data['user_depts'] as $key => $userDept) {
                        $userDeptArr = json_decode($userDept,true);
                        //echo "<pre>"; print_r($userDeptArr); die;
                        $userdept = new UserDepartment;
                        $userdept->user_id = $user->id;
                        $userdept->department_id  =   $userDeptArr['department_id'];
                        //$userdept->designation_id =   $userDeptArr['designation_id']; 
                        $userdept->designation_id =   NULL; 
                        $userdept->report_to      =   $userDeptArr['report_to']; 
                        $userdept->save();
                        if(!empty($userDeptArr['dept_regions'])){
                            foreach($userDeptArr['dept_regions'] as $userRegion){
                                $explodeRegion =  explode('#', $userRegion);
                                $saveUserRegion = new UserDepartmentRegion;
                                $saveUserRegion->user_id = $user->id;
                                $saveUserRegion->user_department_id = $userdept->id;
                                $saveUserRegion->region_id = $explodeRegion[0];
                                $saveUserRegion->sub_region_id = $explodeRegion[1];
                                $saveUserRegion->save();
                            }
                        }
                        if(!empty($userDeptArr['products'])){
                            foreach($userDeptArr['products'] as $productInfo){
                                $saveUserPro = new UserDepartmentProduct;
                                $saveUserPro->user_id = $user->id;
                                $saveUserPro->user_department_id = $userdept->id;
                                $saveUserPro->product_id = $productInfo;
                                $saveUserPro->save();
                            }
                        }
                        if(!empty($userDeptArr['customer_ids'])){
                            foreach($userDeptArr['customer_ids'] as $custid){
                                //echo $custid; die;
                                $saveUserCustomer = new UserCustomer;
                                $saveUserCustomer->customer_id = $custid;
                                //$saveUserCustomer->designation_id = $userDeptArr['designation_id'];
                                $saveUserCustomer->designation_id = null;
                                $saveUserCustomer->user_id     = $user->id;
                                $saveUserCustomer->save();
                            }
                        }
                    }
                    /*if(!isset($data['customers'])){
                        $data['customers'] = array(); 
                    }
                    if($type =="add"){
                        $user->customers()->attach($data['customers']); 
                    }else{
                        $user->customers()->sync($data['customers']);  
                    }*/
                    DB::commit();
                    $redirectTo = url('/admin/users?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('password'=>$e->getMessage())]);
        }
    }

    public function openUserDeptModal(Request $request){
        if($request->ajax()){
            $data = $request->all();
            return response()->json([
                'view' => (String)View::make('admin.users.user-dept-modal'),
            ]); 
        }
    }

    public function appendDesignationInfo(Request $request){
        if($request->ajax()){
            $data = $request->all();
           // $designationids = Designation::getReportDesignations($data['designation']);
            //$getDesignationDetails = DB::table('designations')->where('id',$data['designation'])->first();
            //$userids = UserDepartment::wherein('designation_id',$designationids)->pluck('user_id')->toArray();
            //$userids = array_merge(array(1),$userids);
            $departmentInfo = \App\Department::where('id',$data['department'])->first();
            $getDesignationDetails =  new \stdClass();
            $getDesignationDetails->type  = "";
            if($departmentInfo->department == "Marketing"){
                $getDesignationDetails->type  ="region";
                $getDesignationDetails->multiple_region  =1;
                $getDesignationDetails->multiple_sub_region  =1;
                $getDesignationDetails->having_customer  =0;
            }
            $reportingUsers  = DB::table('users')->select('id','name','designation')->where('status',1);
            if(!empty($data['empid'])){
                $reportingUsers = $reportingUsers->where('id','!=',$data['empid']);
            }
            $reportingUsers = $reportingUsers->get();
            $reportingUsers =  json_decode(json_encode($reportingUsers),true);
            return response()->json([
                'view' => (String)View::make('admin.users.append-designation-info')->with(compact('getDesignationDetails','reportingUsers')),
            ]);
        }
    }

    public function getSubRegions(Request $request){
        if($request->ajax()){
            $data = $request->all();
            if(is_array($data['regions'])){
                $regions[] = $data['regions'];
                $regions = array_flatten($regions);
            }else{
                $regions[0] = $data['regions'];
            }
            $getSubRegions = DB::table('regions')->wherein('parent_id',$regions)->get();
            $getSubRegions = json_decode(json_encode($getSubRegions),true);
            $appendSubRegions ="";
            foreach($getSubRegions as $subRegion){
                $appendSubRegions .= '<option value="'.$subRegion['id'].'">'.$subRegion['region'].'</option>';
            }
            return $appendSubRegions;
        }
    }

    public function addUserDeptDesignation(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $data['department'] = $data['department_id'];
            $resp = UserDepartment::userdeptinfo($data);
            //$designationInfo = $resp['designationInfo'];
            $designationInfo = array();
            $departmentInfo = $resp['departmentInfo'];
            $reportToInfo = $resp['reportToInfo'];
            $subRegions = $resp['subRegions'];
            $products = $resp['products'];
            $customers = $resp['customers'];
            return response()->json([
                'view' => (String)View::make('admin.users.user-dept-list')->with(compact('departmentInfo','designationInfo','reportToInfo','subRegions','products','customers')),
            ]);
        }
    }

    public function appendCustomers(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $customers = array();
            $appendCusts ="";
            if(isset($data['subRegions']) && !empty($data['subRegions'])){
                $getCities = DB::table('region_cities')->wherein('region_id',$data['subRegions'])->pluck('city')->toArray();
                $custIds = DB::table('customer_cities')->wherein('city_name',$getCities)->pluck('customer_id')->toArray();
                $customers = DB::table('customers')->select('id','name')->wherein('id',$custIds)->get();
                $customers = json_decode(json_encode($customers),true);
                foreach($customers as $customer){
                    $appendCusts .= '<option value="'.$customer['id'].'">'.$customer['name'].'</option>';
                }
                return $appendCusts;
            }else{
                return $appendCusts;
            }
        }
    }

    public function userIncentives(Request $Request){
        Session::put('active','userincentives');
        $users = UserIncentive::join('users','users.id','=','user_incentives.user_id')->groupby('users.id')->pluck('users.name','users.id')->toArray();
        //echo "<pre>"; print_r($users); die;
        /*if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = UserIncentive::join('users','users.id','=','user_incentives.user_id')->select('user_incentives.*','users.name');
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('user_incentives.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $userincentive){ 
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-user-incentive/'.$userincentive['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a style="display:none;" title="Clone" class="btn btn-sm yellow margin-top-10" href="'.url('/admin/add-edit-user-incentive/?type=clone&id='.$userincentive['id']).'"> <i class="fa fa-plus"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $userincentive['id'],
                    $userincentive['name'],
                    date('M Y',strtotime($userincentive['start_date'])),
                    "Rs. " .$userincentive['range_from'],
                    "Rs. " .$userincentive['range_to'],
                    $userincentive['discount']."%", 
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }*/
        $title = "Employee Incentive";
        return View::make('admin.users.user-incentives')->with(compact('title','users'));
    }

    public function addEditUserIncentive(Request $request,$userincentiveid=NULL){
        $getLastDiscount = array();
        if(!empty($userincentiveid)){
            $userincentivedata = UserIncentive::join('users','users.id','=','user_incentives.user_id')->select('user_incentives.*','users.name')->where('user_incentives.id',$userincentiveid)->first();

            $title ="Edit Employee Incentive";
        }else{
            $title ="Add Employee Incentive";
            $userincentivedata =array();
        }
        $userids = DB::table('user_departments')->where('department_id',2)->pluck('user_id')->toArray();
        $users = DB::table('users')->wherein('id',$userids)->get();
        $users = json_decode(json_encode($users),true);
        return view('admin.users.add-edit-user-incentive')->with(compact('title','userincentivedata','getLastDiscount','users','userincentiveid'));
    }

    public function saveUserIncentive(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['userincentiveid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                    'user_id' => 'bail|required',
                    /*'start_date' => 'bail|required|date_format:Y-m-d',*/
                    'range_from' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'range_to' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gt:'.$data['range_from'],
                    'discount' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gt:0|lte:99',     
                ]);
                if($validator->passes()) {
                    $data = $request->all();
                    unset($data['_token']);
                    if($type =="add"){
                        $userincentive = new UserIncentive; 
                    }else{
                        $userincentive = UserIncentive::find($data['userincentiveid']);
                        //Update Next Slot
                        /*$getNextDis = UserIncentive::whereDate('start_date',$data['start_date'])->where('end_date',$data['end_date'])->where('designation_id',$data['designation_id'])->where('range_from',$userincentive->range_to +1)->first();
                        $getNextDis = json_decode(json_encode($getNextDis),true);
                        if($getNextDis){
                            if($getNextDis['range_to'] > ($data['range_to'] +1) ){
                                DB::table('user_incentives')->where('id',$getNextDis['id'])->update(['range_from'=>$data['range_to']+1]);
                            }else{
                                return response()->json(['status'=>false,'errors'=>array('range_to'=>array('Range To value cannot be updated becuase its exceeded for next slot'))]);
                            }
                        }*/
                    }
                    $userincentive->user_id = $data['user_id'];
                    $userincentive->month = $data['month'];
                    $userincentive->year = $data['year'];
                    $start_date = $data['year'].'-'.$data['month']."-01";
                    $userincentive->start_date = date('Y-m-d',strtotime($start_date));
                    $userincentive->range_from = $data['range_from'];
                    $userincentive->range_to   = $data['range_to'];
                    $userincentive->discount   = $data['discount'];
                    $userincentive->save();
                    $redirectTo = url('/admin/user-incentives?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }
    }

    public function deleteUserIncentive($incentiveid){
        UserIncentive::where('id',$incentiveid)->delete();
        return redirect::back()->with('flash_message_success','Record has been deleted successfully');
    }


    public function updateRole(Request $request, $userid)
    {
        $rolesExtraPermission = \App\UserRole::checkExtraPermission(4,'roles');
        if(!$rolesExtraPermission){
            return redirect('/admin/dashboard')->with('flash_message_error','You have no right to access this functionality');
        }

        $getModules = Module::where('shown_in_roles', '1')->where('status', 1)->get();
        $getModules = json_decode(json_encode($getModules), true);

        $getRoleDetails = UserRole::where('user_id', $userid)->get();

        if ($request->isMethod('post')) {
            $data = $request->all();

            foreach ($data['module_id'] as $moduleId => $moduleData) {
                $userRole = UserRole::where('user_id', $userid)
                    ->where('module_id', $moduleId)
                    ->first();

                if (!$userRole) {
                    $userRole = new UserRole;
                    $userRole->user_id = $userid;
                    $userRole->module_id = $moduleId;
                }

                // Set fixed access fields (view/edit/delete)
                $userRole->view_access = isset($moduleData['view_access']) ? 1 : 0;
                $userRole->edit_access = isset($moduleData['edit_access']) ? 1 : 0;
                $userRole->delete_access = isset($moduleData['delete_access']) ? 1 : 0;

                // Handle extra_permissions as JSON
                if (isset($moduleData['extra_permissions']) && is_array($moduleData['extra_permissions'])) {
                    // Filter extra_permissions so only keys with value 1 remain (checked)
                    $filteredExtras = [];
                    foreach ($moduleData['extra_permissions'] as $key => $value) {
                        // Treat checked as 1, unchecked might be missing or 0
                        $filteredExtras[$key] = ($value == 1 || $value === '1') ? 1 : 0;
                    }
                    // Save as JSON string
                    $userRole->extra_permissions = json_encode($filteredExtras);
                } else {
                    // No extra permissions selected, save null or empty JSON
                    $userRole->extra_permissions = null;
                }

                $userRole->save();
            }

            // Update inventory, IHP, OSP access arrays (implode to comma-separated strings)
            $view_inventory_access = isset($data['view_inventory_access']) ? implode(',', $data['view_inventory_access']) : '';
            $update_inventory_access = isset($data['update_inventory_access']) ? implode(',', $data['update_inventory_access']) : '';
            $view_ihp_access = isset($data['view_ihp_access']) ? implode(',', $data['view_ihp_access']) : '';
            $update_ihp_access = isset($data['update_ihp_access']) ? implode(',', $data['update_ihp_access']) : '';
            $view_osp_access = isset($data['view_osp_access']) ? implode(',', $data['view_osp_access']) : '';
            $update_osp_access = isset($data['update_osp_access']) ? implode(',', $data['update_osp_access']) : '';

            User::where('id', $userid)->update([
                'view_inventory_access' => $view_inventory_access,
                'update_inventory_access' => $update_inventory_access,
                'view_ihp_access' => $view_ihp_access,
                'update_ihp_access' => $update_ihp_access,
                'view_osp_access' => $view_osp_access,
                'update_osp_access' => $update_osp_access,
            ]);

            return redirect()->back()->with('flash_message_success', 'Employee Roles Updated Successfully!');
        }

        $title = "Update Employee Role";
        $userinfo = User::where('id', $userid)->first();
        $userinfo = json_decode(json_encode($userinfo), true);

        return view('admin.users.update-roles')->with(compact('title', 'userid', 'getRoleDetails', 'getModules', 'userinfo'));
    }


    public function customerRegisterRequests(Request $Request){
        Session::put('active','customerRegisterRequests'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = CustomerRegisterRequest::with('creator');
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            if(!empty($data['city'])){
                $querys = $querys->where('cities','like','%'.$data['city'].'%');
            }
            if(!empty($data['linked_executive']) && $data['linked_executive'] !="All"){
                $querys = $querys->where('created_by',$data['linked_executive']);
            }
            if(!empty($data['status']) && $data['status'] !="All"){
                $querys = $querys->where('status','like','%'.$data['status'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('customer_register_requests.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $regis_req){ 
                $actionValues = '
                    <a title="View Details" class="btn btn-sm blue margin-top-10 view-details-btn" href="javascript:;" data-id="'.$regis_req['id'].'">View</a>';

                if ($regis_req['status'] == "Pending") {
                    if ($regis_req['is_verify'] == 1) {
                        $actionValues .= '
                        <a title="Create Customer" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-customer?empref='.$regis_req['id']).'">Add</a>';
                    } else {
                        $actionValues .= '
                        <button title="Verify First" class="btn btn-sm green margin-top-10" disabled>Add</button>';
                    }

                    $actionValues .= '
                    <a 
    title="Close Request" 
    class="btn btn-sm red margin-top-10 open-close-modal-btn" 
    href="javascript:;" 
    data-id='.$regis_req['id'].'
>Close</a>';
                }

                $status = ucwords($regis_req['status']);

$verifiedIcon = $regis_req['is_verify'] == 1 ? ' <span class="text-success">(✔)</span>' : '';

switch ($regis_req['status']) {
    case 'Pending':
        $statusText = '<b><span class="text-danger">' . $regis_req['status'] . $verifiedIcon . '</span></b>';
        break;
    case 'Closed':
        $statusText = '<b><span>' . $regis_req['status']. '</span></b>';
        break;
    case 'Added':
        $statusText = '<b><span class="text-success">' . $regis_req['status'] . $verifiedIcon . '</span></b>';
        break;
    default:
        $statusText = '<span>' . $regis_req['status'] . $verifiedIcon . '</span>';
        break;
}


                $num = ++$i;
                $records["data"][] = array(  
                    date('d F, Y', strtotime($regis_req['created_at'])),
                    ucwords($regis_req['creator']['name']),  
                    ucwords($regis_req['name']), 
                    ucwords($regis_req['cities']),  
                    $statusText,  
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Customer Register Requests";
        $executives = \App\User::where('status',1)->where('type','!=','admin')->get();
        return View::make('admin.users.customer-register-requests')->with(compact('title','executives'));
    }

    public function closeCustomerRegisterRequest(Request $request, $id)
    {
        $request->validate([
            'close_remarks' => 'required|string|max:255',
        ]);

        $regisRequest = CustomerRegisterRequest::findOrFail($id);
        $regisRequest->status = 'Closed';
        $regisRequest->close_remarks = $request->close_remarks;
        $regisRequest->closed_by = auth()->user()->id;
        $regisRequest->save();

        return response()->json(['message' => 'Request closed successfully']);
    }


    public function showCustomerRegisterRequestDetails($id){
        $request = CustomerRegisterRequest::with(['creator', 'dealer', 'linkedExecutive'])->find($id);
        
        return view('admin.users.customer-register-request-details', compact('request'));
    }

    public function verifyCustomerRequest(Request $request, $id)
    {
        $customerRequest = CustomerRegisterRequest::findOrFail($id);

        if ($customerRequest->is_verify == 1 || in_array($customerRequest->status, ['Closed', 'Added'])) {
            return response()->json(['message' => 'Cannot verify this request.'], 403);
        }

        $request->validate([
            //'verify_remarks' => 'required|string|max:255',
        ]);

        $customerRequest->is_verify = 1;
        $customerRequest->verified_by = auth()->user()->id;
        $customerRequest->verify_remarks = $request->verify_remarks;
        $customerRequest->save();

        return response()->json(['message' => 'Verified successfully.']);
    }

}
