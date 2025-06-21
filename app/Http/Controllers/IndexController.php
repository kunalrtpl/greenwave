<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\QuickEnquiry;
use App\DealershipEnquiry;
use App\JobEnquiry;
class IndexController extends Controller
{
    //
    public function enquiry(){
    	$title = "Enquiry";
    	return view('enquiry')->with(compact('title'));
    }

    public function saveQuickEnquiry(Request $request){
    	$validator = Validator::make($request->all(), [
	            'name'   =>  'bail|required',
	            'email'   => 'bail|required|email',
	            'mobile'  => 'bail|required|numeric|digits:10',
	            'message'  => 'bail|required',
	        ]
	    );
	    if($validator->passes()) {
	    	$data = $request->all();
	    	$quick_enquiry = new QuickEnquiry;
	    	$quick_enquiry->name = $data['name']; 
	    	$quick_enquiry->email = $data['email']; 
	    	$quick_enquiry->phone = $data['mobile']; 
	    	$quick_enquiry->message = $data['message']; 
	    	$quick_enquiry->save();
	    	\Session::flash('flash_message_success','Your informtion has been submitted successfully. We will get back to you soon');
	    	$redirectTo = url('/enquiry');
            return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
	    }else{
            return response()->json(['status'=>false,'errors'=>$validator->messages()]);
        }
    }

    public function saveDealershipEnquiry(Request $request){
    	$validator = Validator::make($request->all(), [
	            'business_name'   =>  'bail|required',
	            'city'   =>  'bail|required',
	            'email'   => 'bail|required|email',
	            'mobile'  => 'bail|required|numeric|digits:10',
	            'contact_person'  => 'bail|required',
	        ]
	    );
	    if($validator->passes()) {
	    	$data = $request->all();
	    	$quick_enquiry = new DealershipEnquiry;
	    	$quick_enquiry->business_name = $data['business_name']; 
	    	$quick_enquiry->city = $data['city']; 
	    	$quick_enquiry->contact_person = $data['contact_person']; 
	    	$quick_enquiry->email = $data['email']; 
	    	$quick_enquiry->phone = $data['mobile']; 
	    	$quick_enquiry->message = $data['message']; 
	    	$quick_enquiry->save();
	    	\Session::flash('flash_message_success','Your informtion has been submitted successfully. We will get back to you soon');
	    	$redirectTo = url('/enquiry');
            return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
	    }else{
            return response()->json(['status'=>false,'errors'=>$validator->messages()]);
        }
    }

    public function saveJobEnquiry(Request $request){
    	$validator = Validator::make($request->all(), [
	            'name'   =>  'bail|required',
	            'email'   => 'bail|required|email',
	            'mobile'  => 'bail|required|numeric|digits:10',
	            'currently_working'  => 'bail|required',
	            'placed_at'  => 'bail|required',
	        ]
	    );
	    if($validator->passes()) {
	    	$data = $request->all();
	    	$quick_enquiry = new JobEnquiry;
	    	$quick_enquiry->name = $data['name']; 
	    	$quick_enquiry->currently_working = $data['currently_working']; 
	    	$quick_enquiry->email = $data['email']; 
	    	$quick_enquiry->phone = $data['mobile']; 
	    	$quick_enquiry->placed_at = $data['placed_at']; 
	    	$quick_enquiry->message = $data['message']; 
	    	$quick_enquiry->save();
	    	\Session::flash('flash_message_success','Your informtion has been submitted successfully. We will get back to you soon');
	    	$redirectTo = url('/enquiry');
            return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
	    }else{
            return response()->json(['status'=>false,'errors'=>$validator->messages()]);
        }
    }
}
