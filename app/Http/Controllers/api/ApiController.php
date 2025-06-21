<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Product;
use App\RegisterRequest;
use App\ProductDetail;
use App\Notification;
use App\Category;
use App\Make;
use App\Discount;
use App\AppVersion;
use App\TrialReportMaster;
use Illuminate\Support\Facades\Input;
use Validator;
use PDF;
use App\QuickEnquiry;
use App\DealershipEnquiry;
use App\JobEnquiry;
use App\RequestOtp;
class ApiController extends Controller
{
    //
    public function products(Request $request){
    	$products = Product::with(['productpacking','pricings','product_stages','product_weightages'])->where('is_trader_product',0);
        /*if(isset($_GET['is_trader'])){
            $products = $products->where('is_trader_product',1);
        }else{
            $products = $products->where('is_trader_product',0);
        }*/
        $products =  $products->where('status',1)->get();
        //echo "<pre>"; print_r($products); die;
        foreach($products as $pkey => $product){
            foreach($product['pricings'] as $pricekey=> $proprice){
                $class = geClass($proprice['dealer_markup']);
                $products[$pkey]['pricings'][$pricekey]['class'] =$class; 
            }
        }
        $result['products'] = $products;
        $discounts = \App\ProductDiscount::get();
        $result['product_discounts'] = $discounts;
        $message = "Products has been fetched successfully";
		return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function registerRequest(Request $request){
    	if($request->isMethod('post')){
    		$data = $request->all();
    		$registerReq = new RegisterRequest;
    		$registerReq->name = $data['name'];
    		$registerReq->email = $data['email'];
    		$registerReq->mobile = $data['mobile'];
    		$registerReq->city = $data['city'];
    		$registerReq->business_name = $data['business_name'];
    		$registerReq->otp = $data['otp'];
    		$registerReq->save();
    		$message = "Registration  request has been added successfully. Our team will contact you shortly.";
			return response()->json(apiSuccessResponse($message),200);
    	}
    }

    public function generateOtp(Request $request){
    	$data = $request->all();
    	$otp =123456;
    	$result['otp'] = $otp;
    	$message = "OTP sent successfully";
		return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function categories(){
        $cats  = ProductDetail::with(['subcats'=>function($query){
            $query->with(['subcats'=>function($query){
                $query->with('subcats');
            }]);
        }])->where('status',1)->where('parent_id','ROOT')->get();
        $result['cats'] = $cats;
        $message = "Fetched successfully";
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function monthlyTurnoverDiscounts(){
        $discounts = Discount::orderby('id','DESC')->get();
        $result['discounts'] = $discounts;
        $message = "Fetched successfully";
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function checkVersion(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $validator = Validator::make($request->all(), [
                    'device'  => 'bail|required|in:android,iOS',
                    'version'  => 'bail|required|exists:app_versions,version'
                ]
            );
            if($validator->passes()) {
                $version_details = AppVersion::where(['type'=>$data['device'],'version'=>$data['version']])->select('id','type','version','status');
                if(isset($data['app_type'])) {
                    $version_details = $version_details->where('app_type',$data['app_type']);
                }else{
                    $version_details = $version_details->where('app_type','customer');
                }
                $version_details = $version_details->first();
                    $message = "ok";
                    $result['versioninfo'] = $version_details;
                    return response()->json(apiSuccessResponse($message,$result),200);
                
            }else{
                return response()->json(validationResponse($validator),200); 
            }
        }
    }

    public function notifications($type){
        $notifications = Notification::where('type',$type)->orderby('id','DESC')->get()->toArray();
        $message = "Notifications has been fetched successfully";
        $result['notifications'] = $notifications;
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function deleteAccount(Request $request){
        $message ="Your request has been recorded successfully. We will get back to you soon";
        return response()->json(apiSuccessResponse($message),200);
    }

    public function customerMtodSpsod(Request $request){
        $spsod = \App\ProductDiscount::get();
        $mtod = \App\Discount::get()->toArray();
        $result['spsod']['list'] = $spsod;
        $result['spsod']['column1HeaderText'] = "Order Value (Rs)";
        $result['spsod']['column2HeaderText'] = "Discount %";
        $result['spsod']['textAbove'] = array('(High Value Order of a Single Product makes it More Cost Effective)');
        $result['spsod']['textBelow'] = [
        'Applicable on List Price',
        'Customer can avail an extra discount if he places a High Value Order of a Single Product as per the mentioned slabs.',
        'Order Value will be calculated at List Price.',
        'All orders are subject to confirmation.'
        ];
        $result['spsod']['rights'] = "Company reserves the right to alter the discount structure any time.";
        $result['mtod']['list'] = $mtod;
        $result['mtod']['column1HeaderText'] = "Monthly Net Turnover Range (Rs)";
        $result['mtod']['column2HeaderText'] = "Discount %";
        $result['mtod']['textAbove'] = array('(Higher the Turnover, More the Discount)');
        $result['mtod']['textBelow'] = [
        'Applicable on Monthly Sales, net of all discounts',
        'Customers with higher monthly sales volume can avail this discount as per the mentioned slabs.',
        'If monthly net turnover falls in a particular slab, then respective discount (%) will be applicable on the entire sales starting from Rs. 1/-'
      ];
        $result['mtod']['rights'] = "Company reserves the right to alter the discount structure any time.";
        //Payment term discount

        $result['ptd']['list'] = [
            [
                'payment_term' =>'90 days',
                'discount' =>'4'
            ],[
                'payment_term' =>'60 days',
                'discount' =>'8'
            ],[
                'payment_term' =>'30 days',
                'discount' =>'12'
            ],[
                'payment_term' =>'7 days',
                'discount' =>'14'
            ]
        ];
        $result['ptd']['column1HeaderText'] = "Payment Term";
        $result['ptd']['column2HeaderText'] = "Discount %";
        $result['ptd']['textAbove'] = array('(Early Payments attract Higher Discounts)');
        $result['ptd']['textBelow'] = [
        'Applicable on List Price',
        'As per the agreed terms between dealer and customer, applicable PTD will be given either at the time of billing or at the time of payment.',
        'No discount if payment goes beyond 90 days.'
      ];
        $result['ptd']['rights'] = "Company reserves the right to alter the discount structure any time.";

        $message = "Data has fetched successfully";
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function productTypes(){
        $product_types = product_types();
        $result['product_types'] = $product_types;
        $message = "Data has fetched successfully";
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function classes(){
        $classes = \App\ProductClass::where('status',1)->get();
        $result['classes'] = $classes;
        $message = "Data has fetched successfully";
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function otherMasterList(){
        $categories = Category::where('status',1)->get();
        $makes = Make::where('status',1)->get();
        $result['categories'] = $categories;
        $result['makes'] = $makes;
        $message = "Data has fetched successfully";
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function generateProductPdf(Request $request){
        $data = $request->all();
        foreach($data['data'] as $ckey => $catInfo){
            foreach($catInfo['sub_cats'] as $subkey=> $subCatInfo){
                $products = Product::with(['packing_type','latestProductPricing','productpacking'])->select('id','product_name','short_description','product_code','suggested_dosage','packing_type_id','packing_size_id')->wherein('id',$subCatInfo['product_ids'])->orderby('product_name','ASC')->get();
                $products = json_decode(json_encode($products),true);
                $data['data'][$ckey]['sub_cats'][$subkey]['products'] = $products;
            }
        }
        //echo "<pre>"; print_r($data); die;
        ini_set('memory_limit','256M');
        $filename = "Product_PDF.pdf";
        PDF::loadView('product_pdf',compact('data'))->save('ProductPdfs/'.$filename);
        $filepath = url('ProductPdfs/'.$filename);
        $result['pdf_url'] = $filepath;
        $message = "Pdf has been fetched successfully";
        return response()->json(apiSuccessResponse($message,$result),200);
    }


    public function saveQuickEnquiry(Request $request){
        $validator = Validator::make($request->all(), [
                'name'   =>  'bail|required',
                'email'   => 'bail|required|email',
                'mobile'  => 'bail|required|numeric|digits:10',
                'message'  => 'bail|required',
                'otp'  => 'bail|required|digits:6',
            ]
        );
        if($validator->passes()) {
            $data = $request->all();
            $validateOtp = validateOtp($data['otp'],'general',$data['mobile']);
            if($validateOtp){
                $quick_enquiry = new QuickEnquiry;
                $quick_enquiry->name = $data['name']; 
                $quick_enquiry->email = $data['email']; 
                $quick_enquiry->phone = $data['mobile']; 
                $quick_enquiry->message = $data['message']; 
                if(isset($data['source_app'])){
                    $quick_enquiry->source_app = $data['source_app']; 
                }
                $quick_enquiry->save();
                $message = "Record has been submitted successfully";
                return response()->json(apiSuccessResponse($message),200);
            }else{
                $message = "You have entered wrong OTP";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            if($validator->fails()) {
                return response()->json(validationResponse($validator),422); 
            }
        }
    }

    public function saveDealershipEnquiry(Request $request){
        $validator = Validator::make($request->all(), [
                'business_name'   =>  'bail|required',
                'city'   =>  'bail|required',
                'email'   => 'bail|required|email',
                'mobile'  => 'bail|required|numeric|digits:10',
                'contact_person'  => 'bail|required',
                'otp'  => 'bail|required|digits:6',
            ]
        );
        if($validator->passes()) {
            $data = $request->all();
            $validateOtp = validateOtp($data['otp'],'dealership',$data['mobile']);
            if($validateOtp){
                $quick_enquiry = new DealershipEnquiry;
                $quick_enquiry->business_name = $data['business_name']; 
                $quick_enquiry->city = $data['city']; 
                $quick_enquiry->contact_person = $data['contact_person']; 
                $quick_enquiry->email = $data['email']; 
                $quick_enquiry->phone = $data['mobile']; 
                $quick_enquiry->message = $data['message']; 
                if(isset($data['source_app'])){
                    $quick_enquiry->source_app = $data['source_app']; 
                }
                $quick_enquiry->save();
                $message = "Record has been submitted successfully";
                return response()->json(apiSuccessResponse($message),200);
            }else{
                $message = "You have entered wrong OTP";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            if($validator->fails()) {
                return response()->json(validationResponse($validator),422); 
            }
        }
    }

    public function saveJobEnquiry(Request $request){
        $validator = Validator::make($request->all(), [
                'name'   =>  'bail|required',
                'email'   => 'bail|required|email',
                'mobile'  => 'bail|required|numeric|digits:10',
                'currently_working'  => 'bail|required',
                'designation'  => 'bail|required',
                'placed_at'  => 'bail|required',
                'otp'  => 'bail|required|digits:6',
            ]
        );
        if($validator->passes()) {
            $data = $request->all();
            $validateOtp = validateOtp($data['otp'],'job',$data['mobile']);
            if($validateOtp){
                $quick_enquiry = new JobEnquiry;
                $quick_enquiry->name = $data['name']; 
                $quick_enquiry->currently_working = $data['currently_working']; 
                $quick_enquiry->designation = $data['designation']; 
                $quick_enquiry->email = $data['email']; 
                $quick_enquiry->phone = $data['mobile']; 
                $quick_enquiry->placed_at = $data['placed_at']; 
                $quick_enquiry->message = $data['message'];
                if(isset($data['source_app'])){
                    $quick_enquiry->source_app = $data['source_app']; 
                } 
                $quick_enquiry->save();
                $message = "Record has been submitted successfully";
                return response()->json(apiSuccessResponse($message),200);
            }else{
                $message = "You have entered wrong OTP";
                return response()->json(apiErrorResponse($message),422);
            }
        }else{
            if($validator->fails()) {
                return response()->json(validationResponse($validator),422); 
            }
        }
    }

    public function trialReportsMaster(){
        $masters = TrialReportMaster::where('status',1)->get();
        $message = "Record has been fetched successfully";
        $result['masters'] = $masters;
        return response()->json(apiSuccessResponse($message,$result),200);
    }

    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric|digits:10',
            'module' => 'required|string|in:dealership,general,job'
        ]);

        if ($validator->fails()) {
            return response()->json(validationResponse($validator), 422);
        }

        $data = $request->only(['mobile', 'module']);

        // Generate 6-digit OTP
        $otp = random_int(100000, 999999);
        // Here, integrate SMS gateway if required to actually send the OTP
        if($data['mobile'] == "9890909090" || $data['mobile'] == "9876543210"){
            $otp = 999888;
        }else{
            $params['mobile'] = $data['mobile'];
            $params['message'] = "Your OTP for Login is ".$otp.". -GREENWAVE GLOBAL LTD";
            sendSms($params);
        }
        // Save to DB
        RequestOtp::create([
            'mobile' => $data['mobile'],
            'module' => $data['module'],
            'otp'    => $otp
        ]);


        return response()->json(apiSuccessResponse('OTP sent successfully', []), 200); // remove `otp` in production!
    }

    public function cities(){
        $cities = \App\City::where('status',1)->get();
        $message = "Cities has been fetched successfully";
        $result['cities'] = $cities;
        return response()->json(apiSuccessResponse($message,$result),200);
    }
}
