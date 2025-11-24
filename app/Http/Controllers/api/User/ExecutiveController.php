<?php

namespace App\Http\Controllers\api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\Dealer;
use App\Product;
use App\PurchaseOrder;
use App\DvrTrialReport;
use App\TrialReport;
use App\SampleStockAdjustment;
use App\PurchaseOrderItem;
use App\SamplingSaleInvoice;
use App\FreeSamplingStock;
use App\PurchaseOrderItemDiscount;
use App\PurchaseOrderItemRawMaterial;
use App\LostSaleReport;
use App\DealerSpecialDiscount;
use App\DvrProduct;
use App\CustomerRegisterRequest;
use App\QtyDiscount;
use App\UserFreeSampleStock;
use App\MaterialApproval;
use App\SampleSubmission;
use App\User;
use Validator;
use DB;
use App\Dvr;
use App\Customer;
use App\FeedbackReply;
use App\SaleInvoice;
use App\SaleInvoiceItem;
use App\PurchaseOrderAdjustment;
use App\SaleInvoiceDiscount;
use App\DealerProduct;
use App\DebitCreditEntry;
use App\CustomerPurchaseReturn;
use App\DealerPurchaseReturn;
use App\CustomerPurchaseReturnItem;
use App\DealerPurchaseReturnItem;
use App\MarketProductInfo;
use App\StockAdjustment;
use App\InterDealerStockLog;
use App\Sampling;
use App\Feedback;
use App\MarketSample;
use App\ComplaintSample;
use App\UserScheduler;
use App\SalesProjection;
use Illuminate\Support\Facades\Input;
use PDF;
use App\Attendance;
use App\Holiday;
class ExecutiveController extends Controller
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
	            'mobile'    => 'bail|required|numeric|digits:10',
	            'password'  =>  'bail|required',
	            'login_device'  =>  'bail|required'
	        ];
	        $customMessages = [];
	        $validator = Validator::make($data,$rules,$customMessages);
	        if ($validator->fails()) {
			    return response()->json(validationResponse($validator),422); 
		    }
	        //Verify Dealer
	        $user = User::where(['mobile'=>$data['mobile']])->where('status',1)->first();
	        if($user && password_verify($data['password'], $user->password)){
	        	$notificationToken =''; $appDetails='';
	        	if(isset($data['notification_token'])){
	        		$notificationToken = $data['notification_token'];
	        	}
	        	if(isset($data['app_details'])){
	        		$appDetails = $data['app_details'];
	        	}
	        	$authorizationToken = encrypt('user##-'.$data['mobile']);
	        	$tokenDetails = array('type'=>'user','user_id'=>$user->id,'notification_token'=>$notificationToken,'app_details'=>$appDetails,'login_device'=>$data['login_device'],'auth_token'=>$authorizationToken);
	        	AuthToken::create($tokenDetails);
	        	$message = 'Logged in successfully';
	        	$result['token'] = $authorizationToken;
	        	$result['user'] = $user;
                $result['user']['user_roles'] = getUserRoles($user->app_roles,'executive');
                $reporting_resp = User::getReportingUsers($user->id);
                $result['user']['report_to_users'] =  $reporting_resp['report_to_users'];
                $result['user']['report_from_users'] =  $reporting_resp['report_from_users'];
                $result['user']['incentives'] =  $reporting_resp['incentives'];
                $result['user']['cities'] =  $reporting_resp['cities'];
			   	return response()->json(apiSuccessResponse($message,$result),200);
	        }else{
	        	$message = "You have entered wrong mobile number or password";
		    	return response()->json(apiErrorResponse($message),422);
	        } 
    	}else{
    		$message = "GET not supported for this route";
		    return response()->json(apiErrorResponse($message),422);
    	}
    }

    public function loginByOtp(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
           /* $rules = [
                'mobile'    => 'bail|required|numeric|digits:10',
                'password'  =>  'bail|required',
                'login_device'  =>  'bail|required'
            ];*/
            $rules = [
                'mobile'    => 'bail|required|numeric|digits:10',
                'step'      => 'bail|required|in:1,2',
                'otp'       => 'required_if:step,2|min:6',
                'login_device'  =>  'required_if:step,2',
            ];
            $customMessages = [];
            $validator = Validator::make($data,$rules,$customMessages);
            if ($validator->fails()) {
                return response()->json(validationResponse($validator),422); 
            }
            //Verify User
            $user = User::where(['mobile'=>$data['mobile']])->first();
            if(is_object($user)){
                if($data['step'] == 1){
                    if($data['mobile'] == "9890909090" || $data['mobile'] == "9876543210"){
                        $otp = 999888;
                    }else{
                        $otp =  rand(100000, 999999);
                        $params['mobile'] = $data['mobile'];
                        $params['message'] = "Your OTP for Login is ".$otp.". -GREENWAVE GLOBAL LTD";
                        sendSms($params);
                    }
                    $user->recent_otp = $otp;
                    $user->save();
                    $message = "Otp has been sent successfully to your mobile number";
                    return response()->json(apiSuccessResponse($message),200);
                }else if($data['step'] == 2){
                    if($user->recent_otp == $data['otp']){
                        $notificationToken =''; $appDetails='';
                        if(isset($data['notification_token'])){
                            $notificationToken = $data['notification_token'];
                        }
                        if(isset($data['app_details'])){
                            $appDetails = $data['app_details'];
                        }
                        $authorizationToken = encrypt('user##-'.$data['mobile']);
                        $tokenDetails = array('type'=>'user','user_id'=>$user->id,'notification_token'=>$notificationToken,'app_details'=>$appDetails,'login_device'=>$data['login_device'],'auth_token'=>$authorizationToken);
                        AuthToken::create($tokenDetails);
                        $message = 'Logged in successfully';
                        $result['token'] = $authorizationToken;
                        $result['user'] = $user;
                        $result['user']['user_roles'] = getUserRoles($user->app_roles,'executive');
                        $reporting_resp = User::getReportingUsers($user->id);
                        $result['user']['report_to_users'] =  $reporting_resp['report_to_users'];
                        $result['user']['report_from_users'] =  $reporting_resp['report_from_users'];
                        $result['user']['incentives'] =  $reporting_resp['incentives'];
                        $result['user']['cities'] =  $reporting_resp['cities'];
                        return response()->json(apiSuccessResponse($message,$result),200);
                    }else{
                        $message = "You have entered wrong OTP";
                        return response()->json(apiErrorResponse($message),422);
                    }
                }
            }else{
                $message = "You have entered wrong mobile number or password";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            $message = "GET not supported for this route";
            return response()->json(apiErrorResponse($message),422);
        }
    }

    public function updatePassword(request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    'old_password'    => 'bail|required',
                    'new_password'    => 'bail|required',
                    
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }else{
                    //echo "<pre>"; print_r($resp['user']); die;
                    $old_password = $data['old_password'];
                    if(password_verify($old_password, $resp['user']['password'])) {
                        $user = User::find($resp['user']['id']);
                        $user->password = bcrypt($data['new_password']);
                        $user->save();
                        $message = "Password has been updated successfully";
                        return response()->json(apiSuccessResponse($message),200);
                    }else{
                        $message = "Your old password is incorrect";
                        return response()->json(apiErrorResponse($message),422);
                    }
                }
            }else{
                $message = "Unable to fetch. PLease try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }

    }


    public function updateHashSalt(Request $request) {
        if ($request->isMethod('post')) {
            $resp = $this->resp; // Assuming this contains user info
            if ($resp['status'] && isset($resp['user'])) {
                $data = $request->all();

                $rules = [
                    'hash' => 'bail|required|string',
                    'salt' => 'bail|required|string',
                ];
                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return response()->json(validationResponse($validator), 422);
                } else {
                    $user = User::find($resp['user']['id']);
                    if ($user) {
                        $user->hash_salt = [
                            'hash' => $data['hash'],
                            'salt' => $data['salt']
                        ];
                        $user->save();

                        $message = "Hash and Salt have been updated successfully";
                        return response()->json(apiSuccessResponse($message), 200);
                    } else {
                        $message = "User not found";
                        return response()->json(apiErrorResponse($message), 422);
                    }
                }
            } else {
                $message = "Unable to fetch user. Please try again later";
                return response()->json(apiErrorResponse($message), 422);
            }
        }
    }

    public function updatePasscodeStatus(Request $request){
        if ($request->isMethod('post')) {
            $resp = $this->resp; // Assuming this contains dealer info

            if ($resp['status'] && isset($resp['user'])) {
                $data = $request->all();

                $rules = [
                    'passcode' => 'bail|required|in:0,1',
                ];
                $validator = Validator::make($data, $rules);

                if ($validator->fails()) {
                    return response()->json(validationResponse($validator), 422);
                } else {
                    $user = User::find($resp['user']['id']);
                    if ($user) {
                        $user->enable_passcode = $data['passcode'];
                        $user->save();

                        $message = "Passcode have been updated successfully";
                        return response()->json(apiSuccessResponse($message), 200);
                    } else {
                        $message = "User not found";
                        return response()->json(apiErrorResponse($message), 422);
                    }
                }
            } else {
                $message = "Unable to fetch user. Please try again later";
                return response()->json(apiErrorResponse($message), 422);
            }
        }
    }

    public function profile(Request $request){
    	if($request->isMethod('post')){
			$resp = $this->resp;
    		if($resp['status']){
    			if(isset($resp['user'])){
    				$message ='Profile has been fetched successfully';
    				$result['user'] = $resp['user'];
                    $result['user']['user_roles'] = getUserRoles($resp['user']['app_roles'],'executive');
                    $reporting_resp = User::getReportingUsers($resp['user']['id']);
                    $result['image_url'] = url('images/AdminImages').'/';
                    $result['user']['report_to_users'] =  $reporting_resp['report_to_users'];
                    $result['user']['report_from_users'] =  $reporting_resp['report_from_users'];
                    $result['user']['incentives'] =  $reporting_resp['incentives'];
                    $result['user']['cities'] =  $reporting_resp['cities'];
                    $noOfUsersLoggedIn = AuthToken::where('type','user')->where('user_id',$resp['user']['id'])->count();
                    $result['user']['no_of_users_logged_in'] = $noOfUsersLoggedIn;
    				return response()->json(apiSuccessResponse($message,$result),200);
    			}else{
    				$message = "Unable to fetch profile. PLease try again after sometime";
		    		return response()->json(apiErrorResponse($message),422);
    			}
    		}else{
    			$message = "Unable to fetch profile. PLease try again after sometime";
		    	return response()->json(apiErrorResponse($message),422);
    		}
    	}else{
    		$message = "GET not supported for this route";
		    return response()->json(apiErrorResponse($message),422); 
    	}
    }

    public function subordinateProfile(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $message ='Subordinate profile has been fetched successfully';
                $userinfo = User::find($data['user_id']);
                $result['user'] = $userinfo;
                $reporting_resp = User::getReportingUsers($data['user_id']);
                $result['image_url'] = url('images/AdminImages').'/';
                $result['user']['report_to_users'] =  $reporting_resp['report_to_users'];
                $result['user']['report_from_users'] =  $reporting_resp['report_from_users'];
                $result['user']['incentives'] =  $reporting_resp['incentives'];
                $result['user']['cities'] =  $reporting_resp['cities'];
                return response()->json(apiSuccessResponse($message,$result),200);
            }else{
                $message = "Unable to fetch profile. PLease try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            $message = "GET not supported for this route";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function dealers(){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])){
            $reporting_resp = User::getReportingUsers($resp['user']['id']);
            $dealers = Dealer::with(['linked_products','special_discounts'])->whereIn('city',$reporting_resp['cities'])->get()->toArray();
            $qty_discounts = QtyDiscount::with('product')->select('id','product_id','range_from','range_to','discount')->get();
            $message = "Fetched successfully";
            $result['dealers'] = $dealers;
            $result['qty_discounts'] = $qty_discounts;
            $result['cash_discounts'] = cashDiscounts();
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function products(Request $request){
        $products = Product::with(['productpacking','pricings','product_stages']);
        $products =  $products->where('status',1)->get();
        $result['products'] = $products;
        $discounts = \App\ProductDiscount::get();
        $result['product_discounts'] = $discounts;
        $message = "Products has been fetched successfully";
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function logout(Request $request){
    	if($request->isMethod('post')){
			$resp = $this->resp;
    		if($resp['status']){
    			if(isset($resp['user'])){
    				$message ='Logged Out successfully';
    				AuthToken::where('auth_token',$resp['token'])->where('type','user')->delete();
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

    public function logoutAllDevices(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                if(isset($resp['user'])){
                    $message ='Your account has been logged out from all devices';
                    AuthToken::where('type','user')->where('user_id',$resp['user']['id'])->delete();
                    return response()->json(apiSuccessResponse($message),200);
                }else{
                    $message ='Your account has been logged out from all devices';
                    return response()->json(apiSuccessResponse($message),200);
                }
            }else{
                $message ='Your account has been logged out from all devices';
                return response()->json(apiSuccessResponse($message),200);
            }
        }else{
            $message = "GET not supported for this route";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function customers(Request $request){
        $resp = $this->resp;
        if($resp['status']) {
            $data = $request->all();
            $mtodShow = true;
            if(isset($data['user_id']) && !empty($data['user_id'])){
                $userId = $data['user_id'];
                $mtodShow =false;
            }else{
                $userId = $resp['user']['id'];
            }
            $customerids = \App\UserCustomerShare::where('user_id',$userId)->pluck('customer_id')->toArray();
            $customers = Customer::with(['corporate_discount','product_discounts','employees','link_dealer'])->wherein('id',$customerids)->get()->toArray();
            $result['customers'] = $customers;
            if($mtodShow){
                $mtod = \App\Discount::orderby('start_date','DESC')->get();
                $spsod = \App\ProductDiscount::get();
                $result['mtod'] = $mtod;
                $result['spsod'] = $spsod;
            }
            $message = "Fetched successfully";
            return response()->json(apiSuccessResponse($message,$result),200);
        }else{
            $message = "Unable to fetch profile. PLease try again after sometime";
            return response()->json(apiErrorResponse($message),422);
        }
    }

    public function fetchCustomers(Request $request){
        $resp = $this->resp;
        if($resp['status']) {
            $data = $request->all();
            $mtodShow = true;
            if(isset($data['user_id']) && !empty($data['user_id'])){
                $userId = $data['user_id'];
                $mtodShow =false;
            }else{
                $userId = $resp['user']['id'];
            }
            $customerids = \App\UserCustomerShare::where('user_id',$userId)->pluck('customer_id')->toArray();
            $customers = Customer::with(['corporate_discount','product_discounts','employees','link_dealer'])->wherein('id',$customerids)->get()->toArray();
            $result['customers'] = $customers;
            if($mtodShow){
                $mtod = \App\Discount::orderby('start_date','DESC')->get();
                $spsod = \App\ProductDiscount::get();
                $result['mtod'] = $mtod;
                $result['spsod'] = $spsod;
            }
            $message = "Fetched successfully";
            return response()->json(apiSuccessResponse($message,$result),200);
        }else{
            $message = "Unable to fetch profile. PLease try again after sometime";
            return response()->json(apiErrorResponse($message),422);
        }
    }


    public function fetchCustomersGroupedByUsers(Request $request)
    {
        $resp = $this->resp;

        if (!$resp['status']) {
            $message = "Unable to fetch profile. Please try again after sometime.";
            return response()->json(apiErrorResponse($message), 422);
        }

        $data = $request->all();

        if (empty($data['user_ids'])) {
            $message = "user_ids parameter is required.";
            return response()->json(apiErrorResponse($message), 400);
        }

        // Convert comma-separated string to array
        $userIds = explode(',', $data['user_ids']);

        if (empty($userIds)) {
            $message = "No valid user IDs provided.";
            return response()->json(apiErrorResponse($message), 400);
        }

        // Fetch users with basic info
        $users = \App\User::whereIn('id', $userIds)
            ->select('id', 'name', 'email') // Add more columns if needed
            ->get()
            ->keyBy('id');

        $resultUsers = [];

        foreach ($userIds as $userId) {
            $user = $users->get($userId);

            if (!$user) {
                continue; // skip if user not found
            }

            // Get customer IDs linked to this user
            $customerIds = \App\UserCustomerShare::where('user_id', $userId)
                ->pluck('customer_id')
                ->toArray();

            // Fetch customers
            $customers = Customer::with(['corporate_discount', 'product_discounts', 'employees', 'link_dealer'])
                ->whereIn('id', $customerIds)
                ->get()
                ->toArray();

            // Build user object including customers
            $resultUsers[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'customers' => $customers
            ];
        }

        $result = [
            'users' => $resultUsers
        ];

        $message = "Fetched successfully.";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    public function purchaseOrder(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    'customer_id'    => 'bail|required|exists:customers,id',
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
                    if(isset($data['dealer_id']) && !empty($data['dealer_id']) && $data['dealer_id'] >0){
                        $action = 'dealer_customer';
                        $resp['dealer']['id'] = $data['dealer_id'];
                    }else{
                        $action = 'customer';
                    }
                    $data['user_id'] = $resp['user']['id']; 
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

    public function purchaseorderListing(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status']){
                if(isset($resp['user'])){
                    $customerids = \App\UserCustomerShare::where('user_id',$resp['user']['id'])->pluck('customer_id')->toArray();
                    $purchaseOrders = PurchaseOrder::with(['customer','customer_employee','orderitems','adjust_items','cancel_items','discounts','saleinvoices'])->wherein('customer_id',$customerids);
                        $purchaseOrders = $purchaseOrders->get()->toArray();
                    $message ="Purcahse Order has been fetched successfully";
                    $result['purchase_orders'] = $purchaseOrders;
                    return response()->json(apiSuccessResponse($message,$result),200);
                }else{
                    $message = "Unable to fetch. Please try again after sometime";
                    return response()->json(apiErrorResponse($message),422);
                }
            }else{
                $message = "Unable to fetch. Please try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            $message = "This method not supported for this route";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function createSampleRequest(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
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
                    $data['user'] = $resp['user']['id'];
                    $data['action'] = 'user';
                    $data['sample_date'] = date('Y-m-d');
                    $data['request_type'] = 'On Request';
                    Sampling::createSample($data,$resp);
                    DB::commit();
                    $message ="Sample has been created successfully";
                    return response()->json(apiSuccessResponse($message),200);
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

    public function samplings(){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])){
            $samplings = Sampling::with('sampling_items','sampling_invoice_items')->where('user_id',$resp['user']['id'])->get();
            $message = "Fetched successfully";
            $result['samplings'] = $samplings;
            return response()->json(apiSuccessResponse($message,$result),200);
        }else{
            $message = "Invalid customer";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function marketProductsInfo(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $rules = [
                    "customer_id"=> "required",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                if(isset($data['market_product_info_id']) && !empty($data['market_product_info_id'])) {
                    $marketProInfo = MarketProductInfo::find($data['market_product_info_id']);
                }else{
                    $marketProInfo = new MarketProductInfo;
                }
                $marketProInfo->customer_id = $data['customer_id'];
                $marketProInfo->user_id = $resp['user']['id'];
                $marketProInfo->product_category_id = $data['product_category_id'];
                if(isset($data['others'])){
                    $marketProInfo->others = $data['others'];
                }
                if(isset($data['product_category_name'])){
                    $marketProInfo->product_category_name = $data['product_category_name'];
                }
                $marketProInfo->product_name = $data['product_name'];
                $marketProInfo->make = $data['make'];
                $marketProInfo->dealer_name = $data['dealer_name'];
                $marketProInfo->price = $data['price'];
                $marketProInfo->dosage = $data['dosage'];
                $marketProInfo->monthly_consumption = $data['monthly_consumption'];
                $marketProInfo->remarks = $data['remarks'];
                $marketProInfo->save();
                $message = 'Market Products info request has been submitted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }else{
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $market_products_info = MarketProductInfo::with(['customer','product_category'])->where('user_id',$resp['user']['id'])->orderby('id','DESC')->get();
                $message = 'Market Products info request has been fetched successfully';
                $result['market_product_infos'] = $market_products_info;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function deleteMarketProductsInfo(Request $request,$id){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                MarketProductInfo::where('id',$id)->delete();
                $message = 'Record has been deleted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }


    public function saveFeedback(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    "customer_id"=> "required",
                    "feedback_date"=> "required",
                    "type"=> "required|in:query,complaint,feedback,suggestion,need sample/trial,feedback/suggestion,",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $data = $request->all();
                $savefeed = new Feedback;
                $savefeed->customer_id = $data['customer_id'];
                $savefeed->feedback_date = $data['feedback_date'];
                $savefeed->user_id = $resp['user']['id'];
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
                $savefeed->submit_by = 'executive';  
                $savefeed->save();
                $message = 'Request has been submitted successfully. We will get back to you soon';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function qcfs(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $rules = [
                    "type"=> "required|in:query,complaint,feedback,suggestion,need sample/trial,feedback/suggestion,all",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $feedbacks = Feedback::with(['customer','customer_employee','product','replies'])->where('user_id',$resp['user']['id']);
                if($data['type'] !='all'){
                    $feedbacks = $feedbacks->where('type',$data['type']);
                }
                if(isset($data['customer_id']) && !empty($data['customer_id'])){
                    $feedbacks = $feedbacks->where('customer_id',$data['customer_id']);
                }
                $feedbacks = $feedbacks->orderby('id','DESC')->get();
                $message = 'Record has ben fetched successfully';
                $result['feedbacks'] = $feedbacks;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function updateQcfsStatus(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $rules = [
                    "feedback_id"=> "required",
                    "status"=> "required",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $feedabck = Feedback::find($data['feedback_id']);
                $feedabck->status = $data['status'];
                $feedabck->save();
                $message = 'Status has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function feedbackReply(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $rules = [
                    "feedback_id"=> "required",
                    "reply"=> "required",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $reply = new FeedbackReply;
                $reply->feedback_id = $data['feedback_id'];
                $reply->reply = $data['reply'];
                $reply->created_by = $resp['user']['id'];
                $reply->save();
                $message = 'Reply has ben fetched successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function saveDvr(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    /*"customer_id"=> "required",*/
                    "dvr_date"=> "required|date_format:Y-m-d",
                    "purpose_of_visit"=> "required",
                    /*"trial_type"=> "required",*/
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                if(isset($data['dvr_id']) && is_numeric($data['dvr_id'])){
                    $dvr = Dvr::find($data['dvr_id']);
                }else{
                    $dvr = new Dvr;
                }
                $dvr->dvr_date = $data['dvr_date'];
                if(isset($data['customer_id']) && !empty($data['customer_id'])){
                    $dvr->customer_id = $data['customer_id'];
                }
                if(isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id'])){
                    $dvr->customer_register_request_id = $data['customer_register_request_id'];
                }
                if(isset($data['complaint_id']) && !empty($data['complaint_id'])){
                    $dvr->complaint_id = $data['complaint_id'];
                }

                if(isset($data['have_you_met'])) {
                    $dvr->have_you_met = $data['have_you_met'];
                }

                if(isset($data['other_team_member_id']) && !empty($data['other_team_member_id'])){
                    $dvr->other_team_member_id = $data['other_team_member_id'];
                }

                $dvr->sample_submission_id =NULL;
                if(isset($data['sample_submission_id']) && !empty($data['sample_submission_id'])){
                    $dvr->sample_submission_id = $data['sample_submission_id'];
                }

                $dvr->market_sample_id =NULL;
                if(isset($data['market_sample_id']) && !empty($data['market_sample_id'])){
                    $dvr->market_sample_id = $data['market_sample_id'];
                }

                $dvr->complaint_sample_id =NULL;
                if(isset($data['complaint_sample_id']) && !empty($data['complaint_sample_id'])){
                    $dvr->complaint_sample_id = $data['complaint_sample_id'];
                }

                $dvr->user_scheduler_id =NULL;
                if(isset($data['user_scheduler_id']) && !empty($data['user_scheduler_id'])){
                    $dvr->user_scheduler_id = $data['user_scheduler_id'];
                }

                $dvr->user_id = $resp['user']['id'];
                $dvr->purpose_of_visit = $data['purpose_of_visit'];
                $dvr->trial_type = $data['trial_type'];
                $dvr->is_fruitful = $data['is_fruitful'];
                if(isset($data['remarks'])){
                    $dvr->remarks = $data['remarks'];
                }
                $dvr->other      = $data['other'];
                $dvr->query      = $data['query'];
                $dvr->other_purpose = $data['other_purpose'];
                $dvr->visit_type = $data['visit_type'];
                $dvr->visit_detail = $data['visit_detail'];
                if(!empty($data['trial_details'])){
                    /*$trial_details = json_decode($data['trial_details'], true, JSON_UNESCAPED_SLASHES);
                    $trial_details = json_encode($trial_details);
                    $dvr->trial_details = NULL;*/
                    $dvr->trial_details = $data['trial_details'];
                }
                $dvr->next_plan = $data['next_plan'];
                if($request->hasFile('trial_report')){
                    if (Input::file('trial_report')->isValid()) {
                        $file = Input::file('trial_report');
                        $destination = 'DvrDocuments/';
                        $ext= $file->getClientOriginalExtension();
                        $mainFilename = "trial_report".uniqid().date('h-i-s').".".$ext;
                        $file->move($destination, $mainFilename);
                        $dvr->trial_report = $mainFilename;
                    }
                }
                if($request->hasFile('trial_costing_report')){
                    if (Input::file('trial_costing_report')->isValid()) {
                        $file = Input::file('trial_costing_report');
                        $destination = 'DvrDocuments/';
                        $ext= $file->getClientOriginalExtension();
                        $mainFilename = "trial_costing_report".uniqid().date('h-i-s').".".$ext;
                        $file->move($destination, $mainFilename);
                        $dvr->trial_costing_report = $mainFilename;
                    }
                }

                if($request->hasFile('trial_report_two')){
                    if (Input::file('trial_report_two')->isValid()) {
                        $file = Input::file('trial_report_two');
                        $destination = 'DvrDocuments/';
                        $ext= $file->getClientOriginalExtension();
                        $mainFilename = "trial_report_two".uniqid().date('h-i-s').".".$ext;
                        $file->move($destination, $mainFilename);
                        $dvr->trial_report_two = $mainFilename;
                    }
                }
                $dvr->save();
                if(isset($data['include_products']) && !empty($data['include_products'])){
                    $products = explode(',', $data['include_products']);
                    foreach($products as $product){
                        $dvrProd = new DvrProduct;
                        $dvrProd->dvr_id= $dvr->id;
                        $dvrProd->product_id= $product;
                        $dvrProd->save();
                    }
                }
                if(isset($data['user_scheduler_id']) && !empty($data['user_scheduler_id'])){
                    UserScheduler::where('id',$data['user_scheduler_id'])->update(['dvr_id'=>$dvr->id]);
                }
                $result['dvr_id'] = $dvr->id;
                $result['id']     = $dvr->id;
                $message = 'DVR has been submitted successfully';
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function linkUnlinkDvrScheduler(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                if($data['type'] == "link"){
                    Dvr::where('id',$data['dvr_id'])->update(['user_scheduler_id'=>$data['user_scheduler_id']]);
                    UserScheduler::where('id',$data['user_scheduler_id'])->update(['dvr_id'=>$data['dvr_id']]);
                }else{
                    Dvr::where('id',$data['dvr_id'])->update(['user_scheduler_id'=>NULL]);
                    UserScheduler::where('id',$data['user_scheduler_id'])->update(['dvr_id'=>NULL]);
                }
                $message = 'Updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function uploadDvrMedia(Request $request){
         if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $dvr = Dvr::find($data['dvr_id']);
                if($request->hasFile('trial_report')){
                    if (Input::file('trial_report')->isValid()) {
                        $file = Input::file('trial_report');
                        $destination = 'DvrDocuments/';
                        $ext= $file->getClientOriginalExtension();
                        $mainFilename = "trial_report".uniqid().date('h-i-s').".".$ext;
                        $file->move($destination, $mainFilename);
                        $dvr->trial_report = $mainFilename;
                    }
                }else{
                    $dvr->trial_report = NULL;
                }
                if($request->hasFile('trial_costing_report')){
                    if (Input::file('trial_costing_report')->isValid()) {
                        $file = Input::file('trial_costing_report');
                        $destination = 'DvrDocuments/';
                        $ext= $file->getClientOriginalExtension();
                        $mainFilename = "trial_costing_report".uniqid().date('h-i-s').".".$ext;
                        $file->move($destination, $mainFilename);
                        $dvr->trial_costing_report = $mainFilename;
                    }
                }else{
                    $dvr->trial_costing_report = NULL;
                }
                if($request->hasFile('trial_report_two')){
                    if (Input::file('trial_report_two')->isValid()) {
                        $file = Input::file('trial_report_two');
                        $destination = 'DvrDocuments/';
                        $ext= $file->getClientOriginalExtension();
                        $mainFilename = "trial_report_two".uniqid().date('h-i-s').".".$ext;
                        $file->move($destination, $mainFilename);
                        $dvr->trial_report_two = $mainFilename;
                    }
                }else{
                    $dvr->trial_report_two = NULL;
                }
                $dvr->save();
                $message = 'Media has been uploaded successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function dvrs(Request $request){
        if($request->isMethod('get')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $dvrs = Dvr::with(['customer','products','customer_register_request','complaint_info','query_info','other_team_member_info','trial_report_info','complaint_sample','market_sample','sample_submission','user_scheduler','trial_reports','customer_contact_info']);
                if(isset($data['customer_id']) && !empty($data['customer_id'])){
                    $dvrs = $dvrs->where('customer_id',$data['customer_id']);
                }

                if(isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id'])){
                    $dvrs = $dvrs->where('customer_register_request_id',$data['customer_register_request_id']);
                }

                if(isset($data['dvr_date']) && !empty($data['dvr_date'])){
                    $dvrs = $dvrs->where('dvr_date',$data['dvr_date']);
                }

                if(isset($data['employee_id']) && !empty($data['employee_id'])){
                    $dvrs = $dvrs->where('user_id',$data['employee_id']);
                }else{
                    $dvrs = $dvrs->where('user_id',$resp['user']['id']);
                }
                
                $dvrs = $dvrs->orderby('id','DESC')->get();
                $message = 'Record has ben fetched successfully';
                $result['report_base_url'] = url('/DvrDocuments/').'/';
                $result['dvrs'] = $dvrs;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function dvrInfo(Request $request,$dvrid){
        if($request->isMethod('get')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $dvr = Dvr::with(['customer','products','customer_register_request','complaint_info','query_info','other_team_member_info','trial_report_info','complaint_sample','market_sample','sample_submission','user_scheduler','trial_reports','customer_contact_info'])->where('id',$dvrid)->orderby('id','DESC')->first();
                $message = 'Record has ben fetched successfully';
                $result['report_base_url'] = url('/DvrDocuments/').'/';
                $result['dvr'] = $dvr;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }


    public function updateDvrCanShare(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $dvr = Dvr::find($data['dvr_id']);
                if(isset($data['trial_costing_report_share'])){
                    $dvr->trial_costing_report_share = $data['trial_costing_report_share'];
                }
                if(isset($data['trial_report_share'])){
                    $dvr->trial_report_share = $data['trial_report_share'];
                }
                $dvr->save();
                $message = 'Updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function deleteDvr($dvrid){
        Dvr::where('id',$dvrid)->delete();
        $message = "DVR has been deleted successfully";
        return response()->json(apiSuccessResponse($message),200);
    }

    public function linkedEmployees(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $reporting_resp = User::getReportingUsers($resp['user']['id']);
                $result['report_to_users'] =  $reporting_resp['report_to_users'];
                $from_users = $reporting_resp['report_from_users'];
                foreach($from_users as $ukey=> $user_level_1){
                    $reporting_resp = User::getReportingUsers($user_level_1['id']);
                    $level1_from_users = $reporting_resp['report_from_users'];
                    $from_users[$ukey]['report_from_users'] = $level1_from_users;
                    foreach($level1_from_users as $u2key=> $user_level_2){
                        $reporting_resp = User::getReportingUsers($user_level_2['id']);
                        $level2_from_users = $reporting_resp['report_from_users'];
                        $from_users[$ukey]['report_from_users'][$u2key]['report_from_users'] = $level2_from_users;
                    }
                }
                $result['report_from_users'] =  $from_users;
                $message = 'Record has ben fetched successfully';
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function masterlists(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $activites = activities();
                $cities = DB::table('cities')->orderby('city_name','ASC')->pluck('city_name');
                $designations = array('Owner','G.M.','Production In-Charge','Purchase In-charge'); 
                $message = 'Record has ben fetched successfully';
                $result['activites'] = $activites;
                $result['designations'] = $designations;
                $result['cities']    = $cities;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function saveCustomerRequest(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $emailunique = function ($attribute, $value, $fail) {
                    $existsInCustomers = DB::table('customers')->where('email', $value)->exists();
                    $existsInRequests = DB::table('customer_register_requests')
                        ->where('email', $value)
                        ->where('status', 'Pending')
                        ->exists();

                    if ($existsInCustomers || $existsInRequests) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                };

                $mobileunique = function ($attribute, $value, $fail) {
                    $existsInCustomers = DB::table('customers')->where('mobile', $value)->exists();
                    $existsInRequests = DB::table('customer_register_requests')
                        ->where('mobile', $value)
                        ->where('status', 'Pending')
                        ->exists();

                    if ($existsInCustomers || $existsInRequests) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                };

                $rules = [
                    'contact_person_name' => 'bail|required',
                    'name'   => 'bail|required',
                    'email'  => ['bail', 'required', 'email', $emailunique],
                    'mobile' => ['bail', 'required', 'numeric', 'digits:10', $mobileunique],
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $request = new CustomerRegisterRequest;
                $request->name = $data['name'];
                $request->email = $data['email'];
                $request->mobile = $data['mobile'];
                $request->address = $data['address'];
                //$request->category = $data['category'];
                $request->contact_person_name = $data['contact_person_name'];
                $request->cities = $data['cities'];
                $request->designation = $data['designation'];
                $request->activity = $data['activity'];
                $request->created_by = $resp['user']['id'];
                $request->save();
                $message = 'Register request has been submitted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function v2saveCustomerRequest(Request $request)
    {
        if ($request->isMethod('post')) {
            $resp = $this->resp;

            if ($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $id = $request->input('customer_register_request_id'); // Edit key

                // Email validation closure (optional + unique)
                $emailunique = function ($attribute, $value, $fail) use ($id) {
                    if (!$value) {
                        return; // Skip if email is empty/null
                    }

                    $existsInCustomers = DB::table('customers')->where('email', $value)->exists();

                    $existsInRequests = DB::table('customer_register_requests')
                        ->where('email', $value)
                        ->where('status', 'Pending')
                        ->when($id, function ($query) use ($id) {
                            return $query->where('id', '!=', $id); // Allow self
                        })
                        ->exists();

                    if ($existsInCustomers || $existsInRequests) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                };

                // Mobile validation closure (still required)
                $mobileunique = function ($attribute, $value, $fail) use ($id) {
                    $existsInCustomers = DB::table('customers')->where('mobile', $value)->exists();

                    $existsInRequests = DB::table('customer_register_requests')
                        ->where('mobile', $value)
                        ->where('status', 'Pending')
                        ->when($id, function ($query) use ($id) {
                            return $query->where('id', '!=', $id); // Allow self
                        })
                        ->exists();

                    if ($existsInCustomers || $existsInRequests) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                };

                $rules = [
                    'contact_person_name' => 'bail|required',
                    'name' => 'bail|required',
                    'email' => ['bail', 'nullable', 'email', $emailunique],
                    'mobile' => ['bail', 'required', 'numeric', 'digits:10', $mobileunique],
                    'business_model' => 'bail|required|in:Dealer,Open,Direct Customer',
                    'dealer_id' => 'required_if:business_model,Dealer',
                    'linked_executive' => 'bail|required',
                    'business_card' => 'nullable|max:2048'
                ];

                $customMessages = [];

                $validator = Validator::make($data, $rules, $customMessages);

                if ($validator->fails()) {
                    return response()->json(validationResponse($validator), 422);
                }

                // Create or update model
                $requestModel = $id ? CustomerRegisterRequest::find($id) : new CustomerRegisterRequest;

                if ($id && !$requestModel) {
                    return response()->json(apiErrorResponse('Request not found'), 404);
                }

                $requestModel->name = $data['name'];
                $requestModel->email = $data['email'] ?? null; // assign null if not provided
                $requestModel->mobile = $data['mobile'];
                $requestModel->address = $data['address'];
                $requestModel->contact_person_name = $data['contact_person_name'];
                $requestModel->cities = $data['cities'];
                $requestModel->designation = $data['designation'];
                $requestModel->activity = $data['activity'];
                $requestModel->business_model = $data['business_model'];
                $requestModel->linked_executive = $data['linked_executive'];
                if(isset($data['employee_remarks'])){
                    $requestModel->employee_remarks = $data['employee_remarks'];
                }

                if(isset($data['declaration'])){
                    $requestModel->declaration = $data['declaration'];
                }
                $requestModel->dealer_id = $data['business_model'] === 'Dealer' ? ($data['dealer_id'] ?? null) : null;

                if (!$id) {
                    $requestModel->created_by = $resp['user']['id'];
                }

                // Handle file upload
                if ($request->hasFile('business_card')) {
                    $file = $request->file('business_card');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = 'business_cards/' . $filename;

                    $file->move(public_path('business_cards'), $filename);
                    $requestModel->business_card = $path;
                    
                    if ($request->hasFile('business_card_two')) {
                        $file = $request->file('business_card_two');
                        $filename = time() . '2_' . $file->getClientOriginalName();
                        $path = 'business_cards/' . $filename;

                        $file->move(public_path('business_cards'), $filename);
                        $requestModel->business_card_two = $path;
                    }
                }


                $requestModel->save();

                $message = $id
                    ? 'Register request has been updated successfully'
                    : 'Register request has been submitted successfully';

                return response()->json(apiSuccessResponse($message), 200);
            }
        }
    }



    public function customersAreaList(Request $request){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])) {
            $data = $request->all();
            $getSubRegions = \App\UserDepartmentRegion::where('user_id',$resp['user']['id'])->pluck('sub_region_id')->toArray();
            $cities = \App\RegionCity::wherein('region_id',$getSubRegions)->pluck('city')->toArray();
            $customerIds = \App\CustomerCity::wherein('city_name',$cities)->pluck('customer_id')->toArray();
            $customers =  \App\Customer::wherein('id',$customerIds)->select('*', \DB::raw("'1' as status"))->get()->toArray();
            $request_customers = \App\CustomerRegisterRequest::with(['linkedExecutive','dealer'])->wherein('cities',$cities)->where('status','!=','Added')->get()->toArray();

            $result['registered_customers'] = $customers;
            $result['request_customers'] = $request_customers;
            $message = 'Customers has been fetched successfully';
            return response()->json(apiSuccessResponse($message,$result),200);
        }
        
    }

    public function addedCustomerRequests(Request $request){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])) {
            $data = $request->all();
            $request_customers = \App\CustomerRegisterRequest::with(['dealer'=>function($query){
                $query->select('id', 'parent_id', 'business_name', 'name', 'designation', 'address', 'city', 'email');
            },'linkedExecutive'=>function($query){
                $query->select('id', 'name', 'designation', 'mobile', 'email');
            }])->where('created_by',$resp['user']['id'])->get()->toArray();
            $result['customers'] = $request_customers;
            $result['declaration'] = "I hereby declare that the customer information provided has been duly verified by me and is accurate to the best of my knowledge. I will submit any missing details, such as email ID or business card, at a later stage. Please approve this customer on my behalf.";
            $result['declaration_short'] = "I hereby declare that the customer information provided has been duly verified by me and is accurate to the best of my knowledge.";
            $message ="fetched successfully";
            return response()->json(apiSuccessResponse($message,$result),200);
        }
        
    }

    public function return_history(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    /*"customer_id"=> "required",*/
                    "start_date"=> "required|date_format:Y-m-d",
                    "end_date"=> "required|date_format:Y-m-d",
                    "type"=> "required|in:sale_invoices,debit_credit,sale_returns,all",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                if(isset($data['customer_id']) && !empty($data['customer_id'])){
                    $data['customer_ids'] = array();
                }else{
                    // check user_ids param
                    $userIds = [];
                    if (isset($data['user_ids']) && !empty($data['user_ids'])) {
                        $userIds = array_filter(array_map('trim', explode(',', $data['user_ids'])));
                    }

                    // fallback to current user if no user_ids provided
                    if (empty($userIds)) {
                        $userIds = [$resp['user']['id']];
                    }

                    $customerids = \App\UserCustomerShare::whereIn('user_id', $userIds)
                        ->pluck('customer_id')
                        ->unique()            // make collection unique
                        ->values()            // reset array indexes
                        ->toArray();

                    $data['customer_ids'] = $customerids;

                }             
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

    public function materialApprovalList(Request $request){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])) {
            $customerids = \App\UserCustomerShare::where('user_id',$resp['user']['id'])->pluck('customer_id')->toArray();
            $list = MaterialApproval::with(['product','customer'])->wherein('customer_id',$customerids)->orderby('id','DESC')->get();
            $message = "Fetched successfully";
            $result['material_approval'] = $list;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function saveLostSaleReport(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $lost_sale_report = new LostSaleReport;
                $lost_sale_report->report_date = $data['report_date'];
                $lost_sale_report->wef_date = $data['wef_date'];
                $lost_sale_report->product_id = $data['product_id'];
                $lost_sale_report->customer_id = $data['customer_id'];
                $lost_sale_report->monthly_requirement = $data['monthly_requirement'];
                $lost_sale_report->reason = $data['reason'];
                $lost_sale_report->replaced_by_product_name = $data['replaced_by_product_name'];
                $lost_sale_report->replaced_by_company_name = $data['replaced_by_company_name'];
                $lost_sale_report->replaced_by_dealer_name = $data['replaced_by_dealer_name'];
                $lost_sale_report->replaced_by_price = $data['replaced_by_price'];
                $lost_sale_report->replaced_by_application = $data['replaced_by_application'];
                $lost_sale_report->replaced_by_dosage_type = $data['replaced_by_dosage_type'];
                $lost_sale_report->replaced_by_dosage_percent = $data['replaced_by_dosage_percent'];
                $lost_sale_report->replaced_by_cost_percent = $data['replaced_by_cost_percent'];
                $lost_sale_report->replaced_by_dosage_gpl = $data['replaced_by_dosage_gpl'];
                $lost_sale_report->replaced_by_mlr = $data['replaced_by_mlr'];
                $lost_sale_report->replaced_by_cost_gpl = $data['replaced_by_cost_gpl'];
                $lost_sale_report->replaced_by_pick_up = $data['replaced_by_pick_up'];
                $lost_sale_report->replaced_by_trough_loss = $data['replaced_by_trough_loss'];
                $lost_sale_report->replaced_by_lot_size = $data['replaced_by_lot_size'];
                $lost_sale_report->replaced_by_dosage_pm = $data['replaced_by_dosage_pm'];
                $lost_sale_report->replaced_by_cost_pm = $data['replaced_by_cost_pm'];
                $lost_sale_report->price = $data['price'];
                $lost_sale_report->application = $data['application'];
                $lost_sale_report->dosage_type = $data['dosage_type'];
                $lost_sale_report->dosage_percent = $data['dosage_percent'];
                $lost_sale_report->cost_percent = $data['cost_percent'];
                $lost_sale_report->dosage_gpl = $data['dosage_gpl'];
                $lost_sale_report->mlr = $data['mlr'];
                $lost_sale_report->cost_gpl = $data['cost_gpl'];
                $lost_sale_report->pick_up = $data['pick_up'];
                $lost_sale_report->trough_loss = $data['trough_loss'];
                $lost_sale_report->lot_size = $data['lot_size'];
                $lost_sale_report->dosage_pm = $data['dosage_pm'];
                $lost_sale_report->cost_pm = $data['cost_pm'];
                if(isset($data['lost_date']) && !empty($data['lost_date'])){
                    $lost_sale_report->lost_date = $data['lost_date'];
                }
                if(isset($data['status']) && !empty($data['status'])) {
                    $lost_sale_report->status = $data['status'];
                }
                $lost_sale_report->creation_type = 'executive';
                $lost_sale_report->created_by = $resp['user']['id'];
                $lost_sale_report->save();
                $message = 'Lost sale report has been added successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function editLostSaleReport(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $lost_sale_report = LostSaleReport::find($data['lost_sale_report_id']);
                $lost_sale_report->recover_date = $data['recover_date'];
                $lost_sale_report->status = $data['status'];
                $lost_sale_report->save();
                $message = 'Record has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function lostSaleReports(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $lost_sale_reports = LostSaleReport::with(['product','customer'])->where('created_by',$resp['user']['id'])->get();
                $result['lost_sale_reports'] = $lost_sale_reports;
                $message = 'Lost sale report has been fetched successfully';
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function deleteLostSaleReport(Request $request,$id){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                LostSaleReport::where('id',$id)->delete();
                $message = 'Record has been deleted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }


    public function sampleInTransitMaterials(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                //echo "<pre>"; print_r($resp); die;
                $lrNos = SamplingSaleInvoice::where('user_id',$resp['user']['id'])->where('transport_name','!=','')->orderby('dispatch_date','ASC')->where('is_delivered',0)->select('lr_no')->groupby('lr_no')->get();
                $lrNos = json_decode(json_encode($lrNos),true);
                $saleInvoices = array();
                foreach($lrNos as $lkey=> $lrNo){
                    $saleInvoices[$lkey]['lr_no'] = $lrNo['lr_no'];
                    $invoices = SamplingSaleInvoice::with('productinfo')->where('user_id',$resp['user']['id'])->where('transport_name','!=','')->orderby('dispatch_date','ASC')->where('is_delivered',0)->Where('lr_no',$lrNo)->get();
                    $invoices = json_decode(json_encode($invoices),true);
                    $saleInvoices[$lkey]['invoices'] = $invoices;
                }
                $message = "Sale Invoice has been fetched successfully";
                $result['sale_invoices'] = $saleInvoices;
                return response()->json(apiSuccessResponse($message,$result),200);
            }else{
                $message = "Unable to fetch. PLease try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            $message = "Unsupported Route";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function updateFreeSampleMaterialDelivery(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            $rules = [
                "sale_invoice_ids"    => "required|array|min:1",
                "sale_invoice_ids.*"  => "required|distinct|min:1",
            ];
            $customMessages = [];
            $validator = Validator::make($data,$rules,$customMessages);
            if ($validator->fails()) {
                return response()->json(validationResponse($validator),422); 
            }
            SamplingSaleInvoice::wherein('id',$data['sale_invoice_ids'])->update(['is_delivered'=>1]);
            foreach($data['sale_invoice_ids'] as $saleInvid){
                $saleInvoice = SamplingSaleInvoice::find($saleInvid);
                $execProd = DB::table('free_sampling_stocks')->where(['user_id'=>$saleInvoice->user_id,'product_id'=>$saleInvoice->product_id])->first();
                if($execProd){
                    $updateexecProd = FreeSamplingStock::find($execProd->id);
                    $updateexecProd->in_transit = $execProd->in_transit - $saleInvoice->qty;
                    $updateexecProd->stock_in_hand = $execProd->stock_in_hand + $saleInvoice->qty;
                    $updateexecProd->save();
                }
            }
            $message = "Material marked delivered successfully";
            return response()->json(apiSuccessResponse($message),200);
        }
    }

    public function productsFreeSampleStock(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $list = FreeSamplingStock::with('productinfo')->where('user_id',$resp['user']['id'])->get();
                $result['product_stocks'] = $list;
                $message = 'Data has been fetched successfully';
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function sampleSubmissionList(Request $request ){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $list = SampleSubmission::with(['customer','product','complaint_info'])->where('user_id',$resp['user']['id'])->get();
                $result['sample_submissions'] = $list;
                $message = 'Data has been fetched successfully';
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function sampleSubmission(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    "submission_date"=> "required|date_format:Y-m-d",
                    "customer_id"=> "required",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                if(isset($data['sample_submission_id']) && !empty($data['sample_submission_id'])){
                    $sample_submission = SampleSubmission::find($data['sample_submission_id']);
                    $old_qty = $sample_submission->qty;
                }else{
                    $sample_submission = new SampleSubmission;
                }
                $sample_submission->user_id = $resp['user']['id']; 
                $sample_submission->customer_id = $data['customer_id']; 
                $sample_submission->submission_date = $data['submission_date']; 
                $sample_submission->purpose = $data['purpose']; 
                $sample_submission->submission_type = $data['submission_type']; 
                $sample_submission->complaint_id = $data['complaint_id']; 
                $sample_submission->product_id = $data['product_id']; 
                $sample_submission->qty = $data['qty']; 
                $sample_submission->from = $data['from']; 
                $sample_submission->sample_value = $data['sample_value']; 
                $sample_submission->remarks = $data['remarks']; 
                $sample_submission->save();
                $freeSamplingStock = DB::table('free_sampling_stocks')->where(['product_id'=>$data['product_id']])->where('user_id',$resp['user']['id'])->first();
                if(is_object($freeSamplingStock)){
                    $update_sample_stock = FreeSamplingStock::find($freeSamplingStock->id);
                    $update_sample_stock->stock_in_hand = $update_sample_stock->stock_in_hand + $old_qty - $data['qty'];
                    $update_sample_stock->save();
                }else{
                    $free_sample_stock = new FreeSamplingStock;
                    $free_sample_stock->user_id = $resp['user']['id'];
                    $free_sample_stock->product_id = $data['product_id'];
                    $free_sample_stock->customer_id = $data['customer_id'];
                    $free_sample_stock->user_id = $resp['user']['id'];
                    $free_sample_stock->stock_in_hand = 0 - $data['qty'];
                    $free_sample_stock->save();
                }
                
                $message = 'Request has been submitted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function updateSampleSubmissionValues(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                foreach($data['values'] as $sampleVal){
                    $update_sample = SampleSubmission::find($sampleVal['sample_submission_id']);
                    $update_sample->sample_value = $sampleVal['value'];
                    $update_sample->save();
                }
                $message = 'Request has been submitted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function addSampleSubsmissionFeedback(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    "sample_submission_id"=> "required",
                    "feedback_date"=> "required|date_format:Y-m-d",
                    "feedback"=> "required",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                $sample_submission = SampleSubmission::find($data['sample_submission_id']);
                $sample_submission->feedback_date = $data['feedback_date'];
                $sample_submission->feedback = $data['feedback'];
                $sample_submission->feedback_remarks = $data['feedback_remarks'];
                if($data['feedback'] =="Positive"){
                    $sample_submission->status = "Order Awaited";
                }else{
                    $sample_submission->status = "Closed";
                    $sample_submission->is_close = 1;
                    $sample_submission->close_reason =$data['feedback_remarks'];
                }
                $sample_submission->save(); 
                $message = 'Feedback has been submitted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function closeSampleSubmission(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    "sample_submission_id"=> "required",
                    "close_reason"=> "required"
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                $sample_submission = SampleSubmission::find($data['sample_submission_id']);
                $sample_submission->status = "Closed";
                $sample_submission->is_close = 1;
                $sample_submission->close_reason = $data['close_reason'];
                $sample_submission->save();
                $message = 'Closed successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function returnSampleSubmission(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])) {
                $data = $request->all();
                $rules = [
                    "sample_submission_id"=> "required",
                    "return_qty"=> "required",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                $sample_submission = SampleSubmission::find($data['sample_submission_id']);
                $sample_submission->is_returned = 1;
                $sample_submission->return_qty = $data['return_qty'];
                $sample_submission->save();
                $freeSamplingStock = DB::table('free_sampling_stocks')->where(['product_id'=>$sample_submission->product_id])->where('user_id',$sample_submission->user_id)->first();
                if(is_object($freeSamplingStock)){
                    $update_sample_stock = FreeSamplingStock::find($freeSamplingStock->id);
                    $update_sample_stock->stock_in_hand = $update_sample_stock->stock_in_hand + $data['return_qty'];
                    $update_sample_stock->save();
                }
                $message = 'Returned successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function sampleStockAdjustment(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $rules = [
                    "product_id"=>"required|exists:products,id",
                    "qty"=>"required",
                    "amount"=>"required",
                    "adjustment_date"=>"required|date_format:Y-m-d",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                DB::beginTransaction();
                $execProd = DB::table('free_sampling_stocks')->where(['user_id'=>$resp['user']['id'],'product_id'=>$data['product_id']])->first();
                if(is_object($execProd)){
                    $updateexecProd = FreeSamplingStock::find($execProd->id);
                    $updateexecProd->stock_in_hand = $execProd->stock_in_hand - $data['qty'];
                    $updateexecProd->save();
                }else{
                    $updateexecProd = new FreeSamplingStock;
                    $updateexecProd->user_id =  $resp['user']['id'];
                    $updateexecProd->product_id =  $data['product_id'];
                    $updateexecProd->stock_in_hand = 0 - $data['qty'];
                    $updateexecProd->save();
                }
                $stock_adjust = new SampleStockAdjustment;
                $stock_adjust->product_id = $data['product_id'];
                $stock_adjust->user_id = $resp['user']['id'];
                $stock_adjust->amount = $data['amount'];
                $stock_adjust->qty = $data['qty'];
                $stock_adjust->remarks = $data['remarks'];
                $stock_adjust->reason = $data['reason'];
                $stock_adjust->adjustment_date = $data['adjustment_date'];
                $stock_adjust->other = $data['other'];
                $stock_adjust->save();
                DB::commit();
                $message = 'Sample Stock has been adjusted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function sampleStockAdjustmentLogs(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $logs = SampleStockAdjustment::with('product')->where('user_id',$resp['user']['id']);
                if(isset($data['product_id'])){
                    $logs = $logs->where('product_id',$data['product_id']);
                }
                $logs = $logs->get();
                $message = 'Logs has been fetched successfully';
                $result['logs'] = $logs;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function marketSamples(Request $request){
       if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $samples = MarketSample::getMarketSamples('executive',$resp['user']['id']);
                $message = 'Market sample has been fetched successfully';
                $result['samples'] = $samples;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        } 
    }

    public function createMarketSample(Request $request){
       if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $request->request->add(['user_id'=> $resp['user']['id']]);
                MarketSample::createMarketSample($request);
                $message = 'Market sample has been added successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function editMarketSample(Request $request){
       if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                MarketSample::editMarketSample($request);
                $message = 'Market sample has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function complaintSamples(Request $request){
       if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $samples = ComplaintSample::getComplaintSamples('executive',$resp['user']['id']);
                $message = 'Complaint samples has been fetched successfully';
                $result['samples'] = $samples;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        } 
    }

    public function createComplaintSample(Request $request){
       if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $request->request->add(['user_id'=> $resp['user']['id']]);
                ComplaintSample::createComplaintSample($request);
                $message = 'Complaint samples has been added successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function editComplaintSample(Request $request){
       if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                ComplaintSample::editComplaintSample($request);
                $message = 'Complaint sample has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }


    public function schedulers(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $schedulers = UserScheduler::with(['customer','customer_register_request','previous_scheduler','next_scheduler']);
                if(isset($data['user_id']) && !empty($data['user_id'])){
                    $schedulers = $schedulers->where('user_id',$data['user_id']);
                }else{
                    $schedulers = $schedulers->where('user_id',$resp['user']['id']);
                }

                if(isset($data['date']) && !empty($data['date'])){
                    $date = $data['date'];
                    $schedulers = $schedulers->where('scheduler_date',$date)->orderby('scheduler_time','ASC');
                }else{
                    $schedulers = $schedulers->whereNULL('scheduler_date');
                }
                $schedulers =  $schedulers->get();
                $result['schedulers'] = $schedulers;
                $message = 'Data has been fetched successfully';
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function createScheduler(Request $request){
       if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $scheduler = new UserScheduler;
                $scheduler->user_id = $resp['user']['id'];
                if(isset($data['customer_id']) && !empty($data['customer_id'])){
                    $scheduler->customer_id = $data['customer_id'];
                }
                if(isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id'])){
                    $scheduler->customer_register_request_id = $data['customer_register_request_id'];
                }
                if(isset($data['previous_scheduler_id']) && !empty($data['previous_scheduler_id'])){
                    $scheduler->previous_scheduler_id = $data['previous_scheduler_id'];
                }
                if(isset($data['dvr_id']) && !empty($data['dvr_id'])){
                    $scheduler->dvr_id = $data['dvr_id'];
                }
                $scheduler->scheduler_date = $data['scheduler_date'];
                $scheduler->scheduler_time = $data['scheduler_time'];
                $scheduler->description = $data['description'];
                $scheduler->save();
                if(isset($data['dvr_id']) && !empty($data['dvr_id'])){
                    Dvr::where('id',$data['dvr_id'])->update(['user_scheduler_id'=>$scheduler->id]);
                }
                $result['scheduler_id'] = $scheduler->id;
                $message = 'Schdule has been added successfully';
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        } 
    }

    public function editScheduler(Request $request){
       if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $scheduler = UserScheduler::find($data['scheduler_id']);
                $scheduler->customer_id = NULL;
                if(isset($data['customer_id']) && !empty($data['customer_id'])){
                    $scheduler->customer_id = $data['customer_id'];
                }
                $scheduler->customer_register_request_id = NULL;
                if(isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id'])){
                    $scheduler->customer_register_request_id = $data['customer_register_request_id'];
                }
                $scheduler->dvr_id = NULL;
                if(isset($data['dvr_id']) && !empty($data['dvr_id'])){
                    $scheduler->dvr_id = $data['dvr_id'];
                }
                if(isset($data['status']) && !empty($data['status'])){
                    $scheduler->status = $data['status'];
                }
                $scheduler->scheduler_date = $data['scheduler_date'];
                $scheduler->scheduler_time = $data['scheduler_time'];
                $scheduler->description = $data['description'];
                $scheduler->save();
                $message = 'Schdule has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function updateSchedulerStatus(Request $request){
       if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $scheduler = UserScheduler::find($data['scheduler_id']);
                $scheduler->status = $data['status'];
                $scheduler->save();
                $message = 'Schdule status has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function updateNextScheduler(Request $request){
       if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $scheduler = UserScheduler::find($data['scheduler_id']);
                $scheduler->next_scheduler_id = $data['next_scheduler_id'];
                $scheduler->save();
                $message = 'Schdule has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function deleteScheduler(Request $request,$schedulerid){
       if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                UserScheduler::find($schedulerid)->delete();
                $message = 'Schdule has been deleted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function trialReports(Request $request){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])){
            $data = $request->all();
            $userid = $resp['user']['id'];
            $trialReports = TrialReport::with(['customer','customer_register_request','other_team_member_info','feedback_info','baths','dvr_info'])->where('user_id',$resp['user']['id']);

            if(isset($data['customer_id']) && !empty($data['customer_id'])){
                $trialReports = $trialReports->where('customer_id',$data['customer_id']);
            }

            if(isset($data['trial_report_date']) && !empty($data['trial_report_date'])){
                $trialReports = $trialReports->where('trial_report_date',$data['trial_report_date']);
            }

            if(isset($data['customer_register_request_id']) && !empty($data['customer_register_request_id'])){
                $trialReports = $trialReports->where('customer_register_request_id',$data['customer_register_request_id']);
            }
            $trialReports = $trialReports->orderby('id','DESC')->get();
            $message = 'Trial Report has been added successfully';
            $result['report_base_url'] = url('/DvrDocuments/').'/';
            $result['trial_reports'] = $trialReports;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function createTrialReport(Request $request){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])){
            $data = $request->all();
            $userid = $resp['user']['id'];
            TrialReport::createOrUpdate($request,$userid);
            $message = 'Trial Report has been added successfully';
            return response()->json(apiSuccessResponse($message),200);
        }
    }

    public function editTrialReport(Request $request){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])){
            $data = $request->all();
            $userid = $resp['user']['id'];
            TrialReport::createOrUpdate($request,$userid);
            $message = 'Trial Report has been updated successfully';
            return response()->json(apiSuccessResponse($message),200);
        }
    }

    public function deleteTrialReport(Request $request,$trialreportid){
       if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                TrialReport::find($trialreportid)->delete();
                $message = 'Trial Report has been deleted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function generateTrialReportPdf(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $trial_report_id = $data['trial_report_id'];
                $costshow = 0;
                if($data['cost'] ==1){
                    $costshow = 1;
                }
                $trialReport = TrialReport::with(['customer','customer_register_request','other_team_member_info','feedback_info','baths'])->where('id',$trial_report_id)->first();
                $trialReport = json_decode(json_encode($trialReport),true);
                //echo "<pre>"; print_r($trialReport); die;
                ini_set('memory_limit','256M');
                $filename = $trial_report_id.".pdf";
                PDF::loadView('trial_report_pdf',compact('trialReport','costshow'))->save('TrialReportPdfs/'.$filename);
                $filepath = url('TrialReportPdfs/'.$filename);
                $result['pdf_url'] = $filepath;
                $message = "Pdf has been fetched successfully";
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function updateTrialReportStatus(Request $request){
       if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $trialReport = TrialReport::find($data['trial_report_id']);
                if(isset($data['status_with_cost'])){
                    $trialReport->status_with_cost = $data['status_with_cost'];
                }
                if(isset($data['status_without_cost'])){
                    $trialReport->status_without_cost = $data['status_without_cost'];
                }
                $trialReport->save();
                $message = 'Trial Report status has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }
    public function updateTrialReportCanShare(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $trial_report = TrialReport::find($data['trial_report_id']);
                if(isset($data['can_share_with_cost'])){
                    $trial_report->can_share_with_cost = $data['can_share_with_cost'];
                }
                if(isset($data['can_share_without_cost'])){
                    $trial_report->can_share_without_cost = $data['can_share_without_cost'];
                }
                $trial_report->save();
                $message = 'Updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function linkDvrTrial(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                if($data['type'] == "link"){
                    $dvr = Dvr::find($data['dvr_id']);
                    $trial_report = TrialReport::find($data['trial_report_id']);
                    if ($dvr->customer_id == $trial_report->customer_id && $dvr->customer_register_request_id ==  $trial_report->customer_register_request_id && $dvr->dvr_date == $trial_report->trial_report_date) {
                        Dvr::where('id',$data['dvr_id'])->update(['trial_report_id'=>$data['trial_report_id']]);
                        TrialReport::where('id',$data['trial_report_id'])->update(['dvr_id'=>$data['dvr_id']]);
                        $linkDvrTrialReport = new DvrTrialReport;
                        $linkDvrTrialReport->dvr_id = $data['dvr_id'];
                        $linkDvrTrialReport->trial_report_id = $data['trial_report_id'];
                        $linkDvrTrialReport->save();
                    }else{
                        $message = "Unable to link trial report";
                        return response()->json(apiErrorResponse($message),422);
                    }
                }else{
                    Dvr::where('id',$data['dvr_id'])->update(['trial_report_id'=>NULL]);
                    TrialReport::where('id',$data['trial_report_id'])->update(['dvr_id'=>NULL]);
                    DvrTrialReport::where(['dvr_id'=>$data['dvr_id'],'trial_report_id'=>$data['trial_report_id']])->delete();
                }
                $message = 'Updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function feedbackHistory(Request $request){
      if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['user'])){
                $data = $request->all();
                $rules = [
                    "feedback_id"=>"required|exists:feedback,id",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $histories = Feedback::feedbackHistories($data);
                $result['histories'] = $histories;
                $message = "History has been fetched successfully";
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }  
    }

    public function customerRegisterInfo(){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])){
            $reporting_resp = User::getReportingUsers($resp['user']['id']);
            $userids = \DB::table('user_departments')->where('department_id',2)->pluck('user_id')->toArray();
            //$userIds = \App\UserDepartmentRegion::wherein('sub_region_id',$reporting_resp['sub_region_ids'])->pluck('user_id')->toArray();
           // $users  = \DB::table('users')->wherein('id',$userIds)->get();
            $users  = \DB::table('users')->whereNotin('id',[16,17])->wherein('id',$userids)->where('status',1)->get();
            $dealers =  \DB::table('dealers')->select('id','business_name','owner_name')->whereNotin('id',[1,5,7])->whereNULL('parent_id')/*->wherein('city',$reporting_resp['cities'])*/->where('status',1)->get();
            $message = "Fetched successfully";
            $result['users'] =  $users;
            $result['dealers'] =  $dealers;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function customerInvoiceSales(Request $request){
        $data = $request->all();
        $saleInvoices = SaleInvoice::with(['dealer','customer','invoice_items','purchase_order'=>function($query){
            $query->select('id','customer_purchase_order_no');
        }]);
        if(isset($data['dealer_invoice_no'])){
            $saleInvoices = $saleInvoices->where(['dealer_invoice_no'=>$data['dealer_invoice_no']]);
        }
        if(isset($data['customer_id'])){
            $saleInvoices = $saleInvoices->where(['customer_id'=>$data['customer_id']]);
        }
        $saleInvoices = $saleInvoices->get();
        $message = 'Sale Invoice has been fetched successfully';
        $result['sale_invoices'] = $saleInvoices;
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function directCustomerProducts($customerid)
    {
        $productIds = \App\CustomerDiscount::where('customer_id', $customerid)
                        ->pluck('product_id')
                        ->toArray();

        $products = Product::with([
            'productpacking',
            'pricings',
            'product_stages',
            'customerDiscounts' => function ($query) use ($customerid) {
                $query->where('customer_id', $customerid);
            }
        ])
        ->whereIn('id', $productIds)
        ->where('status', 1)
        ->get();

        $result['products'] = $products;
        $message = "Products have been fetched successfully";
        return response()->json(apiSuccessResponse($message, $result), 200);
    }

    public function saveSalesProjection(Request $request)
    {
        $data = $request->all(); 
        $resp = $this->resp;
        if($resp['status'] && isset($resp['user'])){
            $rules= [
                'action' => 'required|in:SAVE,SUBMIT',
                'month_year' => 'required|string',
                'customers' => 'required|array',
                'customers.*.customer_id' => 'required|integer',
                'customers.*.products' => 'required|array',
                'customers.*.products.*.product_id' => 'required|integer',
                'customers.*.products.*.projected_qty' => 'required|numeric|min:0',
            ];
            $customMessages = [];
            $validator = Validator::make($data,$rules,$customMessages);
            if ($validator->fails()) {
                return response()->json(validationResponse($validator),422); 
            }
            $userId = $resp['user']['id'];

            DB::beginTransaction();
            try {
                foreach ($request->customers as $customer) {
                    $customerId = $customer['customer_id'];
                    
                    foreach ($customer['products'] as $product) {
                        $productId = $product['product_id'];

                        SalesProjection::where('customer_id', $customerId)
                            ->where('product_id', $productId)
                            ->where('month_year', $request->month_year)
                            ->where('created_by', $userId)
                            ->delete();
                        

                        SalesProjection::create([
                            'customer_id' => $customerId,
                            'product_id' => $productId,
                            'projected_qty' => $product['projected_qty'],
                            'action' => $request->action,
                            'month_year' => $request->month_year,
                            'created_by' => $userId,
                        ]);
                    }
                }

                DB::commit();
                $message = "Sales projections saved successfully";
                return response()->json(apiSuccessResponse($message), 200);

            } catch (\Exception $e) {
                DB::rollBack();
                $message = "Failed to save projections";
                return response()->json(apiErrorResponse($message),422);
            }
        }
    }


    public function getSalesProjections(Request $request)
    {
        $data = $request->all(); 
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['user'])) {
            $rules = [
                'month_year' => 'required|string',
                'user_ids'   => 'nullable|string', // comma separated
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422); 
            }

            // Explode by #
            $monthYearList = explode('#', $request->month_year);

            // Clean whitespace
            $monthYearList = array_map('trim', $monthYearList);

            // check user_ids param
            $userIds = [];
            if (!empty($data['user_ids'])) {
                $userIds = array_filter(array_map('trim', explode(',', $data['user_ids'])));
            }

            // fallback to current user if no user_ids provided
            if (empty($userIds)) {
                $userIds = [$resp['user']['id']];
            }

            $salesProjections = SalesProjection::with([
                'customer',
                'product' => function ($query) {
                    $query->with(['pricings','productpacking']);
                }
            ])
            ->whereIn('month_year', $monthYearList)
            ->whereIn('created_by', $userIds)
            ->get();

            $result['sales_projections'] = $salesProjections;
            $message = "Sales projections fetched successfully";

            return response()->json(apiSuccessResponse($message, $result), 200);
        }
    }


    public function getMonthlyProjectionStatus(Request $request){
        $data = $request->all(); 
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['user'])) {
            $rules = [
                'month_years' => 'required|string',
                'user_ids'    => 'nullable|string', // comma separated
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422); 
            }

            // check user_ids param
            $userIds = [];
            if (isset($data['user_ids']) && !empty($data['user_ids'])) {
                $userIds = array_filter(array_map('trim', explode(',', $data['user_ids'])));
            }

            // fallback to current user if no user_ids provided
            if (empty($userIds)) {
                $userIds = [$resp['user']['id']];
            }

            $monthYears = explode('#', $data['month_years']);

            // prepare placeholders
            $userPlaceholders  = implode(',', array_fill(0, count($userIds), '?'));
            $monthPlaceholders = implode(',', array_fill(0, count($monthYears), '?'));

            $results = DB::select("
                SELECT month_year, action, updated_at, created_by FROM (
                    SELECT *, ROW_NUMBER() OVER (PARTITION BY month_year, created_by ORDER BY id ASC) AS rn
                    FROM sales_projections
                    WHERE created_by IN ($userPlaceholders) 
                    AND month_year IN ($monthPlaceholders)
                ) AS sub
                WHERE rn = 1
            ", array_merge($userIds, $monthYears));

            $monthYearMap = collect($results)->map(function ($item) {
                return [
                    'month_year' => $item->month_year,
                    'action'     => $item->action,
                    'updated_at' => $item->updated_at,
                    'user_id'    => $item->created_by, // add user info if needed
                ];
            })->toArray();

            $result['details'] = $monthYearMap;
            $message = "Data fetched successfully";
            return response()->json(apiSuccessResponse($message, $result), 200);
        }
    }

    public function updateSalesProjectionAction(Request $request)
    {
        $data = $request->all();
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['user'])) {
            $rules = [
                'sale_projection_ids' => 'required|string',
                'action' => 'required|in:SAVE,SUBMIT',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422);
            }

            $ids = explode(',', $data['sale_projection_ids']);
            $ids = array_filter(array_map('trim', $ids)); // remove whitespace and empty values

            try {
                SalesProjection::whereIn('id', $ids)->update([
                    'action' => $data['action'],
                ]);

                $message = "Sales projection actions updated successfully.";
                return response()->json(apiSuccessResponse($message), 200);

            } catch (\Exception $e) {
                $message = "Failed to update sales projection actions.";
                return response()->json(apiErrorResponse($message),422);
            }
        }
    }


    public function updateCustomerLatitudeLongitude(Request $request)
    {
        $data = $request->all();
        $resp = $this->resp; // Assuming you are checking auth or something similar

        if ($resp['status'] && isset($resp['user'])) {
            $rules = [
                'customer_id' => 'required|integer|exists:customers,id',
                'latitude'    => 'required|numeric',
                'longitude'   => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422);
            }

            try {
                \App\Customer::where('id', $data['customer_id'])->update([
                    'latitude'  => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);

                $message = "Customer location updated successfully.";
                return response()->json(apiSuccessResponse($message), 200);

            } catch (\Exception $e) {
                //\Log::error("Failed to update customer location: ".$e->getMessage());

                $message = "Failed to update customer location.";
                return response()->json(apiErrorResponse($message), 422);
            }
        } else {
            $message = "Unauthorized request.";
            return response()->json(apiErrorResponse($message), 401);
        }
    }


    public function markAttendance(Request $request)
    {
        $resp = $this->resp;
        if (!$resp['status'] || !isset($resp['user'])) {
            return response()->json(apiErrorResponse("Token expired or invalid user"), 422);
        }
        
        // Time check: only allow between 9:00 AM and 10:00 AM
        $currentTime = date('H:i'); // current time in 24-hour format
        $startTime = env('ATTENDANCE_START_TIME');
        $endTime   = env('ATTENDANCE_END_TIME');
        $halfdayTime     = env('ATTENDANCE_HALFDAY_TIME');

        if ($currentTime < $startTime || $currentTime > $endTime) {
            $formattedStart = date('g:i A', strtotime($startTime)); // 9:00 AM
            $formattedEnd   = date('g:i A', strtotime($endTime));   // 03:00 PM
            return response()->json(apiErrorResponse("Attendance can only be marked between {$formattedStart} and {$formattedEnd}"), 422);
        }
        

        $userId = $resp['user']['id'];
        $date   = $request->input('date') ?? now()->toDateString();
        $status = $request->input('status');
        $remarks = $request->input('remarks') ?? '';
        // ✅ Half-day check
        $secondaryStatus = null;
        if ($currentTime > $halfdayTime) {
            $secondaryStatus = 'First Half Leave';
        }
        if (!in_array($status, ['present','absent','leave','holiday'])) {
            return response()->json(apiErrorResponse("Invalid status"), 422);
        }

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $userId, 'date' => $date],
            [
                'status' => $status, 
                'latitude' => $request->input('latitude') ?? '',
                'longitude' => $request->input('longitude') ?? '',
                'remarks' => $remarks,
                'secondary_status'=> $secondaryStatus
            ]
        );

        return response()->json(apiSuccessResponse("Attendance marked", $attendance));
    }

    /**
     * 2. Attendance List (monthly)
     */
    public function attendanceList(Request $request)
    {
        $resp = $this->resp;
        if (!$resp['status'] || !isset($resp['user'])) {
            return response()->json(apiErrorResponse("Token expired or invalid user"), 422);
        }

        // If user_id is provided in request, use it, else fallback to logged-in user
        $userId = $request->input('user_id', $resp['user']['id']);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(apiErrorResponse("start_date and end_date are required"), 422);
        }

        // Get user attendance
        $records = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        // DB holidays
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

        // Map attendance
        $statusList = $records->map(function($r){
            return $r;
        });

        // Map DB holidays
        $holidayList = $holidays->map(function($h){
            return [
                'date' => $h->date,
                'reason' => $h->reason
            ];
        })->toArray();

        // Generate Sundays as holidays
        $sundays = [];
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day') // include end date
        );

        foreach ($period as $date) {
            if ($date->format('w') == 0) { // 0 = Sunday
                $sundays[] = [
                    'date' => $date->format('Y-m-d'),
                    'reason' => 'Sunday'
                ];
            }
        }

        // Merge DB holidays + Sundays
        $holidayList = array_merge($holidayList, $sundays);

        // Group by month
        $grouped = [];
        foreach ($statusList as $record) {
            $month = date('m', strtotime($record['date']));
            $year  = date('Y', strtotime($record['date']));
            $monthName = date('F', strtotime($record['date'])); // Full month name
            $monthShortName = date('M', strtotime($record['date'])); // Full month name
            $key = $year.'-'.$month;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'month_name' => $monthName,
                    'month_short_name' => $monthShortName,
                    'month' => $month,
                    'year' => $year,
                    'status' => [],
                    'holidays' => []
                ];
            }
            $grouped[$key]['status'][] = $record;
        }

        foreach ($holidayList as $holiday) {
            $month = date('m', strtotime($holiday['date']));
            $year  = date('Y', strtotime($holiday['date']));
            $key = $year.'-'.$month;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'month' => $month,
                    'year' => $year,
                    'status' => [],
                    'holidays' => []
                ];
            }
            $grouped[$key]['holidays'][] = $holiday;
        }

        // Sort months in reverse (latest first)
        krsort($grouped);

        $startTime = env('ATTENDANCE_START_TIME');
        $endTime   = env('ATTENDANCE_END_TIME');
        $halfDayTime   = env('ATTENDANCE_HALFDAY_TIME');
        $result['attendances'] = array_values($grouped);
        $result['attendance_start_time'] = $startTime;
        $result['attendance_end_time'] = $endTime;
        $result['attendance_halfday_time'] = $halfDayTime;

        return response()->json(apiSuccessResponse("Attendance list", $result));
    }

    /**
     * 3. Leave Request
     */
    public function leaveRequest(Request $request)
    {
        $resp = $this->resp;
        if (!$resp['status'] || !isset($resp['user'])) {
            return response()->json(apiErrorResponse("Token expired or invalid user"), 422);
        }
        $userId = $resp['user']['id'];

        $dates = $request->input('dates');
        $remarks = $request->input('remarks');

        if (empty($dates)) {
            return response()->json(apiErrorResponse("At least one date required"), 422);
        }

        foreach ($dates as $d) {
            Attendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $d],
                ['status' => 'leave', 'remarks' => $remarks]
            );
        }

        return response()->json(apiSuccessResponse("Leave marked successfully"));
    }

    public function holidaysByYears(Request $request)
    {
        $years = $request->input('years', []);

        // If empty, take current year
        if (empty($years)) {
            $years = [date('Y')];
        }

        // Fetch holidays from DB (exclude Sundays)
        $holidays = Holiday::whereIn(DB::raw('YEAR(date)'), $years)
            ->whereRaw('WEEKDAY(date) != 6') // Exclude Sundays (MySQL: Sunday=6 in WEEKDAY())
            ->orderBy('date')
            ->get(['date', 'reason']);

        $holidayList = $holidays->map(function($h){
            return [
                'date' => $h->date,
                'reason' => $h->reason,
            ];
        });
        $result['holidays'] = $holidayList;
        return response()->json(apiSuccessResponse("Holidays fetched successfully", $result));
    }

    public function markEmergencyLeave(Request $request)
    {
        $resp = $this->resp;
        if (!$resp['status'] || !isset($resp['user'])) {
            return response()->json(apiErrorResponse("Token expired or invalid user"), 422);
        }
        $data = $request->all();

        $userId = $resp['user']['id'];

        $rules = [
            'attendance_id'     => 'required|integer|exists:attendances,id',
            'secondary_status'  => 'required|string',
            'leave_time'        => 'required|date_format:H:i',
            'remarks'           => 'required',
        ];
        $customMessages = [];
        $validator = Validator::make($data,$rules,$customMessages);
        if ($validator->fails()) {
            return response()->json(validationResponse($validator),422); 
        }

        $attendanceId = $data['attendance_id'];
        $secondaryStatus = $data['secondary_status'];
        $leaveTime = $data['leave_time'];
        $remarks = $data['remarks'];

        // ✅ Fetch config times from .env
        $emergencyStart = env('EMERGENCY_LEAVE_START_TIME', '09:00');
        $emergencyEnd   = env('EMERGENCY_LEAVE_END_TIME', '21:00');

        // ✅ Check if current time is within emergency leave window
        if ($leaveTime < $emergencyStart || $leaveTime > $emergencyEnd) {
            $formattedStart = date('g:i A', strtotime($emergencyStart));
            $formattedEnd   = date('g:i A', strtotime($emergencyEnd));
            return response()->json(apiErrorResponse("Emergency leave can only be marked between {$formattedStart} and {$formattedEnd}"), 422);
        }

        // ✅ Find attendance record
        $attendance = Attendance::where('id', $attendanceId)
            ->where('user_id', $userId)
            ->first();

        if (!$attendance) {
            return response()->json(apiErrorResponse("Attendance record not found."), 404);
        }

        // ✅ Allow emergency leave only if status is "Present"
        if (strtolower($attendance->status) !== 'present') {
            return response()->json(apiErrorResponse("Emergency leave can only be applied for Present status."), 422);
        }

        // ✅ Update record
        $attendance->update([
            'secondary_status' => $secondaryStatus,
            'leave_time'       => $leaveTime,
            'remarks'          => $remarks
        ]);

        return response()->json(apiSuccessResponse("Emergency leave marked successfully.", [
            'attendance_id' => $attendance->id,
            'secondary_status' => $secondaryStatus,
            'leave_time' => $leaveTime,
        ]));
    }


    public function sendOtpForCustomerContact(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            $resp = $this->resp;

            // ✅ Validation (only mobile number needed)
            $rules = [
                'mobile_number' => 'bail|required|numeric|digits:10'
            ];
            $customMessages = [];
            $validator = Validator::make($data, $rules, $customMessages);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422);
            }

            if ($resp['status'] && isset($resp['user'])) {

                // Generate OTP
                $otp = rand(100000, 999999);
                $params['mobile'] = $data['mobile_number'];
                $params['message'] = "Dear Customer, Your code to verify Greenwave Executive's visit is ".$otp.". GREENWAVE GLOBAL LTD";
                sendSms($params);
                // Store OTP temporarily for verification
                $key = 'contact_otp_' . $data['mobile_number'];
                \Cache::put($key, $otp, 300); // valid for 5 minutes

                // sendOtpSms($data['mobile_number'], $otp);

                return response()->json(apiSuccessResponse("OTP sent successfully"), 200);
            }
        }
    }


    public function createCustomerContact(Request $request)
    {
        if ($request->isMethod('post')) {

            $data = $request->all();
            $resp = $this->resp;

            // ✅ Validation Rules
            $rules = [
                'customer_id'                   => 'bail|required_without:customer_register_request_id|numeric',
                'customer_register_request_id'  => 'bail|required_without:customer_id|numeric',
                'name'                          => 'bail|required|string',
                'mobile_number'                 => 'bail|required|numeric|digits:10',
                'otp'                           => 'bail|required|numeric|digits:6',
                'dvr_id'                        => 'bail|required|numeric'
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422);
            }

            if ($resp['status'] && isset($resp['user'])) {

                // ✅ 1. Check if mobile already exists
                $existing = \App\CustomerContact::where('mobile_number', $data['mobile_number'])->first();

                if ($existing) {
                    if ($existing->status == 'active') {
                        return response()->json(apiErrorResponse("This contact number has already been used for another customer"), 400);
                    }
                    // inactive → allowed
                }

                // ✅ 2. Validate OTP
                $key = 'contact_otp_' . $data['mobile_number'];
                $storedOtp = \Cache::get($key);

                if (!$storedOtp) {
                    return response()->json(apiErrorResponse("The OTP you entered is incorrect or has expired."), 400);
                }

                if ($storedOtp != $data['otp']) {
                    return response()->json(apiErrorResponse("Invalid OTP."), 400);
                }

                // Pick whichever ID is present
                $customerId = $data['customer_id'] ?? null;
                $customerRegisterId = $data['customer_register_request_id'] ?? null;

                // ✅ 3. Create Contact
                $contact = \App\CustomerContact::create([
                    'customer_id'                 => $customerId,
                    'customer_register_request_id'=> $customerRegisterId,
                    'name'                        => $data['name'],
                    'designation'                 => $data['designation'] ?? null,
                    'mobile_number'               => $data['mobile_number'],
                    'created_by'                  => $resp['user']['id'],
                    'status'                      => 'active',
                ]);

                // delete OTP
                \Cache::forget($key);

                // 4. Update DVR table
                $dvr = \App\Dvr::find($data['dvr_id']);

                if (!$dvr) {
                    return response()->json(apiErrorResponse("Invalid DVR ID"), 400);
                }

                $dvr->customer_contact_id = $contact->id;
                $dvr->dvr_verified_date_time = date('Y-m-d H:i:s');
                $dvr->save();

                return response()->json(apiSuccessResponse("Customer contact created and DVR updated successfully", [
                    'contact' => $contact,
                    'dvr'     => $dvr
                ]), 200);
            }
        }
    }



    public function verifyExistingContactOtp(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            $resp = $this->resp;

            // ✅ Validation
            $rules = [
                'contact_id' => 'bail|required|numeric',
                'otp'        => 'bail|required|numeric|digits:6',
                'dvr_id'     => 'bail|required|numeric'
            ];
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422);
            }

            if ($resp['status'] && isset($resp['user'])) {

                $contact = \App\CustomerContact::find($data['contact_id']);

                if (!$contact) {
                    return response()->json(apiErrorResponse("Invalid contact ID"), 400);
                }

                if ($contact->status != 'active') {
                    return response()->json(apiErrorResponse("This contact is not active."), 400);
                }

                $key = 'contact_otp_' . $contact->mobile_number;
                $storedOtp = \Cache::get($key);

                if (!$storedOtp) {
                    return response()->json(apiErrorResponse("The OTP you entered is incorrect or has expired."), 400);
                }

                if ($storedOtp != $data['otp']) {
                    return response()->json(apiErrorResponse("Invalid OTP."), 400);
                }

                // ✅ OTP Success — Now update DVR
                $dvr = \App\Dvr::find($data['dvr_id']);

                if (!$dvr) {
                    return response()->json(apiErrorResponse("Invalid DVR ID"), 400);
                }

                // update dvr fields
                $dvr->dvr_verified_date_time = date('Y-m-d H:i:s');
                $dvr->customer_contact_id = $contact->id;
                $dvr->save();

                // ✅ clear otp
                \Cache::forget($key);

                return response()->json(apiSuccessResponse("Contact verified and DVR updated successfully"), 200);
            }
        }
    }


    public function getCustomerContacts(Request $request)
    {
        if ($request->isMethod('post')) {

            $data = $request->all();
            $resp = $this->resp;

            // ✅ Validation (at least one is required)
            $rules = [
                'customer_id'                  => 'bail|required_without:customer_register_request_id|numeric',
                'customer_register_request_id' => 'bail|required_without:customer_id|numeric',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422);
            }

            if ($resp['status'] && isset($resp['user'])) {

                // Determine which ID to use
                $customerId = $data['customer_id'] ?? null;
                $customerRegisterId = $data['customer_register_request_id'] ?? null;

                // Build query
                $query = \App\CustomerContact::where('status', 'active');

                if ($customerId) {
                    $query->where('customer_id', $customerId);
                }

                if ($customerRegisterId) {
                    $query->where('customer_register_request_id', $customerRegisterId);
                }

                $contacts = $query->get();

                return response()->json(apiSuccessResponse("Data fetched successfully", [
                    'persons' => $contacts
                ]), 200);
            }
        }
}


}
