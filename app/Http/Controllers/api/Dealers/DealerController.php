<?php

namespace App\Http\Controllers\api\Dealers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\Dealer;
use App\Product;
use App\PurchaseOrder;
use App\DealerLinkedProduct;
use App\PurchaseOrderItem;
use App\PurchaseOrderItemDiscount;
use App\PurchaseOrderItemRawMaterial;
use App\DealerSpecialDiscount;
use App\QtyDiscount;
use App\MaterialApproval;
use Validator;
use DB;
use App\Customer;
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
use App\FreeSamplingStock;
use App\SamplingSaleInvoice;
use App\DealerPurchaseProjection;
class DealerController extends Controller
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
	        $dealer = Dealer::with(['contact_persons','linked_products'])->where(['owner_mobile'=>$data['mobile']])->first();
	        if($dealer && password_verify($data['password'], $dealer->password)){
	        	$notificationToken =''; $appDetails='';
	        	if(isset($data['notification_token'])){
	        		$notificationToken = $data['notification_token'];
	        	}
	        	if(isset($data['app_details'])){
	        		$appDetails = $data['app_details'];
	        	}
	        	$authorizationToken = encrypt('dealer##-'.$data['mobile']);
	        	$tokenDetails = array('type'=>'dealer','dealer_id'=>$dealer->id,'notification_token'=>$notificationToken,'app_details'=>$appDetails,'login_device'=>$data['login_device'],'auth_token'=>$authorizationToken);
	        	AuthToken::create($tokenDetails);
	        	$message = 'Logged in successfully';
	        	$result['token'] = $authorizationToken;
	        	$result['dealer'] = $dealer;
                $result['dealer']['dealer_roles'] = getUserRoles($dealer->app_roles,'dealer');
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
            //Verify Dealer
            $dealer = Dealer::with(['contact_persons','linked_products','parent_dealer_info'])->where(['owner_mobile'=>$data['mobile']])->first();
            if(is_object($dealer)){
                if($data['step'] == 1){
                    if($data['mobile'] == "9898989898" || $data['mobile'] == "9876543210" || $data['mobile'] =="9988771234" || $data['mobile'] =="7887880136"){
                        $otp = 123456;
                    }else{
                        $otp =  rand(100000, 999999);
                        $params['mobile'] = $data['mobile'];
                        $params['message'] = "Your OTP for Login is ".$otp.". -GREENWAVE GLOBAL LTD";
                        sendSms($params);
                    }
                    $dealer->recent_otp = $otp;
                    $dealer->save();
                    $message = "Otp has been sent successfully to your mobile number";
                    return response()->json(apiSuccessResponse($message),200);
                }else if($data['step'] == 2){
                    if($dealer->recent_otp == $data['otp']){
                        $notificationToken =''; $appDetails='';
                        if(isset($data['notification_token'])){
                            $notificationToken = $data['notification_token'];
                        }
                        if(isset($data['app_details'])){
                            $appDetails = $data['app_details'];
                        }
                        $authorizationToken = encrypt('dealer##-'.$data['mobile']);
                        $tokenDetails = array('type'=>'dealer','dealer_id'=>$dealer->id,'notification_token'=>$notificationToken,'app_details'=>$appDetails,'login_device'=>$data['login_device'],'auth_token'=>$authorizationToken);
                        AuthToken::create($tokenDetails);
                        $message = 'Logged in successfully';
                        $result['token'] = $authorizationToken;
                        $result['dealer'] = $dealer;
                        $result['dealer']['dealer_roles'] = getUserRoles($dealer->app_roles,'dealer');
                        return response()->json(apiSuccessResponse($message,$result),200);
                    }else{
                        $message = "You have entered wrong OTP";
                        return response()->json(apiErrorResponse($message),422);
                    }
                }
            }else{
                $message = "You have entered wrong mobile number";
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
            if($resp['status'] && isset($resp['dealer'])) {
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
                    $old_password = $data['old_password'];
                    if(password_verify($old_password, $resp['dealer']['password'])) {
                        $dealer = Dealer::find($resp['dealer']['id']);
                        $dealer->password = bcrypt($data['new_password']);
                        $dealer->save();
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

    public function profile(Request $request){
    	if($request->isMethod('post')){
			$resp = $this->resp;
    		if($resp['status']){
    			if(isset($resp['dealer'])){
    				$message ='Profile has been fetched successfully'; 
    				$result['dealer'] = $resp['dealer'];
                    $result['dealer']['dealer_roles'] = getUserRoles($resp['dealer']['app_roles'],'dealer');
                    $addOnUsers = Dealer::addOnUsers($resp['dealer']['id']);
                    $noOfUsersLoggedIn = AuthToken::where('type','dealer')->where('dealer_id',$resp['dealer']['id'])->count();
                    $result['dealer']['no_of_users_logged_in'] = $noOfUsersLoggedIn;
                    $result['dealer']['addon_users'] = $addOnUsers;
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

    public function addOnUsers(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                if(isset($resp['dealer'])){
                    $message ='Add On users has been fetched successfully'; 
                    $addOnUsers = Dealer::addOnUsers($resp['dealer']['id']);
                    $result['addon_users'] = $addOnUsers;
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

    public function addEditAddOnUser(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            $data = $request->all();
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                if(isset($data['id']) && !empty($data['id'])){
                    $type ="update";
                    $emailunique = "unique:dealers,email,".$data['id'];
                    $mobileunique = "unique:dealers,owner_mobile,".$data['id'];
                }else{ 
                    $type ="add";
                    $emailunique = "unique:dealers,email";
                    $mobileunique = "unique:dealers,owner_mobile";
                }
                $validator = Validator::make($request->all(), [
                        //'dealer_type'   =>  'bail|required',
                        'name'   =>  'bail|required',
                        //'designation'   =>  'bail',
                        'email'   => 'bail|email|'.$emailunique,
                        'mobile' => 'bail|required|numeric|digits:10|'.$mobileunique,
                        'status' => 'bail|required',
                    ]
                );
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }else{
                    if($type =="add"){
                        $dealer = new Dealer; 
                    }else{
                        $dealer = Dealer::find($data['id']);
                    }
                    $dealer->parent_id = $resp['dealer']['id'];
                    $dealer->name = $data['name'];
                    $dealer->designation = $data['designation'];
                    $dealer->department = $data['department'];
                    $dealer->email = $data['email'];
                    $dealer->owner_mobile = $data['mobile'];
                    $dealer->dealer_type = "dealer";
                    $dealer->status = $data['status'];
                    $dealer->show_class = "No";
                    if(isset($data['show_class'])){
                         $dealer->show_class = $data['show_class'];
                    }
                    $dealer->app_roles = "";
                    if(isset($data['app_roles'])){
                        $dealer->app_roles = $data['app_roles'];
                    }
                    $dealer->save();
                    $message = "success";
                    return response()->json(apiSuccessResponse($message),200);
                }
            }
        }
    }

    public function deleteAddonUser(Request $request){
        $data = $request->all();
        Dealer::where('id',$data['dealer_id'])->update(['status'=>0,'is_delete'=>1]);
        $message = "Deleted successfully";
        return response()->json(apiSuccessResponse($message),200);
    }

    public function logout(Request $request){
    	if($request->isMethod('post')){
			$resp = $this->resp;
    		if($resp['status']){
    			if(isset($resp['dealer'])){
    				$message ='Logged Out successfully';
    				AuthToken::where('auth_token',$resp['token'])->where('type','dealer')->delete();
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
                if(isset($resp['dealer'])){
                    $message ='Your account has been logged out from all devices';
                    AuthToken::where('type','dealer')->where('dealer_id',$resp['dealer']['id'])->delete();
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
    	if($request->isMethod('post')){
			$resp = $this->resp;
    		if($resp['status']){
                $data = $request->all();
    			if(isset($resp['dealer'])){
    				$message ='Customers Fetched successfully';
                    $dealerIds = Dealer::getParentChildDealers($resp['dealer']);
    				$getCustomers = Customer::with(['corporate_discount','product_discounts','employees','user_customer_shares','dealer'])->whereIN('dealer_id',$dealerIds)->where('status',1)->get();
                    $products = Product::with(['productpacking','pricings','product_stages'])->where('status',1);
                    if(isset($data['product_types']) && !empty($data['product_types'])) {
                        $productTypes = explode(',',$data['product_types']);
                        $products = $products->whereIn('is_trader_product',$productTypes);
                    }else{
                        $products = $products->where('is_trader_product',0);
                    }
                    $products = $products->get();
                    $products = json_decode(json_encode($products),true);
                    foreach($products as $pkey => $product){
                        foreach($product['pricings'] as $pricekey=> $proprice){
                            $class = geClass($proprice['dealer_markup']);
                            $products[$pkey]['pricings'][$pricekey]['class'] =$class; 
                        }
                    }
    				$result['customers'] = $getCustomers;
                    $result['products'] = $products;
                    $discounts = \App\ProductDiscount::get();
                    $result['product_discounts'] = $discounts;
    				return response()->json(apiSuccessResponse($message,$result),200);
    			}else{
    				$message = "Unable to fetch. PLease try again after sometime";
		    		return response()->json(apiErrorResponse($message),422);
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

    public function purchaseOrder(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                $rules = [
                    'customer_id'    => 'bail|exists:customers,id',
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
                    if(isset($data['customer_id']) && !empty($data['customer_id'])){
                        $action = 'dealer_customer';
                    }else{
                        $action = 'dealer';
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

    public function traderPurchaseOrder(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
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
                    $action = 'dealer';
                    $data['action'] = $action;
                    $data['trader_po'] = true;
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
                if(isset($resp['dealer'])){
                    $dealerIds = Dealer::getParentChildDealers($resp['dealer']);
                    if(isset($_GET['include']) && $_GET['include']=='dealer'){
                        $purchaseOrders = PurchaseOrder::with(['customer','customer_employee','orderitems','adjust_items','cancel_items','discounts','saleinvoices','dealer'])->whereIn('dealer_id',$dealerIds)->where('action','dealer');
                        if(isset($_GET['is_trader'])){
                            $purchaseOrders = $purchaseOrders->where('trader_po',1);
                        }else{
                            $purchaseOrders = $purchaseOrders->where('trader_po',0);
                        }
                        $purchaseOrders = $purchaseOrders->get()->toArray();
                    }else if(isset($_GET['customer_id'])) {
                        //for specific customers
                        $purchaseOrders = PurchaseOrder::with(['customer','customer_employee','orderitems','adjust_items','cancel_items','discounts','saleinvoices','dealer'])->where('customer_id',$_GET['customer_id']);
                        $purchaseOrders = $purchaseOrders->get()->toArray();
                    }else{
                        $custids = Customer::whereIn('dealer_id',$dealerIds)->pluck('id')->toArray();
                        $purchaseOrders = PurchaseOrder::with(['customer','customer_employee','orderitems','adjust_items','cancel_items','discounts','saleinvoices','dealer'])->wherein('customer_id',$custids)->get()->toArray();
                    }
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

    public function saleInvoice(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                if(isset($resp['dealer'])){
                    $data = $request->all();
                    $rules = [
                        'customer_id'    => 'bail|required|exists:customers,id',
                        'purchase_order_id' => 'bail|required|exists:purchase_orders,id',
                        'sale_invoice_date' => 'bail|required|date_format:Y-m-d',
                        'gst' => 'bail|required|numeric',
                        //'order_items'          =>   'bail|required|array|min:1',
                        //'order_items.*.order_item_id' => 'required|exists:purchase_order_items,id',
                        'order_items.*.product_id' => 'required|exists:products,id',
                        'order_items.*.qty' => 'required|numeric',

                    ];
                    $customMessages = [];
                    $validator = Validator::make($data,$rules,$customMessages);
                    if ($validator->fails()) {
                        return response()->json(validationResponse($validator),422); 
                    }else{
                        DB::beginTransaction();
                        $action = 'dealer_customer';
                        $data['action'] = $action;
                        SaleInvoice::createSaleInvoice($data,$resp);
                        DB::commit();
                        $message ="Sale Invoice has been created successfully";
                        return response()->json(apiSuccessResponse($message),200);
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
    }

    public function updateCustomerPaymentTerm(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                if(isset($resp['dealer'])){
                    $data = $request->all();
                    $rules = [
                        'customer_id'    => 'bail|required|exists:customers,id',
                        'payment_term_type' => 'bail|in:On Bill,On Month End,On Payment',
                        'payment_term' => 'bail',
                        //'payment_discount' => 'required_if:payment_term_type,==,On Bill|nullable|numeric'

                    ];
                    $customMessages = [];
                    $validator = Validator::make($data,$rules,$customMessages);
                    if ($validator->fails()) {
                        return response()->json(validationResponse($validator),422); 
                    }else{
                        DB::beginTransaction();
                        $updatePymtTerm =   Customer::find($data['customer_id']);
                        if(isset($data['payment_term_type'])){
                            $updatePymtTerm->payment_term_type = $data['payment_term_type'];
                        }
                        if(isset($data['payment_term'])){
                            $updatePymtTerm->payment_term = $data['payment_term'];
                        }
                        if(isset($data['payment_discount'])){
                           $updatePymtTerm->payment_discount = $data['payment_discount'];
                        }else{
                            $updatePymtTerm->payment_discount = 0;
                        }
                        $updatePymtTerm->save();
                        DB::commit();
                        $message ="Payment Term has been updated successfully";
                        return response()->json(apiSuccessResponse($message),200);
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
    }

    public function updatePO(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                $data = $request->all();
                $rules = [
                    'purchase_order_id' => 'bail|required|exists:purchase_orders,id',
                    'po_status' => 'bail|required|in:approved,rejected',
                    'po_edited' => 'required_if:po_status,==,approved|nullable',
                    'items' =>   'bail|required_if:po_status,==,approved|array|min:1',
                    'items.*.purchase_order_item_id' => 'required_if:po_status,==,approved|exists:purchase_order_items,id',
                    'items.*.qty' => 'required_if:po_status,==,approved|numeric',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }else{
                    if($data['po_status'] =="approved"){
                        $data['reason'] = "";
                        $purchaseOrderInfo = PurchaseOrder::where('id',$data['purchase_order_id'])->first();
                        $purchaseOrderInfo = json_decode(json_encode($purchaseOrderInfo),true);
                        $totalmarketPrice = 0;
                        foreach($data['items'] as $orderitem){
                            $updatePOitem = PurchaseOrderItem::find($orderitem['purchase_order_item_id']);
                            $updatePOitem->qty = $orderitem['qty'];
                            if(isset($orderitem['spsod'])){
                                $updatePOitem->spsod = $orderitem['spsod']; 
                            }
                            $updatePOitem->save();
                            $totalmarketPrice += $updatePOitem->market_price * $orderitem['qty'];

                            $this->syncDealerProductStock($updatePOitem,$purchaseOrderInfo);

                        }
                        $corporate_discount = (($totalmarketPrice * $purchaseOrderInfo['corporate_discount_per'])/100);
                        $totatSaleAmt = $totalmarketPrice;
                        $getPaymentDis = (($totatSaleAmt * $purchaseOrderInfo['payment_discount_per'])/100);
                        if(isset($purchaseOrderInfo['payment_term_type']) && $purchaseOrderInfo['payment_term_type'] =="On Bill"){
                            $totatSaleAmt = $totatSaleAmt - $getPaymentDis - $corporate_discount;
                        }else{
                            $totatSaleAmt = $totatSaleAmt - $corporate_discount;
                        }
                        $calGST =  ($totatSaleAmt *$purchaseOrderInfo['gst_per']) /100;
                        $totatSaleAmt = $totatSaleAmt + $calGST;
                        $updatePO = PurchaseOrder::find($data['purchase_order_id']);
                        $updatePO->price =  $totalmarketPrice;
                        $updatePO->payment_discount = $getPaymentDis;
                        $updatePO->corporate_discount = $corporate_discount;
                        $updatePO->gst = $calGST;
                        $updatePO->grand_total = $totatSaleAmt;
                        $updatePO->po_edited = $data['po_edited'];
                        $updatePO->edited_by = 'dealer';
                        $updatePO->edited_by_id = $resp['dealer']['id'];
                        $updatePO->save();
                        $message = "Purchase Order has been approved successfully";
                    }else{
                        $message = "Purchase Order has been rejected";
                    }
                    PurchaseOrder::where('id',$data['purchase_order_id'])->update(['po_status'=>$data['po_status'],'reason'=>$data['reason']]);
                    return response()->json(apiSuccessResponse($message),200);
                }
            }
        }else{
            $message = "This method not supported for this route";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function syncDealerProductStock($poItem,$purchaseorder){
        if($purchaseorder['customer_id'] > 0){
            $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$purchaseorder['dealer_id'],'product_id'=>$poItem->product_id])->first();
            if($dealerProd){
                $updatedealerProd = DealerProduct::find($dealerProd->id);
                $updatedealerProd->pending_customer_orders = $dealerProd->pending_customer_orders + $poItem->qty;
                $updatedealerProd->save();
            }
        }
    }

    public function purchaseOrderAdjustment(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                $data = $request->all(); 
                foreach($data['items'] as $itemInfo){
                    $poitem = PurchaseOrderItem::find($itemInfo['order_item_id']);
                    $poadjust = new PurchaseOrderAdjustment; 
                    $poadjust->type = $data['type'];
                    $poadjust->purchase_order_id = $data['purchase_order_id'];
                    $poadjust->purchase_order_item_id = $itemInfo['order_item_id'];
                    $poadjust->qty = $itemInfo['qty'];
                    $poadjust->adjustment_by = 'dealer';
                    $poadjust->ref_id    = $resp['dealer']['id'];
                    $poadjust->reason    = $data['reason'];
                    $poadjust->save();
                    $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$resp['dealer']['id'],'product_id'=>$poitem->product_id])->first();
                    if($dealerProd){
                        $updatedealerProd = DealerProduct::find($dealerProd->id);
                        $updatedealerProd->pending_customer_orders = $dealerProd->pending_customer_orders - $itemInfo['qty'];
                        $updatedealerProd->save();
                    }
                }
                $message = "Request has been recorded successfully";
                return response()->json(apiSuccessResponse($message),200);
            }else{
                $message = "Unable to fetch profile. PLease try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            $message = "GET not supported for this route";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function deletePO(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status']){
                if(isset($resp['dealer'])){
                    $data = $request->all();
                    $rules = [
                        'purchase_order_id' => 'bail|required|exists:purchase_orders,id',
                    ];
                    $customMessages = [];
                    $validator = Validator::make($data,$rules,$customMessages);
                    if ($validator->fails()) {
                        return response()->json(validationResponse($validator),422); 
                    }
                    $totalSaleInvoices = SaleInvoice::where('purchase_order_id',$data['purchase_order_id'])->count();
                    if($totalSaleInvoices == 0){
                        PurchaseOrder::where('id',$data['purchase_order_id'])->delete();
                        $message = "Purchase order has been deleted successfully";
                    }else{
                        $message = "You can not delete Purchase Order as the sale invoices already been generated";
                    }
                    return response()->json(apiSuccessResponse($message),200);
                }
            }
        }
    }

    public function intransitMaterials(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                //echo "<pre>"; print_r($resp); die;
                $saleInvoices = SaleInvoice::with('item')->where('dealer_id',$resp['dealer']['id'])->where('transport_name','!=','')->orderby('dispatch_date','ASC')->where('is_delivered',0)->get();
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


    public function v2intransitMaterials(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                //echo "<pre>"; print_r($resp); die;
                $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
                $lrNos = SaleInvoice::with(['item','dealer'])->whereIN('dealer_id',$dealerIds)->where('transport_name','!=','')->orderby('dispatch_date','ASC')->where('is_delivered',0)->select('lr_no')->groupby('lr_no')->get();
                $lrNos = json_decode(json_encode($lrNos),true);
                $saleInvoices = [];
                foreach($lrNos as $lkey=> $lrNo){
                    $saleInvoices[$lkey]['lr_no'] = $lrNo['lr_no'];
                    $invoices = SaleInvoice::with(['item','dealer'])->whereIN('dealer_id',$dealerIds)->where('transport_name','!=','')->orderby('dispatch_date','ASC')->where('is_delivered',0)->Where('lr_no',$lrNo)->get();
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

    public function updateMaterialDeliver(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $rules = [
                "sale_invoice_ids"    => "required|array|min:1|exists:sale_invoices,id",
                "sale_invoice_ids.*"  => "required|distinct|min:1|exists:sale_invoices,id",
            ];
            $customMessages = [];
            $validator = Validator::make($data,$rules,$customMessages);
            if ($validator->fails()) {
                return response()->json(validationResponse($validator),422); 
            }
            SaleInvoice::wherein('id',$data['sale_invoice_ids'])->update(['is_delivered'=>1]);
            foreach($data['sale_invoice_ids'] as $saleInvid){
                $saleInvoice = SaleInvoice::with('item')->find($saleInvid);
                $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$saleInvoice->dealer_id,'product_id'=>$saleInvoice->item->product_id])->first();
                if($dealerProd){
                    $updatedealerProd = DealerProduct::find($dealerProd->id);
                    $updatedealerProd->in_transit = $dealerProd->in_transit - $saleInvoice->item->qty;
                    $updatedealerProd->stock_in_hand = $dealerProd->stock_in_hand + $saleInvoice->item->qty;
                    $updatedealerProd->save();
                }
            }
            /*$updateSo = SaleInvoice::find($data['sale_invoice_id']);
            $updateSo->is_delivered = 1;
            $updateSo->save();*/
            $message = "Material marked delivered successfully";
            return response()->json(apiSuccessResponse($message),200);
        }
    }


    public function fetchProductsStock(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
                $dealerProducts = DealerProduct::with(['dealer','product'=>function($query){
                    $query->select('id','product_name');
                }])->wherein('dealer_id',$dealerIds)->get();
                $dealerProducts = json_decode(json_encode($dealerProducts),true);
                //echo "<pre>"; print_r($dealerProducts); die;
                $message = "Fetched successfully";
                $result['dealer_products'] = $dealerProducts;
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

    public function debitCreditEntry(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                $rules = [
                    "customer_id"=> "required|exists:customers,id",
                    "entry_date"=> "required|date_format:Y-m-d",
                    "type"=> "required|in:debit,credit",
                    "on_account_of"=> "required",
                    "amount" => "required",
                    "gst_per"=> "required",
                    "gst"=> "required",
                    "total"=> "required",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                $entry = new DebitCreditEntry;
                $entry->dealer_id = $resp['dealer']['id'];
                $entry->customer_id = $data['customer_id'];
                $entry->entry_date = $data['entry_date'];
                $entry->type = $data['type'];
                $entry->on_account_of = $data['on_account_of'];
                $entry->amount = $data['amount'];
                $entry->gst_per = $data['gst_per'];
                $entry->gst = $data['gst'];
                $entry->total = $data['total'];
                $entry->debit_credit_no = $data['debit_credit_no'];
                if(isset($data['month_year'])){
                    $entry->month_year = $data['month_year'];
                }
                $entry->remarks = $data['remarks'];
                $entry->save();
                $message = "Debit Credit entry added successfully";
                return response()->json(apiSuccessResponse($message),200);
            }else{
                $message = "Unable to fetch. Please try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }
    }

    public function deleteDebitCreditEntry($id){
        DebitCreditEntry::where('id',$id)->delete();
        $message = "Debit Credit entry deleted successfully";
        return response()->json(apiSuccessResponse($message),200);
    }

    public function getDebitCreditAccountOf(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
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
                $details = DebitCreditEntry::where('dealer_id',$resp['dealer']['id'])->where('on_account_of',$data['account_of'])->where('month_year',$data['month_year']);
                if(isset($data['customer_id'])){
                    $details = $details->where('customer_id',$data['customer_id']);
                }
                $details = $details->get();
                $result['entries'] = $details;
                $message = "Fetched successfully";
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function customerPurchaseReturn(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $resp['dealer']['id'] = Dealer::getParentDealer($resp['dealer']);
                $data = $request->all();
                $rules = [
                    "customer_id"=> "required|exists:customers,id",
                    "return_date"=> "required|date_format:Y-m-d",
                    'items'          =>   'bail|required|array|min:1',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.qty' => 'required',
                    'items.*.market_price' => 'required',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                //DB::beginTransaction();
                $cpr = new CustomerPurchaseReturn;
                $cpr->dealer_id = $resp['dealer']['id'];
                $cpr->customer_id = $data['customer_id'];
                $empDetails = \App\UserCustomerShare::where('customer_id',$data['customer_id'])->orderby('user_date','DESC')->first();
                if(is_object($empDetails)){
                    $cpr->linked_employee_id =  $empDetails->user_id;
                }
                $cpr->return_date = $data['return_date'];
                $cpr->remarks = $data['remarks']; 
                $cpr->credit_note_no = $data['credit_note_no']; 
                $cpr->save();
                $totalmarketPrice =0;
                foreach($data['items'] as $item){
                    $cprItem = new CustomerPurchaseReturnItem;
                    $cprItem->customer_purchase_return_id = $cpr->id;
                    $cprItem->product_id = $item['product_id'];
                    $cprItem->qty = $item['qty'];
                    $cprItem->market_price = $item['market_price'];
                    $cprItem->subtotal = $item['market_price'] * $item['qty'];
                    $totalmarketPrice += $item['market_price'] * $item['qty'];
                    if(isset($item['spsod'])){
                        $cprItem->spsod = $item['spsod']; 
                    }
                    $cprItem->save();
                    $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$resp['dealer']['id'],'product_id'=>$item['product_id']])->first();
                    if($dealerProd){
                        $updatedealerProd = DealerProduct::find($dealerProd->id);
                        $updatedealerProd->stock_in_hand = $dealerProd->stock_in_hand + $item['qty'];
                    }else{
                        $updatedealerProd = new DealerProduct;
                        $updatedealerProd->dealer_id =$resp['dealer']['id'];
                        $updatedealerProd->product_id =$item['product_id'];
                        $updatedealerProd->stock_in_hand = $item['qty'];
                    }
                    $updatedealerProd->save();
                }

                if(isset($data['corporate_discount'])){
                    $corporate_discount_per =$data['corporate_discount_per'];
                    $corporate_discount = $data['corporate_discount'];
                }else{
                    $corporate_discount_per =0;
                    $corporate_discount = 0;
                }
                $totatSaleAmt = $totalmarketPrice;
                $getPaymentDis = (($totatSaleAmt * $data['payment_discount'])/100);
                if(isset($data['payment_term_type']) && $data['payment_term_type'] =="On Bill"){
                    $totatSaleAmt = $totatSaleAmt - $getPaymentDis - $corporate_discount;
                }else{
                    $totatSaleAmt = $totatSaleAmt  - $corporate_discount;
                }
                $totatSaleAmt = $totatSaleAmt + $data['gst'];
                $updateCpr = CustomerPurchaseReturn::find($cpr->id);
                $updateCpr->price =  $totalmarketPrice;
                $updateCpr->payment_discount_per = $data['payment_discount'];
                $updateCpr->payment_discount = $getPaymentDis;
                $updateCpr->corporate_discount_per = $corporate_discount_per;
                $updateCpr->corporate_discount = $corporate_discount;
                $updateCpr->payment_term_type = $data['payment_term_type'];
                $updateCpr->payment_term = $data['payment_term'];
                $updateCpr->gst_per = $data['gst_per'];
                $updateCpr->gst = $data['gst'];
                $updateCpr->grand_total = $totatSaleAmt;
                $updateCpr->save();
                //DB::commit();
                $message = "Record has been added successfully";
                return response()->json(apiSuccessResponse($message),200);
            }else{
                $message = "Unable to fetch. PLease try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }
    }

    public function deleteCustomerPurchaseReturn(Request $request)
    {
        if ($request->isMethod('post')) {
            $resp = $this->resp;
            if ($resp['status'] && isset($resp['dealer'])) {
                $dealerId = Dealer::getParentDealer($resp['dealer']);
                $data = $request->all();

                $rules = [
                    "cpr_id" => "required|exists:customer_purchase_returns,id",
                ];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator), 422);
                }

                $cpr = CustomerPurchaseReturn::where('id', $data['cpr_id'])
                    ->where('dealer_id', $dealerId)
                    ->first();

                if (!$cpr) {
                    return response()->json(apiErrorResponse("Purchase return not found."), 404);
                }

                //DB::beginTransaction();
                try {
                    $items = CustomerPurchaseReturnItem::where('customer_purchase_return_id', $cpr->id)->get();

                    foreach ($items as $item) {
                        // Reduce stock from dealer
                        $dealerProd = DealerProduct::where([
                            'dealer_id'  => $dealerId,
                            'product_id' => $item->product_id
                        ])->first();

                        if ($dealerProd) {
                            // Ensure stock never goes negative
                            $dealerProd->stock_in_hand = max(0, $dealerProd->stock_in_hand - $item->qty);
                            $dealerProd->save();
                        }

                        // Delete CPR item
                        $item->delete();
                    }

                    // Delete main CPR
                    $cpr->delete();

                    //DB::commit();
                    return response()->json(apiSuccessResponse("Purchase return deleted successfully"), 200);
                } catch (\Exception $e) {
                    //DB::rollBack();
                    return response()->json(apiErrorResponse("Something went wrong: " . $e->getMessage()), 500);
                }
            } else {
                return response()->json(apiErrorResponse("Unable to fetch dealer."), 422);
            }
        }
    }


    public function dealerPurchaseReturn(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                $rules = [
                    "return_date"=> "required|date_format:Y-m-d",
                    "through_email_dated"=> "date_format:Y-m-d",
                    'items'          =>   'bail|required|array|min:1',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.qty' => 'required',
                    'items.*.market_price' => 'required',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                DB::beginTransaction();
                $dpr = new DealerPurchaseReturn;
                $dpr->dealer_id = $resp['dealer']['id'];
                $dpr->approval_taken_from = $data['approval_taken_from'];
                $dpr->transport_name = $data['transport_name']; 
                $dpr->remarks = $data['remarks']; 
                $dpr->lr_no = $data['lr_no']; 
                $dpr->return_date = $data['return_date']; 
                if(isset($data['through_email_dated'])){
                    $dpr->through_email_dated = $data['through_email_dated'];
                }
                $dpr->save();
                $totalmarketPrice = 0;
                foreach($data['items'] as $item){
                    $dprItem = new DealerPurchaseReturnItem;
                    $dprItem->dealer_purchase_return_id = $dpr->id;
                    $dprItem->product_id = $item['product_id'];
                    $dprItem->qty = $item['qty'];
                    $dprItem->market_price = $item['market_price'];
                    $dprItem->subtotal = $item['market_price'] * $item['qty'];
                    $totalmarketPrice += $item['market_price'] * $item['qty'];
                    $dprItem->save();
                    /*$dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$resp['dealer']['id'],'product_id'=>$item['product_id']])->first();
                    if($dealerProd){
                        $updatedealerProd = DealerProduct::find($dealerProd->id);
                        $updatedealerProd->stock_in_hand = $dealerProd->stock_in_hand - $item['qty'];
                    }else{
                        $updatedealerProd = new DealerProduct;
                        $updatedealerProd->stock_in_hand = $item['qty'];
                    }
                    $updatedealerProd->save();*/
                }
                if(isset($data['corporate_discount'])){
                    $corporate_discount_per =$data['corporate_discount_per'];
                    $corporate_discount = $data['corporate_discount'];
                }else{
                    $corporate_discount_per =0;
                    $corporate_discount = 0;
                }
                $totatSaleAmt = $totalmarketPrice;
                $getPaymentDis = (($totatSaleAmt * $data['payment_discount'])/100);
                if(isset($data['payment_term_type']) && $data['payment_term_type'] =="On Bill"){
                    $totatSaleAmt = $totatSaleAmt - $getPaymentDis - $corporate_discount;
                }else{
                    $totatSaleAmt = $totatSaleAmt  - $corporate_discount;
                }
                $calGST =  ($totatSaleAmt *$data['gst']) /100;
                $totatSaleAmt = $totatSaleAmt + $calGST;
                $updateDpr = DealerPurchaseReturn::find($dpr->id);
                $updateDpr->price =  $totalmarketPrice;
                $updateDpr->payment_discount_per = $data['payment_discount'];
                $updateDpr->payment_discount = $getPaymentDis;
                $updateDpr->corporate_discount_per = $corporate_discount_per;
                $updateDpr->corporate_discount = $corporate_discount;
                $updateDpr->gst_per = $data['gst_per'];
                $updateDpr->gst = $calGST;
                $updateDpr->grand_total = $totatSaleAmt;
                $updateDpr->save();
                DB::commit();
                $message = "Record has been added successfully";
                return response()->json(apiSuccessResponse($message),200);
            }else{
                $message = "Unable to fetch. PLease try again after sometime";
                return response()->json(apiErrorResponse($message),422);
            }
        }
    }

    public function return_history(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
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

    public function purchase_data(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                $rules = [
                    "start_date"=> "required|date_format:Y-m-d",
                    "end_date"=> "required|date_format:Y-m-d",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }
                $saleinvoices = SaleInvoice::getDealerSaleInvoices($data,$resp);
                $purchaseReturns = DealerPurchaseReturn::entries($data,$resp);
                $message = 'Fetched successfully';
                $result['saleinvoices'] = $saleinvoices;
                $result['purchase_returns'] = $purchaseReturns;
                return response()->json(apiSuccessResponse($message,$result),200);
                
            }
        }
    }

    public function saveFeedback(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                $rules = [
                    "customer_id"=> "required",
                    "feedback_date"=> "required",
                    "type"=> "required|in:query,complaint,feedback,suggestion,need sample/trial,feedback/suggestion",
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
                $savefeed->dealer_id = $resp['dealer']['id'];
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
                $savefeed->submit_by = 'dealer';  
                $savefeed->save();
                $message = 'Request has been submitted successfully. We will get back to you soon';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function marketProductsInfo(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
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
                $marketProInfo->dealer_id = $resp['dealer']['id'];
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
            if($resp['status'] && isset($resp['dealer'])){
                $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
                $market_products_info = MarketProductInfo::with(['customer','product_category','dealer'])->wherein('dealer_id',$dealerIds)->orderby('id','DESC')->get();
                $message = 'Market Products info request has been fetched successfully';
                $result['market_product_infos'] = $market_products_info;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function deleteMarketProductsInfo(Request $request,$id){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                MarketProductInfo::where('id',$id)->delete();
                $message = 'Record has been deleted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function dealerPurchaseReturnHistory(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                $rules = [
                    "start_date" => "bail|required|date_format:Y-m-d",
                    "end_date" => "bail|required|date_format:Y-m-d"
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $fetchHistory = DealerPurchaseReturn::with('items')->where(['dealer_id'=>$resp['dealer']['id']])->whereDate('return_date','>=',$data['start_date'])->whereDate('return_date','<=',$data['end_date'])->get();
                $result['history'] = $fetchHistory;
                $message = 'Fetched successfully';
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }
    }

    public function stockAdjustment(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $data = $request->all();
                $rules = [
                    "product_id"=>"required|exists:products,id",
                    "qty"=>"required|numeric",
                    "amount"=>"required",
                    "adjustment_date"=>"required|date_format:Y-m-d",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                DB::beginTransaction();
                $checkDealerPro = DealerProduct::where(['dealer_id'=>$resp['dealer']['id'],'product_id'=>$data['product_id']])->first();
                if(is_object($checkDealerPro)){
                    $checkDealerPro = DealerProduct::find($checkDealerPro->id);
                }else{
                    $checkDealerPro = new DealerProduct;
                }
                $checkDealerPro->stock_in_hand = $checkDealerPro->stock_in_hand - $data['qty'];
                $checkDealerPro->save();
                $stock_adjust = new StockAdjustment;
                $stock_adjust->product_id = $data['product_id'];
                $stock_adjust->dealer_id = $resp['dealer']['id'];
                $stock_adjust->amount = $data['amount'];
                $stock_adjust->qty = $data['qty'];
                $stock_adjust->remarks = $data['remarks'];
                $stock_adjust->reason = $data['reason'];
                $stock_adjust->adjustment_date = $data['adjustment_date'];
                $stock_adjust->other = $data['other'];
                $stock_adjust->save();
                DB::commit();
                $message = 'Stock has been adjusted successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        }
    }

    public function stockAdjustmentLogs(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $data = $request->all();
                $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
                $logs = StockAdjustment::with(['product','dealer'])->whereIn('dealer_id',$dealerIds);
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

    public function qcfs(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $data = $request->all();
                $rules = [
                    "type"=> "required|in:query,complaint,feedback,suggestion,need sample/trial,feedback/suggestion,all",
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()){
                    return response()->json(validationResponse($validator),422); 
                }
                $dealerIds = Dealer::getParentChildDealers($resp['dealer']);
                $feedbacks = Feedback::with(['customer','customer_employee','product','replies','dealer'])->wherein('dealer_id',$dealerIds);
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

    public function linkedDealers(){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['dealer'])){
            if(empty($resp['dealer']['parent_id'])){
                $linked_dealers = explode(',',$resp['dealer']['linked_dealers']);
            }else{
                $parentDealerId = Dealer::getParentDealer($resp['dealer']);
                $dealerInfo = Dealer::find($parentDealerId);
                $linked_dealers = explode(',',$dealerInfo->linked_dealers);
            }
            $dealers = Dealer::whereIn('id',$linked_dealers)->get()->toArray();
            $message = "Fetched successfully";
            $result['dealers'] = $dealers;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function transferStock(Request $request)
    {
        $data = $request->all();
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['dealer'])) {
            $parentDealerId = Dealer::getParentDealer($resp['dealer']);

            if (isset($data['products']) && is_array($data['products']) && !empty($data['products'])) {
                foreach ($data['products'] as $productData) {
                    if (!isset($productData['product_id'], $productData['transfer_stock'])) {
                        continue; // skip if required keys are missing
                    }

                    $getDealerProductStock = DealerProduct::where([
                        'dealer_id'  => $parentDealerId,
                        'product_id' => $productData['product_id']
                    ])->first();

                    if (is_object($getDealerProductStock)) {
                        // $to_dealer_pro_details will fetch/create product stock for to_dealer
                        $to_dealer_pro_details = $this->toDealerProductDetails([
                            'to_dealer'   => $data['to_dealer'],
                            'product_id'  => $productData['product_id']
                        ]);

                        $stockLog = new InterDealerStockLog;
                        $stockLog->from_dealer_id   = $parentDealerId;
                        $stockLog->to_dealer_id     = $data['to_dealer'];
                        $stockLog->product_id       = $productData['product_id'];
                        $stockLog->transfer_stock   = $productData['transfer_stock'];
                        $stockLog->from_dealer_stock= $getDealerProductStock->stock_in_hand;
                        $stockLog->to_dealer_stock  = $to_dealer_pro_details->stock_in_hand;
                        $stockLog->transfer_date    = $data['transfer_date'];
                        $stockLog->invoice_number   = $data['invoice_number'] ?? null;
                        $stockLog->remarks          = $data['remarks'] ?? null;
                        $stockLog->save();

                        // Decrement stock from parent dealer
                        DealerProduct::where([
                            'dealer_id'  => $parentDealerId,
                            'product_id' => $productData['product_id']
                        ])->decrement('stock_in_hand', $productData['transfer_stock']);

                        // Increment stock to child dealer
                        DealerProduct::where([
                            'dealer_id'  => $data['to_dealer'],
                            'product_id' => $productData['product_id']
                        ])->increment('stock_in_hand', $productData['transfer_stock']);
                    }
                }

                $message = "Stock has been transferred successfully";
                return response()->json(apiSuccessResponse($message), 200);
            } else {
                $message = "Please update the app to use this feature";
                return response()->json(apiErrorResponse($message), 422);
            }
        }
    }

    public function toDealerProductDetails($data){
        $getStock = DealerProduct::where(['dealer_id'=>$data['to_dealer'],'product_id'=>$data['product_id']])->first();
        if(is_object($getStock)){
            return $getStock;
        }else{
            $dealer_pro = new DealerProduct;
            $dealer_pro->dealer_id = $data['to_dealer'];
            $dealer_pro->product_id = $data['product_id'];
            $dealer_pro->stock_in_hand = 0;
            $dealer_pro->in_transit = 0;
            $dealer_pro->pending_orders = 0;
            $dealer_pro->pending_customer_orders = 0;
            $dealer_pro->save();
            return $dealer_pro;
        }
    }

    public function transferStockHistory(Request $request){
        $data = $request->all();
        $resp = $this->resp;
        if($resp['status'] && isset($resp['dealer'])){
            $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
            $inHistory = InterDealerStockLog::with(['product','from_dealer'])->whereIn('to_dealer_id',$dealerIds)->orderby('id','DESC')->get();
            $outHistory = InterDealerStockLog::with(['product','to_dealer'])->whereIn('from_dealer_id',$dealerIds)->orderby('id','DESC')->get();
            $message = "Fetched successfully";
            $result['in_history'] = $inHistory;
            $result['out_history'] = $outHistory;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function discounts(){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['dealer'])){
            $qty_discounts = QtyDiscount::with('product')->select('id','product_id','range_from','range_to','discount')->get();
            $parentDealerId = Dealer::getParentDealer($resp['dealer']);
            $dealer_special_discounts = DealerSpecialDiscount::with('product')->where('dealer_id',$parentDealerId)->get();
            $message = "Fetched successfully";
            $result['qty_discounts'] = $qty_discounts;
            $result['special_discounts'] = $dealer_special_discounts;
            $result['cash_discounts'] = cashDiscounts();
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function createSampleRequest(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
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
                    $data['dealer'] = $resp['dealer']['id'];
                    $data['action'] = 'dealer';
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
        if($resp['status'] && isset($resp['dealer'])){
            $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
            $samplings = Sampling::with(['sampling_items','sampling_invoice_items','dealer'])->wherein('dealer_id',$dealerIds)->get();
            $message = "Fetched successfully";
            $result['samplings'] = $samplings;
            return response()->json(apiSuccessResponse($message,$result),200);
        }else{
            $message = "Invalid dealer";
            return response()->json(apiErrorResponse($message),422); 
        }
    }

    public function materialApproval(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                $rules = [
                    'approval_date' =>   'bail|required|date_format:Y-m-d',
                    'customer_id'    => 'bail|required',
                    'product_id'    => 'bail|required',
                    'qty'    => 'bail|required',
                    'value'    => 'bail|required',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }else{
                    DB::beginTransaction();
                    $material_approval = new MaterialApproval;
                    $material_approval->approval_date = $data['approval_date'];
                    $material_approval->customer_id = $data['customer_id'];
                    $material_approval->product_id = $data['product_id'];
                    $material_approval->qty = $data['qty'];
                    $material_approval->value = $data['value'];
                    $material_approval->creation_type= "dealer";
                    $material_approval->created_by = $resp['dealer']['id'];
                    $material_approval->status = "Pending Feedback";
                    $material_approval->save();
                    $dealer_product = DealerProduct::where(['product_id'=>$data['product_id'],'dealer_id'=>$resp['dealer']['id']])->first();
                    if(is_object($dealer_product)){
                        $update_dealer_pro = DealerProduct::find($dealer_product->id);
                        $update_dealer_pro->material_approval = $dealer_product->material_approval + $data['qty'];
                        $update_dealer_pro->save();
                    }else{
                        $update_dealer_pro = new DealerProduct;
                        $update_dealer_pro->product_id = $data['product_id'];
                        $update_dealer_pro->dealer_id = $resp['dealer']['id'];
                        $update_dealer_pro->material_approval = $data['qty'];
                        $update_dealer_pro->save();
                    }   
                    DB::commit();
                    $message ="Record has been added successfully";
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

    public function materialApprovalList(Request $request){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['dealer'])) {
            $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
            $list = MaterialApproval::with(['product','customer','dealer'])->whereIn('created_by',$dealerIds)->orderby('id','DESC')->get();
            $message = "Fetched successfully";
            $result['material_approval'] = $list;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function materialApprovalFeedback(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                $data = $request->all();
                $rules = [
                    'material_approval_id' => 'bail|required',
                    'feedback_date' =>   'bail|required|date_format:Y-m-d',
                    'feedback'    => 'bail|required',
                    'feedback_action'    => 'bail|required',
                    'remarks'    => 'bail|required',
                ];
                $customMessages = [];
                $validator = Validator::make($data,$rules,$customMessages);
                if ($validator->fails()) {
                    return response()->json(validationResponse($validator),422); 
                }else{
                    $material_approval = MaterialApproval::find($data['material_approval_id']);
                    $material_approval->feedback_date = $data['feedback_date'];
                    $material_approval->feedback = $data['feedback'];
                    $material_approval->status = $data['feedback_action'];
                    $material_approval->feedback_action = $data['feedback_action'];
                    $material_approval->remarks = $data['remarks'];
                    $material_approval->save();
                    $dealer_product = DealerProduct::where(['product_id'=>$material_approval->product_id,'dealer_id'=>$material_approval->created_by])->first();
                    $update_dealer_pro = DealerProduct::find($dealer_product->id);
                    $update_dealer_pro->material_approval = $dealer_product->material_approval - $material_approval->qty;
                    $update_dealer_pro->save();
                    $message = "feedback has been submitted successfully";
                    return response()->json(apiSuccessResponse($message),200);
                }
            }
        }
    }

    public function linkedProducts(Request $request){
        $resp = $this->resp;
        if($resp['status'] && isset($resp['dealer'])){
            $parentDealerId = Dealer::getParentDealer($resp['dealer']);
            $productids = DealerLinkedProduct::where('dealer_id',$parentDealerId)->pluck('product_id')->toArray();
            $products = Product::with(['productpacking','pricings','product_stages'])->whereIn('id',$productids)->where('status',1)->get()->toArray();
            $message = "Products Fetched successfully";
            $result['products'] = $products;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
    }

    public function marketSamples(Request $request){
       if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $samples = MarketSample::getMarketSamples('dealer',$resp['dealer']['id']);
                $message = 'Market sample has been fetched successfully';
                $result['samples'] = $samples;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        } 
    }

    public function createMarketSample(Request $request){
       if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $request->request->add(['dealer_id'=> $resp['dealer']['id']]);
                MarketSample::createMarketSample($request);
                $message = 'Market sample has been added successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function editMarketSample(Request $request){
       if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                MarketSample::editMarketSample($request);
                $message = 'Market sample has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function complaintSamples(Request $request){
       if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $samples = ComplaintSample::getComplaintSamples('dealer',$resp['dealer']['id']);
                $message = 'Complaint samples has been fetched successfully';
                $result['samples'] = $samples;
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        } 
    }

    public function createComplaintSample(Request $request){
       if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $request->request->add(['dealer_id'=> $resp['dealer']['id']]);
                ComplaintSample::createComplaintSample($request);
                $message = 'Complaint samples has been added successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function editComplaintSample(Request $request){
       if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                ComplaintSample::editComplaintSample($request);
                $message = 'Complaint sample has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function updateProductsStock(Request $request){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $data = $request->all();
                $dealerid = $resp['dealer']['id'];
                foreach($data['items'] as $item){
                    $stock_in_hand = $item['stock_in_hand'];
                    DealerProduct::where([
                        'dealer_id' =>$dealerid,
                        'product_id' => $item['product_id']
                    ])->update(['stock_in_hand'=>$stock_in_hand]);
                    $stock_adjust = new StockAdjustment;
                    $stock_adjust->product_id = $item['product_id'];
                    $stock_adjust->dealer_id = $resp['dealer']['id'];
                    $stock_adjust->amount = 0;
                    $stock_adjust->qty = 0;
                    $stock_adjust->stock_in_hand = $stock_in_hand;
                    $stock_adjust->remarks = "";
                    $stock_adjust->reason = "";
                    $stock_adjust->adjustment_date = date('Y-m-d');
                    $stock_adjust->other = "";
                    $stock_adjust->save();
                }
                $message = 'Stock has been updated successfully';
                return response()->json(apiSuccessResponse($message),200);
            }
        } 
    }

    public function sampleInTransitMaterials(Request $request){
        if($request->isMethod('get')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])) {
                //echo "<pre>"; print_r($resp); die;
                $dealerIds = Dealer::getParentChildDealers($resp['dealer']);
                $lrNos = \App\SamplingSaleInvoice::whereIn('dealer_id',$dealerIds)->where('transport_name','!=','')->orderby('dispatch_date','ASC')->where('is_delivered',0)->select('lr_no')->groupby('lr_no')->get();
                $lrNos = json_decode(json_encode($lrNos),true);
                $saleInvoices = array();
                foreach($lrNos as $lkey=> $lrNo){
                    $saleInvoices[$lkey]['lr_no'] = $lrNo['lr_no'];
                    $invoices = \App\SamplingSaleInvoice::with(['productinfo','dealer','sampling'])->whereIn('dealer_id',$dealerIds)->where('transport_name','!=','')->orderby('dispatch_date','ASC')->where('is_delivered',0)->Where('lr_no',$lrNo)->get();
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

    public function updateSampleMaterialDelivery(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
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
                $saleInvoice = SamplingSaleInvoice::with('sampling')->find($saleInvid);
                if($saleInvoice->sampling->sample_type == "free")
                {
                    $execProd = DB::table('free_sampling_stocks')->where(['dealer_id'=>$saleInvoice->dealer_id,'product_id'=>$saleInvoice->product_id])->first();
                    if($execProd){
                        $updateexecProd = FreeSamplingStock::find($execProd->id);
                        $updateexecProd->in_transit = $execProd->in_transit - $saleInvoice->qty;
                        $updateexecProd->stock_in_hand = $execProd->stock_in_hand + $saleInvoice->qty;
                        $updateexecProd->save();
                    }
                }else{
                    //for paid samplings
                    $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$saleInvoice->dealer_id,'product_id'=>$saleInvoice->product_id])->first();
                    if(is_object($dealerProd)) {
                        $updatedealerProd = DealerProduct::find($dealerProd->id);
                        $updatedealerProd->in_transit = $execProd->in_transit - $saleInvoice->qty;
                        $updatedealerProd->stock_in_hand = $execProd->stock_in_hand + $saleInvoice->qty;
                        $updatedealerProd->save();
                    }
                }
            }
            $message = "Material marked delivered successfully";
            return response()->json(apiSuccessResponse($message),200);
        }
    }

    public function feedbackHistory(Request $request){
      if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
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
                $message = "Pdf has been fetched successfully";
                return response()->json(apiSuccessResponse($message,$result),200);
            }
        }  
    }

    public function deleteSaleInvoice(Request $request,$saleInvoiceId){
        if($request->isMethod('post')){
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $parentDealerId = \App\Dealer::getParentDealer($resp['dealer']);
                $resp['dealer']['id'] = $parentDealerId;
                $saleInvoice  =SaleInvoice::with(['invoice_items','purchase_order'])->where('id',$saleInvoiceId)->first();
                if(is_object($saleInvoice)){
                    $invoice = json_decode(json_encode($saleInvoice),true);
                    if($invoice['purchase_order']['action'] == 'dealer_customer'){
                        foreach($invoice['invoice_items'] as $item){
                            $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$resp['dealer']['id'],'product_id'=>$item['product_id']])->first();
                            if($dealerProd){
                                $updatedealerProd = DealerProduct::find($dealerProd->id);
                                $updatedealerProd->pending_customer_orders = $dealerProd->pending_customer_orders + $item['qty'];
                                $updatedealerProd->stock_in_hand = $dealerProd->stock_in_hand + $item['qty'];
                                $updatedealerProd->save();
                            }
                        }
                    }
                    $saleInvoice->delete();
                    $message = "Sale Invoice has been deleted successfully";
                    return response()->json(apiSuccessResponse($message),200);
                }else{
                    $message = "Sale Invoice not found";
                    return response()->json(apiSuccessResponse($message),404);
                }
            }
        }
       
    }

    public function deleteBulkSaleInvoice(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $resp = $this->resp;
            if($resp['status'] && isset($resp['dealer'])){
                $parentDealerId = \App\Dealer::getParentDealer($resp['dealer']);
                $resp['dealer']['id'] = $parentDealerId;

                $saleInvoiceIds = explode(',',$data['sale_invoice_ids']); 
                foreach($saleInvoiceIds as $saleInvoiceId){
                    $saleInvoice  =SaleInvoice::with(['invoice_items','purchase_order'])->where('id',$saleInvoiceId)->first();
                    if(is_object($saleInvoice)){
                        $invoice = json_decode(json_encode($saleInvoice),true);
                        if($invoice['purchase_order']['action'] == 'dealer_customer'){
                            foreach($invoice['invoice_items'] as $item){
                                $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$resp['dealer']['id'],'product_id'=>$item['product_id']])->first();
                                if($dealerProd){
                                    $updatedealerProd = DealerProduct::find($dealerProd->id);
                                    $updatedealerProd->pending_customer_orders = $dealerProd->pending_customer_orders + $item['qty'];
                                    $updatedealerProd->stock_in_hand = $dealerProd->stock_in_hand + $item['qty'];
                                    $updatedealerProd->save();
                                }
                            }
                        }
                        $saleInvoice->delete();
                        
                    }
                }
                $message = "Sale Invoice has been deleted successfully";
                return response()->json(apiSuccessResponse($message),200);
            }
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

    public function updateCustomerLatitudeLongitude(Request $request)
    {
        $data = $request->all();
        $resp = $this->resp; // Assuming you are checking auth or something similar

        if ($resp['status'] && isset($resp['dealer'])) {
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

    public function savePurchaseProjection(Request $request)
    {
        $data = $request->all(); 
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['dealer'])) {
            $rules = [
                'action' => 'required|in:SAVE,SUBMIT',
                'month_year' => 'required|string',
                'products' => 'required|array',
                'products.*.product_id' => 'required|integer',
                'products.*.projected_qty' => 'required|numeric|min:0',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422); 
            }

            $dealerId = Dealer::getParentDealer($resp['dealer']);
            $createdBy = $resp['dealer']['id'];
            $monthYear = $request->month_year;

            DB::beginTransaction();
            try {
                $productIds = collect($request->products)->pluck('product_id')->toArray();

                // 🔥 1. Delete all existing projections in one go
                DealerPurchaseProjection::where('dealer_id', $dealerId)
                    ->where('month_year', $monthYear)
                    ->where('created_by', $createdBy)
                    ->whereIn('product_id', $productIds)
                    ->delete();

                // 🔥 2. Prepare new projections
                $insertData = [];
                $timestamp = now();

                foreach ($request->products as $product) {
                    $insertData[] = [
                        'dealer_id'      => $dealerId,
                        'product_id'     => $product['product_id'],
                        'projected_qty'  => $product['projected_qty'],
                        'action'         => $request->action,
                        'month_year'     => $monthYear,
                        'created_by'     => $createdBy,
                        'created_at'     => $timestamp,
                        'updated_at'     => $timestamp,
                    ];
                }

                // 🔥 3. Bulk insert in one query
                DealerPurchaseProjection::insert($insertData);

                DB::commit();
                return response()->json(apiSuccessResponse("Purchase projections saved successfully"), 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(apiErrorResponse("Failed to save projections"), 422);
            }
        }

        return response()->json(apiErrorResponse("Unauthorized or invalid request"), 401);
    }


    public function getPurchaseProjections(Request $request)
    {
        $data = $request->all(); 
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['dealer'])) {
            $rules = [
                'month_year' => 'required|string',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422); 
            }

            // Support multiple month_years (split by #)
            $monthYearList = explode('#', $request->month_year);
            $monthYearList = array_map('trim', $monthYearList);

            $dealerId = Dealer::getParentDealer($resp['dealer']);
            $createdBy = $resp['dealer']['id'];

            $projections = DealerPurchaseProjection::with([
                    'dealer',
                    'created_by_dealer',
                    'product' => function ($query) {
                        $query->with('pricings');
                    }
                ])
                ->whereIn('month_year', $monthYearList)
                ->where('dealer_id', $dealerId)
                ->get();

            $result['purchase_projections'] = $projections;
            $message = "Purchase projections fetched successfully";

            return response()->json(apiSuccessResponse($message, $result), 200);
        }

        return response()->json(apiErrorResponse("Unauthorized or invalid request"), 401);
    }


    public function getMonthlyProjectionStatus(Request $request)
    {
        $data = $request->all(); 
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['dealer'])) {
            $rules = [
                'month_years' => 'required|string',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422); 
            }

            $createdBy = $resp['dealer']['id'];
            $dealerId = Dealer::getParentDealer($resp['dealer']);
            $monthYears = explode('#', $data['month_years']);

            // Build placeholders for binding
            $placeholders = implode(',', array_fill(0, count($monthYears), '?'));

            $bindings = array_merge([$dealerId, $createdBy], $monthYears);

            $results = DB::select("
                SELECT month_year, action, updated_at FROM (
                    SELECT *, ROW_NUMBER() OVER (PARTITION BY month_year ORDER BY id ASC) AS rn
                    FROM dealer_purchase_projections
                    WHERE dealer_id = ? AND created_by = ? AND month_year IN ($placeholders)
                ) AS sub
                WHERE rn = 1
            ", $bindings);

            $monthYearMap = collect($results)->map(function ($item) {
                return [
                    'month_year' => $item->month_year,
                    'action'     => $item->action,
                    'updated_at' => $item->updated_at,
                ];
            })->toArray();

            $result['details'] = $monthYearMap;
            $message = "Data fetched successfully";
            return response()->json(apiSuccessResponse($message, $result), 200);
        }

        return response()->json(apiErrorResponse("Unauthorized or invalid request"), 401);
    }



    public function updatePurchaseProjectionAction(Request $request)
    {
        $data = $request->all();
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['dealer'])) {
            $rules = [
                'purchase_projection_ids' => 'required|string',
                'action' => 'required|in:SAVE,SUBMIT',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422);
            }

            $ids = explode(',', $data['purchase_projection_ids']);
            $ids = array_filter(array_map('trim', $ids)); // remove whitespace and empty values

            try {
                DealerPurchaseProjection::whereIn('id', $ids)->update([
                    'action' => $data['action'],
                ]);

                $message = "Purchase projection actions updated successfully.";
                return response()->json(apiSuccessResponse($message), 200);

            } catch (\Exception $e) {
                $message = "Failed to update purchase projection actions.";
                return response()->json(apiErrorResponse($message), 422);
            }
        }

        return response()->json(apiErrorResponse("Unauthorized or invalid request"), 401);
    }

    public function getMonthlySalesProjectionStatus(Request $request){
        $data = $request->all(); 
        $resp = $this->resp;
        if($resp['status'] && isset($resp['dealer'])){
            $rules= [
                'month_years' => 'required|string',
            ];
            $customMessages = [];
            $validator = Validator::make($data,$rules,$customMessages);
            if ($validator->fails()) {
                return response()->json(validationResponse($validator),422); 
            }
            $dealerId = Dealer::getParentDealer($resp['dealer']);

            $customerIds = \App\Customer::where('business_model','Dealer')->where('dealer_id',$dealerId)->pluck('id')->toArray();
            $monthYears = explode('#',$data['month_years']);

            $results = DB::select("
                SELECT month_year, action, updated_at FROM (
                    SELECT *, ROW_NUMBER() OVER (PARTITION BY month_year ORDER BY id ASC) AS rn
                    FROM sales_projections
                    WHERE customer_id IN (" . implode(',', array_fill(0, count($customerIds), '?')) . ")
                      AND month_year IN (" . implode(',', array_fill(0, count($monthYears), '?')) . ")
                ) AS sub
                WHERE rn = 1
            ", array_merge($customerIds, $monthYears));


            // Convert to array of associative arrays
            $monthYearMap = collect($results)->map(function ($item) {
                return [
                    'month_year' => $item->month_year,
                    'action'     => $item->action,
                    'updated_at' => $item->updated_at,
                ];
            })->toArray();



            $result['details'] = $monthYearMap;
            $message = "Data fetched successfully";
            return response()->json(apiSuccessResponse($message,$result), 200);
        }
    }

    public function getSalesProjections(Request $request)
    {
        $data = $request->all(); 
        $resp = $this->resp;

        if ($resp['status'] && isset($resp['dealer'])) {
            $rules = [
                'month_year' => 'required|string',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(validationResponse($validator), 422); 
            }

            $dealerId = Dealer::getParentDealer($resp['dealer']);

            $customerIds = \App\Customer::where('business_model','Dealer')->where('dealer_id',$dealerId)->pluck('id')->toArray();

            // Explode by #
            $monthYearList = explode('#', $request->month_year);

            // Clean whitespace
            $monthYearList = array_map('trim', $monthYearList);

            $salesProjections = \App\SalesProjection::with([
                'customer',
                'product' => function ($query) {
                    $query->with(['pricings','productpacking']);
                }
            ])
            ->whereIn('month_year', $monthYearList)
            ->wherein('customer_id', $customerIds)
            ->get();

            $result['sales_projections'] = $salesProjections;
            $message = "Sales projections fetched successfully";

            return response()->json(apiSuccessResponse($message, $result), 200);
        }
    }

}
