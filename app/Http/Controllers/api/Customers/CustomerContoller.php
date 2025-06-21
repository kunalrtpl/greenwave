<?php

namespace App\Http\Controllers\api\Customers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Customer;
use App\CustomerEmployee;
use App\AuthToken;
use App\PurchaseOrder;
use App\Feedback;
use DB;
use App\SaleInvoice;
use App\CustomerPurchaseReturn;
use App\DebitCreditEntry;
class CustomerContoller extends Controller
{
    //
	public function __construct(Request $request){
        if($request->header('Authorization')){
            $token = $request->header('Authorization');
            $resp = AuthToken::verifyUser($token);
            $this->resp = $resp;
        }
    }

    public function login(Request $request){
    	if($request->isMethod('post')){
    		$data = $request->all();
    		$rules = [
    			'step'     => 'bail|required|in:1,2',
    			'mobile'   => 'bail|required|numeric|digits:10',
    			'otp'      => 'required_if:step,==,2|nullable|numeric|digits:4',
	            'login_type'  =>  'required_if:step,==,2|nullable|in:customer-employee,customer',
	            'login_device'  =>  'required_if:step,==,2|nullable'
	        ];
	        $customMessages = [];
	        $validator = Validator::make($data,$rules,$customMessages);
	        if ($validator->fails()) {
			    return response()->json(validationResponse($validator),422); 
		    }
	        //Verify customer
	        if($data['step'] ==1){
				$customer = Customer::where(['mobile'=>$data['mobile']])->first();
				if($customer){
					$otp = 1234;
					$message = "OTP Sent successfully to your registered mobile number";
					$result['next_step_info']['mobile'] = $customer->mobile;
					$result['next_step_info']['type']   = 'customer';
					Customer::where('id',$customer->id)->update(['otp'=>$otp]);
					return response()->json(apiSuccessResponse($message,$result),200);
				}else{
					$customer = CustomerEmployee::where(['mobile'=>$data['mobile'],'is_delete'=>0])->first();
					if($customer){
						$otp = 1234;
						$message = "OTP Sent successfully to your registered mobile number";
						$result['next_step_info']['mobile'] = $customer->mobile;
						$result['next_step_info']['type']   = 'customer-employee';
						CustomerEmployee::where('id',$customer->id)->update(['otp'=>$otp]);
						return response()->json(apiSuccessResponse($message,$result),200);
					}else{
						$message = "Entered mobile number not registered. Please contact with system administrator";
		    			return response()->json(apiErrorResponse($message),422);
					}
				}
	        }else{
	        	if($data['login_type'] =='customer-employee'){
	        		$customer = CustomerEmployee::where(['mobile'=>$data['mobile'],'otp'=>$data['otp']])->first();
	        		if($customer){
	        			CustomerEmployee::where(['mobile'=>$data['mobile']])->update(['otp'=>'']);
	        			$notificationToken =''; $appDetails='';
			        	if(isset($data['notification_token'])){
			        		$notificationToken = $data['notification_token'];
			        	}
			        	if(isset($data['app_details'])){
			        		$appDetails = $data['app_details'];
			        	}
			        	$authorizationToken = encrypt("customer-employee##-".$data['mobile']);
			        	$tokenDetails = array('type'=>'customer-employee','customer_id'=>$customer->customer_id,'customer_employee_id'=>$customer->id,'notification_token'=>$notificationToken,'app_details'=>$appDetails,'login_device'=>$data['login_device'],'auth_token'=>$authorizationToken);
			        	AuthToken::create($tokenDetails);
			        	$message = 'Logged in successfully';
			        	$result['token'] = $authorizationToken;
			        	$result['customer_employee'] = $customer;
			        	$result['customer_employee']['type'] = 'customer-employee';
			        	$dealerInfo = array();
			        	$customerInfo = array();
			        	if(!empty($customer->customer_id)){
			        		$customerInfo = DB::table('customers')->where('id',$customer->customer_id)->first();
			        		if(!empty($customerInfo->dealer_id)){
			        			$dealerInfo = DB::table('dealers')->where('id',$customerInfo->dealer_id)->first();
			        		}
			        	}
			        	$result['customer_employee']['customer_info'] = $customerInfo;
                        if(!empty($dealerInfo)){
                            $result['customer_employee']['dealer'] = $dealerInfo;
                        }
					   	return response()->json(apiSuccessResponse($message,$result),200);	
	        		}else{
	        			$message = "You have entered wrong OTP";
		    			return response()->json(apiErrorResponse($message),422);
	        		}
	        	}else{
	        		$customer = Customer::where(['mobile'=>$data['mobile'],'otp'=>$data['otp']])->first();
	        		if($customer){
	        			Customer::where(['mobile'=>$data['mobile']])->update(['otp'=>'']);
	        			$notificationToken =''; $appDetails='';
			        	if(isset($data['notification_token'])){
			        		$notificationToken = $data['notification_token'];
			        	}
			        	if(isset($data['app_details'])){
			        		$appDetails = $data['app_details'];
			        	}
			        	$authorizationToken = encrypt("customer##-".$data['mobile']);
			        	$tokenDetails = array('type'=>'customer','customer_id'=>$customer->id,'notification_token'=>$notificationToken,'app_details'=>$appDetails,'login_device'=>$data['login_device'],'auth_token'=>$authorizationToken);
			        	AuthToken::create($tokenDetails);
			        	$message = 'Logged in successfully';
			        	$result['token'] = $authorizationToken;
			        	$result['customer'] = $customer;
			        	$result['customer']['type'] = 'customer';
			        	$dealerInfo = array();
			        	if(!empty($customer->dealer_id)){
		        			$dealerInfo = DB::table('dealers')->where('id',$customer->dealer_id)->first();
		        		    $result['customer']['dealer'] = $dealerInfo;
		        		}
					   	return response()->json(apiSuccessResponse($message,$result),200);	
	        		}else{
	        			$message = "You have entered wrong OTP";
		    			return response()->json(apiErrorResponse($message),422);
	        		}
	        	}
	        }
    	}else{
    		$message = "GET not supported for this route";
		    return response()->json(apiErrorResponse($message),422);
    	}
    }

