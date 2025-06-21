<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use Illuminate\Support\Facades\Route;
use App\Dealer;
use App\User;
use App\VoluntaryDispatch;
use App\Product;
use DB;
use Cookie;
use Session;
use Crypt;
use Illuminate\Support\Facades\Mail;
use Auth;
use Image;
use Validator;
class VoluntaryDispatchController extends Controller
{

	public function list(Request $Request){
        Session::put('active','VoluntaryDispatches'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = VoluntaryDispatch::join('products','products.id','=','voluntary_dispatches.product_id')->leftjoin('dealers','dealers.id','=','voluntary_dispatches.dealer_id')->leftjoin('users','users.id','=','voluntary_dispatches.user_id')->select('voluntary_dispatches.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','products.product_name','products.product_code','users.name as executive_name');
            if(!empty($data['dealer_info'])){
                $keyword = $data['dealer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('dealers.business_name', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.owner_mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.email', 'like', '%' . $keyword . '%');
                });
            }
            if(!empty($data['user_info'])){
                $keyword = $data['user_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('users.name', 'like', '%' . $keyword . '%');
                });
            }
            if(!empty($data['dispatch_to'])){
                 $querys = $querys->where('voluntary_dispatches.dispatch_to',$data['dispatch_to']);
            }	
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like', '%' .$data['product_name']. '%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('voluntary_dispatches.created_at','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $dispatchInfo){ 
                $userInfo = "";
                if(!empty($dispatchInfo['business_name'])){
                    $userInfo = ucwords($dispatchInfo['business_name']);
                }else{
                    $userInfo = ucwords($dispatchInfo['executive_name']);
                }
                $actionValues = "";
                $type = "Executive";
                if(!empty($dispatchInfo['business_name'])){
                    $type = "Dealer";
                }
                $records["data"][] = array( 
                    '<small>'.
                    date('d M Y',strtotime($dispatchInfo['created_at'])).'<small>',  
                    $type,  
                    $userInfo,
                    $dispatchInfo['product_name'].'<br><small>('.$dispatchInfo['product_code'].')</small>',
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Voluntary Dispatches";
        return View::make('admin.voluntary_dispatches.list')->with(compact('title'));
    }

	public function create(){
		$title = "Create Voluntary Dispatch";
		$executives = User::where('type','employee')->where('status',1)->get();
		$dealers = Dealer::where('status',1)->whereNull('parent_id')->get();
		$products = Product::where('status',1)->get();
		return view('admin.voluntary_dispatches.create')->with(compact('title','executives','dealers','products'));
	}

	public function store(Request $request)
	{
	    $validator = Validator::make($request->all(),[
	        'dispatch_to'         => 'required|in:dealer,executive',
	        'dealer_id'           => 'nullable|required_if:dispatch_to,dealer|exists:dealers,id',
	        'user_id'        => 'nullable|required_if:dispatch_to,executive|exists:users,id',
	        'product_id'          => 'required|exists:products,id',
	        'dispatch_basis'      => 'required|in:free,paid',
	        'invoice_no'          => 'nullable|required_if:dispatch_basis,paid|string|max:50',
	        'challan_no'          => 'nullable|required_if:dispatch_basis,free|string|max:50',
	        'dispatch_date'=> 'required|date',
	        'sent_through'        => 'required|in:transport,courier',
	        'gr_no'               => 'nullable|required_if:sent_through,transport|string|max:50',
	        'pod_no'              => 'nullable|required_if:sent_through,courier|string|max:50',
	        'sent_date'           => 'required|date',
	    ]);
	    if($validator->passes()) {
	    	$validatedData = $data =$request->all();
		    // Create a new dispatch record
		    $dispatch = new VoluntaryDispatch();
		    $dispatch->date_of_entry = date('Y-m-d');
		    $dispatch->dispatch_to = $validatedData['dispatch_to'];
		    $dispatch->dealer_id = $validatedData['dispatch_to'] === 'dealer' ? $validatedData['dealer_id'] : null;
		    $dispatch->user_id = $validatedData['dispatch_to'] === 'executive' ? $validatedData['user_id'] : null;
		    $dispatch->product_id = $validatedData['product_id'];
		    $dispatch->dispatch_basis = $validatedData['dispatch_basis'];
		    $dispatch->invoice_no = $validatedData['dispatch_basis'] === 'paid' ? $validatedData['invoice_no'] : null;
		    $dispatch->challan_no = $validatedData['dispatch_basis'] === 'free' ? $validatedData['challan_no'] : null;
		    $dispatch->dispatch_date = $validatedData['dispatch_date'];
		    $dispatch->sent_through = $validatedData['sent_through'];
		    $dispatch->gr_no = $validatedData['sent_through'] === 'transport' ? $validatedData['gr_no'] : null;
		    $dispatch->pod_no = $validatedData['sent_through'] === 'courier' ? $validatedData['pod_no'] : null;
		    $dispatch->sent_date = $validatedData['sent_date'];
		    $dispatch->save();
		    $url = url('/admin/voluntary-dispatch/create');
		   return response()->json(['status'=>true,'url'=>$url]);
		}else{
		 	return response()->json(['status'=>false,'errors'=>$validator->messages()]);
		 }
	}
	
}