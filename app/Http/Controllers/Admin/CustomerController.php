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
use App\RegisterRequest;
use App\Product;
use App\CustomerRegisterRequest;
use App\Customer;
use App\CustomerDiscount;
use App\UserCustomerShare;
use App\CustomerCity;
use App\CustomerEmployee;
use App\Discount;
use Validator;
class CustomerController extends Controller
{
    //
    public function customers(Request $Request){
        Session::put('active','customers'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Customer::with(['cities','dealer','user_customer_shares']);
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            if (!empty($data['city_name'])) {
                $querys->whereHas('cities', function($q) use ($data) {
                    $q->where('city_name', 'like', '%' . $data['city_name'] . '%');
                });
            }
            if(!empty($data['email'])){
                $querys = $querys->where('email','like','%'.$data['email'].'%');
            }
            if(!empty($data['category'])){
                $querys = $querys->where('category','like','%'.$data['category'].'%');
            }
            if(!empty($data['mobile'])){
                $querys = $querys->where('mobile',$data['mobile']);
            }

            if(!empty($data['business_linking'])){

                if($data['business_linking'] == "Open" || $data['business_linking'] == "Direct Customer"){
                    $querys = $querys->where('business_model',$data['business_linking']);
                }elseif(is_numeric($data['business_linking'])){
                    $querys = $querys->where('dealer_id',$data['business_linking']);
                }
            }

            if (!empty($data['linked_executive'])) {
                $querys->whereHas('user_customer_shares.user', function ($q) use ($data) {
                    $q->where('name', 'like', '%' . $data['linked_executive'] . '%');
                });
            }

            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('customers.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $customer){ 
                $checked='';
                if($customer['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-customer/'.$customer['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                if (isset($customer['dealer']['business_name'])) {
                    $dealer_name = $customer['dealer']['business_name'];
                } else {
                    if ($customer['business_model'] == 'Open') {
                        $dealer_name = '<span style="color: red; font-weight: bold;">(' . $customer['business_model'] . ')</span>';
                    } elseif ($customer['business_model'] == 'Direct Customer') {
                        $dealer_name = '<span style="color: green; font-weight: bold;">(' . $customer['business_model'] . ')</span>';
                    } else {
                        $dealer_name = '(' . $customer['business_model'] . ')';
                    }
                }
                $cities = implode(',',array_column($customer['cities'], 'city_name'));
                $linkedExecutives = [];

                foreach ($customer['user_customer_shares'] as $share) {
                    if (isset($share['user']['name'])) {
                        $linkedExecutives[] = $share['user']['name'];
                    }
                }
                $records["data"][] = array(      
                    $customer['id'],
                    //$customer['category'],
                    ucwords($customer['name']),
                    $cities,
                    $dealer_name,
                    implode(', ', $linkedExecutives),
                    '<div  id="'.$customer['id'].'" rel="customers" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Customers";
        $linkedDealers = \App\Dealer::where('status',1)->where('parent_id',NULL)->select('id','business_name')->get();
        return View::make('admin.customers.customers')->with(compact('title','linkedDealers'));
    }

    public function addEditCustomer(Request $request,$customerid=NULL){
        $userids = \DB::table('user_departments')->where('department_id',2)->pluck('user_id')->toArray();
        $users  = \DB::table('users')->wherein('id',$userids)->get();
        $users = json_decode(json_encode($users),true);
        $selCities = array();
        $customerDiscounts = array();
        $existingNetProducts = array();
    	if(!empty($customerid)){
    		$customerdata = Customer::with(['cities','discounts','employees'=>function($query){
                $query->where('is_delete',0);
            },'user_customer_shares'])->where('id',$customerid)->first();
            $customerdata = json_decode(json_encode($customerdata),true);
            $selCities = array_column($customerdata['cities'], 'city_name');
            $customerDiscounts = CustomerDiscount::where('customer_id',$customerid)->where('discount_type','discounts')->get();
            $existingNetProducts = CustomerDiscount::where('customer_id', $customerid)
            ->where('discount_type', 'net_products')
            ->join('products', 'products.id', '=', 'customer_discounts.product_id') // Assuming the relationship is via product_id
            ->select('customer_discounts.*', 'products.product_name', 'products.is_trader_product as product_type')
            ->get();
            //echo "<pre>"; print_r(json_decode(json_encode($customerDiscounts),true)); die;
    		$title ="Edit Customer";
    	}else{
    		$title ="Add Customer";
	    	$customerdata =array();
    	}
        if(isset($_GET['ref'])){
            $customerdata = RegisterRequest::select('business_name as name','city','name as contact_person_name','email','mobile','city')->where('id',$_GET['ref'])->first();
            $customerdata = json_decode(json_encode($customerdata),true);
            $selCities = array(strtoupper($customerdata['city']));
        }
        if(isset($_GET['empref'])){
            $registerRequestData = CustomerRegisterRequest::where('id',$_GET['empref'])->first();
            $registerRequestData = json_decode(json_encode($registerRequestData),true);
            $customerdata['name'] = $registerRequestData['name'];
            $customerdata['address'] = $registerRequestData['address'];
            $customerdata['activity'] = $registerRequestData['activity'];
            $customerdata['contact_person_name'] = $registerRequestData['contact_person_name'];
            $customerdata['designation'] = $registerRequestData['designation'];
            $customerdata['mobile'] = $registerRequestData['mobile'];
            $customerdata['email'] = $registerRequestData['email'];
            $customerdata['business_model'] = $registerRequestData['business_model'];
            $customerdata['customer_product_type'] = $registerRequestData['customer_product_type'];
            $customerdata['dealer_id'] = $registerRequestData['dealer_id'];
            $customerdata['linked_executive'] = $registerRequestData['linked_executive'];
            $customerdata['created_at'] = date('Y-m-d',strtotime($registerRequestData['created_at']));
            $selCities = array(strtoupper($registerRequestData['cities']));
        }

    	return view('admin.customers.add-edit-customer')->with(compact('title','customerdata','selCities','users','customerDiscounts','existingNetProducts'));
    }

    public function saveCustomer(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['customerid']==""){
                    $type ="add";
                    $emailunique = "unique:customers,email";
                    $mobileunique = "unique:customers,mobile";
                    $pwdValidation = "bail|required|min:6";
                }else{ 
                    $type ="update";
                    $emailunique = "unique:customers,email,".$data['customerid'];
                    $mobileunique = "unique:customers,mobile,".$data['customerid'];
                    $pwdValidation = "bail|min:6";
                }
                $validator = Validator::make($request->all(), [
                        'name'   =>  'bail|required',
                        'email'   => 'bail|nullable|email|'.$emailunique,
                        /*'password' => $pwdValidation,*/
                        'mobile'  => 'bail|required|numeric|digits:10|'.$mobileunique,
                        //'category'  => 'bail|required',
                        //'address'  => 'bail|required',
                        'business_model'  => 'bail|required',
                        'dealer_id' => 'bail|required_if:business_model,==,Dealer|nullable',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();

                    // Validate if discount_products and net_products have the same product
                    $discountProducts = $data['discount_products'] ?? [];
                    $netProducts = $data['net_products'] ?? [];

                    // Extract product_ids from both arrays
                    $discountProductIds = array_column($discountProducts, 'product_id');
                    $netProductIds = array_column($netProducts, 'product_id');

                    // Check for intersection between discount and net products
                    $commonProductIds = array_intersect($discountProductIds, $netProductIds);

                    if (!empty($commonProductIds)) {
                        // Fetch product names from the database using the common product IDs
                        $commonProducts = Product::whereIn('id', $commonProductIds)->pluck('product_name', 'id')->toArray();

                        // Return an error message if there's any duplicate product
                        return response()->json([
                            'status' => false,
                            'errors' => [
                                'linking_error' => [
                                    'The following products cannot be in both Discount and Net Price sections: ' . implode(', ', $commonProducts)
                                ]
                            ]
                        ]);
                    }
                    //echo "<pre>"; print_r($data); die;
                    unset($data['_token']);
                    DB::beginTransaction();
                    if($type =="add"){
                        $customer = new Customer; 
                    }else{
                        $customer = Customer::find($data['customerid']); 
                    }
                    /*if(!empty($data['password'])){
                        $data['password'] = bcrypt($data['password']);
                        $customer->password = $data['password'];
                    }*/
                    $customer->customer_product_type = $data['customer_product_type'];
                    $customer->name = $data['name'];
                    $customer->contact_person_name = $data['contact_person_name'];
                    $customer->designation = $data['designation'];
                    //$customer->category = $data['category'];
                    $customer->business_model = $data['business_model'];
                    $customer->address = $data['address'];
                    if(isset($data['activity'])){
                        $customer->activity = implode(',',$data['activity']);
                    }else{
                        $customer->activity = "";
                    }
                    $customer->mobile = $data['mobile'];
                    $customer->email = $data['email'];
                    $customer->status = $data['status'];
                    if(!empty($data['dealer_id'])){
                        $customer->dealer_id =  $data['dealer_id'];
                    }
                    $customer->payment_term_type ="";
                    if($data['business_model'] =="Open"){
                        $customer->dealer_id = NULL;
                    }elseif($data['business_model'] =="Direct Customer"){
                        $customer->dealer_id = NULL;
                        $customer->payment_term_type = 'On Bill';
                        $customer->payment_term = $data['direct_customer_payment_term'];
                        //echo "<pre>"; print_r($data); die;
                        //$customer->payment_discount = $data['payment_discount'];
                    }
                    //$customer->is_spsod = $data['is_spsod'];
                    //$customer->is_monthly_turnover_discount = $data['is_monthly_turnover_discount'];
                    //$customer->custom_spsod_from = $data['custom_spsod_from'];
                    //$customer->custom_spsod_to = $data['custom_spsod_to'];
                    //$customer->custom_spsod_discount = $data['custom_spsod_discount'];
                    $customer->custom_mtod_from = 0;
                    $customer->custom_mtod_to = 0;
                    $customer->custom_mtod_discount = 0;
                    /*if($data['is_monthly_turnover_discount'] =="no"){
                        $customer->custom_mtod_from = $data['custom_mtod_from'];
                        $customer->custom_mtod_to = $data['custom_mtod_to'];
                        $customer->custom_mtod_discount = $data['custom_mtod_discount'];
                    }*/
                    $customer->save();
                    DB::table('customer_discounts')->where('customer_id', $customer->id)->delete();

                    if (isset($data['discount_products'])) {
                        foreach ($data['discount_products'] as $customeDisInfo) {
                            $custDis = new CustomerDiscount();
                            $custDis->customer_id = $customer->id;
                            $custDis->discount_type = 'discounts'; // Set manually or use form field if available
                            
                            if (!empty($customeDisInfo['product_id'])) {
                                $custDis->product_id = $customeDisInfo['product_id'];
                            }
                            if (!empty($customeDisInfo['min_qty'])) {
                                $custDis->min_qty = $customeDisInfo['min_qty']; // min_qty maps to min_qty
                            }
                            if (!empty($customeDisInfo['moq'])) {
                                $custDis->moq = $customeDisInfo['moq']; // min_qty maps to min_qty
                            }
                            if (!empty($customeDisInfo['discount'])) {
                                $custDis->discount = $customeDisInfo['discount'];
                            }
                            if (!empty($customeDisInfo['special_discount'])) {
                                $custDis->special_discount = $customeDisInfo['special_discount'];
                            }
                            $custDis->save();
                        }
                    }

                    if (isset($data['net_products'])) {
                        foreach ($data['net_products'] as $netProdInfo) {
                            $custDis = new CustomerDiscount();
                            $custDis->customer_id = $customer->id;
                            $custDis->discount_type = 'net_products'; // Set manually or use form field if available
                            
                            if (!empty($netProdInfo['product_id'])) {
                                $custDis->product_id = $netProdInfo['product_id'];
                            }
                            if (!empty($netProdInfo['net_price'])) {
                                $custDis->net_price = $netProdInfo['net_price'];
                            }
                            if (!empty($netProdInfo['moq'])) {
                                $custDis->moq = $netProdInfo['moq'];
                            }
                            $custDis->save();
                        }
                    }

                    /*DB::table('customer_discounts')->where('customer_id',$customer->id)->delete();
                    if(isset($data['customer_discounts'])){
                        foreach($data['customer_discounts'] as $customeDisInfo){
                            $customeDisInfo = json_decode($customeDisInfo,true);
                            $custDis = new CustomerDiscount;
                            $custDis->customer_id = $customer->id; 
                            $custDis->discount_type = $customeDisInfo['discount_type']; 
                            //$custDis->company_share = $customeDisInfo['company_share'];  
                            //$custDis->dealer_share = $customeDisInfo['dealer_share'];  
                            if(!empty($customeDisInfo['product_id'])){
                                $custDis->product_id = $customeDisInfo['product_id'];  
                            }
                            if(!empty($customeDisInfo['from_qty'])){
                                $custDis->from_qty = $customeDisInfo['from_qty'];  
                            }
                            if(!empty($customeDisInfo['to_qty'])){
                                $custDis->to_qty = $customeDisInfo['to_qty'];  
                            }
                            if(!empty($customeDisInfo['discount'])){
                                $custDis->discount = $customeDisInfo['discount'];  
                            }
                            $custDis->save();
                        }
                    }*/
                    DB::table('customer_cities')->where('customer_id',$customer->id)->delete();
                    foreach($data['cities'] as $cityInfo){
                        $custCity = new CustomerCity;
                        $custCity->customer_id = $customer->id;
                        $custCity->city_name = $cityInfo;
                        $custCity->save();
                    }
                    /*CustomerEmployee::where('customer_id',$customer->id)->delete();*/
                    if(isset($data['names'])){
                        foreach ($data['names'] as $nkey => $custEmp) {
                            if(isset($data['cust_emp_id'][$nkey]) && !empty($data['cust_emp_id'][$nkey])){
                                $savecustEmp = CustomerEmployee::find($data['cust_emp_id'][$nkey]);
                                if(isset($data['is_delete'][$nkey])){
                                    $savecustEmp->is_delete = $data['is_delete'][$nkey];
                                }
                            }else{
                                $savecustEmp = new CustomerEmployee;
                                $savecustEmp->customer_id = $customer->id;
                            }
                            $savecustEmp->name = $custEmp;
                            $savecustEmp->mobile = $data['mobiles'][$nkey];
                            $savecustEmp->email = $data['emails'][$nkey];
                            $savecustEmp->designation = $data['designations'][$nkey];
                            /*$savecustEmp->password    = bcrypt($data['passwords'][$nkey]);
                            $savecustEmp->decrypt_password = $data['passwords'][$nkey];*/
                            $savecustEmp->save();
                        }
                    }
                    UserCustomerShare::where('customer_id',$customer->id)->delete();
                    if(isset($data['marketing_user_ids'])){
                        foreach($data['marketing_user_ids'] as $ukey=> $marketuser){
                            $user_cust_share = new UserCustomerShare;
                            $user_cust_share->user_id = $marketuser;
                            $user_cust_share->customer_id = $customer->id;
                            $user_cust_share->share = 100;
                            $user_cust_share->user_date = $data['user_dates'][$ukey];
                            //$user_cust_share->average_sales = $data['average_sales'][$ukey];
                            $user_cust_share->save();
                        }
                    }
                    if(isset($data['register_request_id'])){
                        RegisterRequest::where('id',$data['register_request_id'])->delete();
                    }

                    if(isset($data['customer_register_request_id'])){
                        CustomerRegisterRequest::where('id',$data['customer_register_request_id'])->update(['status'=>'Added']);
                    }
                    DB::commit();
                    $redirectTo = url('/admin/customers?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }
    }

    public function appedDiscountDetails(Request $request){
    	if($request->ajax()){
    		$data = $request->all();
    		return response()->json([
                'view' => (String)View::make('admin.customers.append-discount-details')->with(compact('data')),
            ]);
    	}
    }

    public function addCustomerDiscount(Request $request){
    	if($request->ajax()){
    		$validator = Validator::make($request->all(), [
	                'discount_type'     =>  'bail|required',
	                'product_id' => 'bail|required_if:discount_type,==,Product Base|nullable',
	                'from_qty' => 'bail|required_if:discount_type,==,Product Base|nullable',
                    'to_qty' => 'bail|required_if:discount_type,==,Product Base|nullable',
	                /*'company_share' =>  'bail|numeric|between:0,100',
	                'dealer_share' =>  'bail|numeric|between:0,100',*/
	            ]
	        );
	        if($validator->passes()) {
	        	$data = $request->all();
                if($data['discount_type'] == 'Turnover'){
                    $sumCustDealer = $data['company_share'] + $data['dealer_share'];
    	        	if($sumCustDealer ==100){
    	        		$discountinfo = $data;
    		            return response()->json([
    		            	'status' => true,
    		                'view' => (String)View::make('admin.customers.customer-discount-list')->with(compact('discountinfo')),
    		            ]);
    	        	}else{
    	        		return response()->json(['status'=>false,'errors'=>array('dealer_share'=> array('Sum of Customer and Dealer share must be 100%'))]);
    	        	}
                }else{
                    $discountinfo = $data;
                    return response()->json([
                        'status' => true,
                        'view' => (String)View::make('admin.customers.customer-discount-list')->with(compact('discountinfo')),
                    ]);
                }
	        }else{
                return response()->json(['status'=>false,'errors'=>$validator->messages()]);
            }
    	}
    }

    public function customerDiscounts(Request $Request){
        Session::put('active','customerdiscounts'); 
        $discounts = Discount::orderby('start_date','DESC')->groupby('start_date')->pluck('start_date')->toArray();
        //echo "<pre>"; print_r($discounts); die;
        /*if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Discount::query();
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('discounts.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $discount){ 
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-customer-discount/'.$discount['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a style="display:none;" title="Clone" class="btn btn-sm yellow margin-top-10" href="'.url('/admin/add-edit-customer-discount/?type=clone&id='.$discount['id']).'"> <i class="fa fa-plus"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $discount['id'],
                    date('M Y',strtotime($discount['start_date'])),
                    "Rs. " .$discount['range_from'],
                    "Rs. " .$discount['range_to'],
                    $discount['discount']."%", 
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }*/
        $title = "Turnover Discounts";
        return View::make('admin.customers.customer-discounts')->with(compact('title','discounts'));
    }

    public function addEditCustomerDiscount(Request $request,$discountid=NULL){
        $getLastDiscount = array();
        if(!empty($discountid)){
            $discountdata = Discount::where('id',$discountid)->first();
            $title ="Edit Turnover Discount";
        }else{
            /*if(isset($_GET['type']) && isset($_GET['id']) && $_GET['type'] =='clone'){
                $custDiscount =  Discount::find($_GET['id']);
                $getLastDiscount = Discount::whereDate('start_date',$custDiscount->start_date)->where('end_date',$custDiscount->end_date)->orderby('id','DESC')->first();
                $getLastDiscount = json_decode(json_encode($getLastDiscount),true);
            }*/
            $title ="Add Turnover Discount";
            $discountdata =array();
        }
        return view('admin.customers.add-edit-customer-discount')->with(compact('title','discountdata','getLastDiscount','discountid'));
    }

    public function saveCustomerDiscount(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['discountid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                    /*'start_date' => 'bail|required|date_format:Y-m-d',
                    'end_date' => 'bail|required|date_format:Y-m-d',*/
                    'range_from' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'range_to' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'discount' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gte:0|lte:99',     
                ]);
                if($validator->passes()) {
                    $data = $request->all();
                    unset($data['_token']);
                    if($type =="add"){
                        $discount = new Discount; 
                    }else{
                        $discount = Discount::find($data['discountid']);
                        //Update Next Slot
                        /*$getNextDis = Discount::whereDate('start_date',$data['start_date'])->where('end_date',$data['end_date'])->where('range_from',$discount->range_to +1)->first();
                        $getNextDis = json_decode(json_encode($getNextDis),true);
                        if($getNextDis){
                            if($getNextDis['range_to'] > ($data['range_to'] +1) ){
                                DB::table('discounts')->where('id',$getNextDis['id'])->update(['range_from'=>$data['range_to']+1]);
                            }else{
                                return response()->json(['status'=>false,'errors'=>array('range_to'=>array('Range To value cannot be updated becuase its exceeded for next slot'))]);
                            }
                        }*/
                    }
                    $discount->month = $data['month'];
                    $discount->year = $data['year'];
                    $start_date = $data['year'].'-'.$data['month']."-01";
                    $discount->start_date = date('Y-m-d',strtotime($start_date));
                    $discount->range_from = $data['range_from'];
                    $discount->range_to   = $data['range_to'];
                    $discount->discount   = $data['discount'];
                    $discount->save();
                    $redirectTo = url('/admin/customer-discounts?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }
    }

    public function deleteCustomerDiscount($disid){
        Discount::where('id',$disid)->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function appendMarketingUsers(Request $request){
        $userids = \DB::table('user_departments')->where('department_id',2)->pluck('user_id')->toArray();
        $users  = \DB::table('users')->wherein('id',$userids)->get();
        $users = json_decode(json_encode($users),true);
        return response()->json([
                'view' => (String)View::make('admin.customers.append-marketing-users')->with(compact('users')),
            ]);
    }

    public function registerRequests(Request $Request){
        Session::put('active','registerRequests'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = RegisterRequest::query();
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            if(!empty($data['business_name'])){
                $querys = $querys->where('business_name','like','%'.$data['business_name'].'%');
            }
            if(!empty($data['email'])){
                $querys = $querys->where('email','like','%'.$data['email'].'%');
            }
            if(!empty($data['mobile'])){
                $querys = $querys->where('mobile',$data['mobile']);
            }
            if(!empty($data['city'])){
                $querys = $querys->where('city',$data['city']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('register_requests.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $regis_req){ 
                $actionValues='
                    <a title="Create Customer" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-customer?ref='.$regis_req['id']).'"> Create Customer
                    </a>
                     <a title="Delete Request" class="btn btn-sm red margin-top-10" href="'.url('/admin/delete-register-request/'.$regis_req['id']).'"><i class="fa fa-times"></i></a>';
                $num = ++$i;
                $records["data"][] = array(      
                    ucwords($regis_req['business_name']),  
                    ucwords($regis_req['name']),  
                    ucwords($regis_req['email']),  
                    ucwords($regis_req['mobile']),  
                    ucwords($regis_req['city']),  
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Register Requests";
        return View::make('admin.customers.register-requests')->with(compact('title'));
    }

    public function deleteRegisterRequest($id){
        RegisterRequest::where('id',$id)->delete();
        return redirect()->back()->with('flash_message_success','Register request has been deleted successfully');
    }

    public function fetchProductsByType(Request $request)
    {
        $type = $request->get('type');

        $products = fetchProducts($type);

        return response()->json($products);
    }
}