    public function customerinfo(){
        $resp = $this->resp;
        if($resp['status']) {
        	if($resp['type'] =="customer-employee"){
            	$customerid = $resp['customer_employee']['customer_id'];
            }else{
            	$customerid = $resp['customer']['id'];
            }
            $customerInfo = Customer::with(['corporate_discount','user_customer_shares'=>function($query){
                $query->with('user');
            },'dealer','employees','product_discounts'])->where('id',$customerid)->first();
            $message = "Fetched successfully";
            $result['customer_info'] = $customerInfo;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function profile(){
        $resp = $this->resp;
        if($resp['status']) {
            if($resp['type'] =="customer-employee"){
                $customerid = $resp['customer_employee']['customer_id'];
                $result['customer_employee'] = $resp['customer_employee'];
                $result['customer_employee']['type'] = 'customer-employee';
                $dealerInfo = array();
                $customerInfo = array();
                if(!empty($customerid)){
                    $customerInfo = Customer::with(['corporate_discount','user_customer_shares'=>function($query){
                $query->with('user');
            },'dealer','employees','product_discounts'])->where('id',$customerid)->where('id',$customerid)->first();
                    if(!empty($customerInfo->dealer_id)){
                        $dealerInfo = DB::table('dealers')->where('id',$customerInfo->dealer_id)->first();
                    }
                }
                $result['customer_employee']['customer_info'] = $customerInfo;
                $result['customer_employee']['dealer'] = $dealerInfo;
            }else{
                $customerid = $resp['customer']['id'];
                $result['customer'] = $resp['customer'];
                $result['customer']['type'] = 'customer';
                $dealerInfo = array();
                if(!empty($resp['customer']['dealer_id'])) {
                    $dealerInfo = DB::table('dealers')->where('id',$resp['customer']['dealer_id'])->first();
                }
                $result['customer']['dealer'] = $dealerInfo;
            }
            $message = "Fetched successfully";
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function purchaseOrder(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']) {
                $data = $request->all();
                $rules = [
                    'items'          =>   'bail|required|array|min:1',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.qty' => 'required|numeric',
                    'gst'    => 'bail|required|numeric',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }else{
                    DB::beginTransaction();
                    if($resp['type'] =="customer-employee"){
                    	$data['customer_id'] = $resp['customer_employee']['customer_id'];
                    	$data['customer_employee_id'] = $resp['customer_employee']['id'];
                    	$data['dealer_id']   = NULL;
                    	if(!empty($resp['customer_employee']['customer_id'])){
			        		$cusomerInfo = DB::table('customers')->where('id',$resp['customer_employee']['customer_id'])->first();
			        		if(!empty($cusomerInfo->dealer_id)){
			        			$data['dealer_id']   = $cusomerInfo->dealer_id;
			        		}
			        	}
                    	$action = 'customer_employee';
                    }else{
                    	$data['customer_id'] = $resp['customer']['id'];
                    	$data['dealer_id']   = $resp['customer']['dealer_id'];
                        $action = 'customer';
                    }
                    $data['action'] = $action;
                    $poid = PurchaseOrder::createPO($data,$resp);
                    DB::commit();
                    $purchaseOrderInfo = PurchaseOrder::with(['customer','orderitems','discounts','saleinvoices'])->where('id',$poid)->first();
                    $result['po_details'] = $purchaseOrderInfo;
                    $message ="Purcahse Order has been added successfully";
                    return response()->json(apiSuccessResponse($message,$result),200);
                }
            }else{
                $message = "Unable to fetch. PLease try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            $message = "GET not supported for this route";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function purchaseorderListing(){
        $resp = $this->resp;
        if($resp['status']) {
        	if($resp['type'] =="customer-employee"){
            	$customerid = $resp['customer_employee']['customer_id'];
            }else{
            	$customerid = $resp['customer']['id'];
            }
            $purchaseOrders = PurchaseOrder::with(['customer','customer_employee','orderitems','adjust_items','cancel_items','discounts','saleinvoices'])->where('customer_id',$customerid)->get()->toArray();
            $message = "Fetched successfully";
            $result['purchase_orders'] = $purchaseOrders;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function logout(Request $request){
    	if($request->isMethod('post')){
			$resp = $this->resp;
    		if($resp['status']){
    			if($resp['type'] =="customer-employee"){
    				$message ='Logged Out successfully';
    				AuthToken::where('auth_token',$resp['token'])->where('type','customer-employee')->delete();
    				return response()->json(apiSuccessResponse($message),200);
    			}elseif($resp['type'] =="customer"){
    				$message ='Logged Out successfully';
    				AuthToken::where('auth_token',$resp['token'])->where('type','customer')->delete();
    				return response()->json(apiSuccessResponse($message),200);
    			}else{
    				$message ='Logged Out successfully';
                    return response()->json(apiSuccessResponse($message),200);
    			}
    		}else{
    			$message ='Logged Out successfully';
                return response()->json(apiSuccessResponse($message),200);
    		}
    	}else{
    		$message = "GET not supported for this route";
		    return response()->json(apiErrorResponse($message),422); 
    	}
    }

    public function customerEmployees(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status']){
                if($resp['type'] =="customer-employee"){
                    $customerid = $resp['customer_employee']['customer_id'];
                }else{
                    $customerid = $resp['customer']['id'];
                }
                $customerEmployees = CustomerEmployee::where('customer_id',$customerid)->where('is_delete',0)->get();
                $result['employees'] = $customerEmployees;
                $message ="Employees has been fetched successfully";
                    return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function deleteCustomerEmp(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                $data = $request->all();
                $rules = [
                    'customer_employee_id' => 'bail|required|exists:customer_employees,id',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                if($resp['type'] =="customer-employee"){
                    $customerid = $resp['customer_employee']['customer_id'];
                }else{
                    $customerid = $resp['customer']['id'];
                }
                CustomerEmployee::where('customer_id',$customerid)->where('id',$data['customer_employee_id'])->update(['is_delete'=>1]);
                $message ="Employee has been deleted successfully";
                    return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function addCustomerEmployee(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                $data = $request->all();
                $rules = [
                    'name'       => 'bail|required',
                    'email'      => 'bail|required',
                    'mobile'     => 'bail|required|numeric|unique:customer_employees,mobile|digits:10',
                    'designation'=> 'bail|required',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }else{
                    if($resp['type'] =="customer-employee"){
                        $customerid = $resp['customer_employee']['customer_id'];
                    }else{
                        $customerid = $resp['customer']['id'];
                    }
                    $createCustEmp = new CustomerEmployee;
                    $createCustEmp->customer_id = $customerid;
                    $createCustEmp->name = $data['name'];
                    $createCustEmp->email = $data['email'];
                    $createCustEmp->mobile = $data['mobile'];
                    $createCustEmp->designation = $data['designation'];
                    $createCustEmp->save();
                    $message ="Customer Employee has been added successfully";
                    return response()->json(apiSuccessResponse($message),200);
                }
            }else{
                $message = "Session time out";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            $message = "GET not supported for this route";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function customer_return_history(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                $data = $request->all();
                $rules = [
                    "start_date"=> "required|date_format:Y-m-d",
                    "end_date"=> "required|date_format:Y-m-d",
                    "type"=> "required|in:sale_invoices,debit_credit,sale_returns,all",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                if($resp['type'] =="customer-employee"){
                    $customerid = $resp['customer_employee']['customer_id'];
                }else{
                    $customerid = $resp['customer']['id'];
                }
                $data['customer_id'] = $customerid;
                if($data['type'] == "all"){
                    $saleinvoices = SaleInvoice::getCustSaleInvoices($data,$resp);
                    $debitcredits = DebitCreditEntry::entries($data,$resp);
                    $cprs = CustomerPurchaseReturn::cprEntries($data,$resp);
                    $message = 'Fetched successfully';
                    $result['saleinvoices'] = $saleinvoices;
                    $result['debitcredits'] = $debitcredits;
                    $result['sale_returns'] = $cprs;
                    return response()->json(apiSuccessResponse($message,$result),200);
                }elseif($data['type'] == "sale_invoices"){
                    $saleinvoices = SaleInvoice::getCustSaleInvoices($data,$resp);
                    $message = 'Fetched successfully';
                    $result['saleinvoices'] = $saleinvoices;
                    return response()->json(apiSuccessResponse($message,$result),200);
                }elseif($data['type'] == "debit_credit"){
                    $debitcredits = DebitCreditEntry::entries($data,$resp);
                    $message = 'Fetched successfully';
                    $result['debitcredits'] = $debitcredits;
                    return response()->json(apiSuccessResponse($message,$result),200);
                }elseif($data['type'] == "sale_returns"){
                    $cprs = CustomerPurchaseReturn::cprEntries($data,$resp);
                    $message = 'Fetched successfully';
                    $result['sale_returns'] = $cprs;
                    return response()->json(apiSuccessResponse($message,$result),200);
                }
            }
        }
    }

    public function saveFeedback(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']) {
                $data = $request->all();
                $rules = [
                    "feedback_date"=> "required",
                    "type"=> "required|in:query,complaint,feedback,suggestion,need sample/trial,feedback/suggestion",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $data = $request->all();
                if($resp['type'] =="customer-employee"){
                    $customerid = $resp['customer_employee']['customer_id'];
                    $customer_employee_id = $resp['customer_employee']['id'];
                    $submit_by = "customer_employee";
                }else{
                    $customerid = $resp['customer']['id'];
                    $customer_employee_id = NULL;
                    $submit_by = "customer";
                }
                $data['customer_id'] = $customerid;
                $customer= Customer::find($customerid);
                $savefeed = new Feedback;
                $savefeed->customer_id = $data['customer_id'];
                $savefeed->customer_employee_id = $customer_employee_id;
                $savefeed->feedback_date = $data['feedback_date'];
                $savefeed->dealer_id = $customer->dealer_id;
                $savefeed->remarks = $data['remarks'];
                $savefeed->type = $data['type'];
                if($data['type']=="need sample/trial"){
                    $savefeed->product_id = $data['product_id'];
                }else{
                    $savefeed->is_product_related = $data['is_product_related'];
                    if($data['is_product_related'] == 1){
                        $savefeed->product_id = $data['product_id'];
                        $savefeed->batch_no = $data['batch_no'];
                    }
                }
                $savefeed->submit_by = $submit_by;  
                $savefeed->save();
                $message = 'Request has been submitted successfully. We will get back to you soon';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function qcfs(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                $data = $request->all();
                $rules = [
                    "type"=> "required|in:query,complaint,feedback,suggestion,need sample/trial,feedback/suggestion,all",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                if($resp['type'] =="customer-employee"){
                    $customerid = $resp['customer_employee']['customer_id'];
                    $customer_employee_id = $resp['customer_employee']['id'];
                }else{
                    $customerid = $resp['customer']['id'];
                    $customer_employee_id = NULL;
                }
                $feedbacks = Feedback::with(['customer','product','replies','customer_employee'])->where('customer_id',$customerid);
                if($data['type'] !='all'){
                    $feedbacks = $feedbacks->where('type',$data['type']);
                }
                $feedbacks = $feedbacks->orderby('id','DESC')->get();
                $message = 'Record has ben fetched successfully';
                $result['feedbacks'] = $feedbacks;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function getDebitCreditAccountOf(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status']){
                $data = $request->all();
                $rules = [
                    "month_year"=> "required",
                    'account_of' => 'required'
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                if($resp['type'] =="customer-employee"){
                    $customerid = $resp['customer_employee']['customer_id'];
                }else{
                    $customerid = $resp['customer']['id'];
                }
                $details = DebitCreditEntry::where('customer_id',$customerid)->where('on_account_of',$data['account_of'])->where('month_year',$data['month_year'])->get();
                $result['entries'] = $details;
                $message = "Fetched successfully";
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function deletePO(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                $data = $request->all();
                $rules = [
                    'purchase_order_id' => 'bail|required|exists:purchase_orders,id',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                PurchaseOrder::where('id',$data['purchase_order_id'])->delete();
                $message = "Purchase order has been deleted successfully";
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }
}
