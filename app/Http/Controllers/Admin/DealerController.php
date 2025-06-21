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
use App\Dealer;
use App\MarketProductInfo;
use App\DealerAtod;
use App\PurchaseOrder;
use App\DealerProduct;
use App\QtyDiscount;
use App\DealerIncentive;
use App\DealerContactPerson;
use App\DealerSpecialDiscount;
use App\DealerLinkedProduct;
use App\Product;
use Validator;

class DealerController extends Controller
{
    //
    public function dealers(Request $Request){
        Session::put('active','dealers'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Dealer::whereNULL('parent_id')->withCount(['customers' => function ($query) {
                $query->where('business_model', 'Dealer')->where('status',1);
            }])->withCount(['linked_products']);
            if(!empty($data['business_name'])){
                $querys = $querys->where('business_name','like','%'.$data['business_name'].'%');
            }
            if(!empty($data['owner_name'])){
                $querys = $querys->where('owner_name','like','%'.$data['owner_name'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('dealers.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $dealer){ 
                $checked='';
                if($dealer['status']==1){
                    $checked='on';
                }
                else{
                    $checked='off';
                }
                $actionValues='
                    <a style="font-size:8px;" title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-dealer/'.$dealer['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a style="font-size:11px;" title="Manage Dealer Stock" class="btn btn-sm red margin-top-10" href="'.url('/admin/manage-dealer-stock/'.$dealer['id']).'">Manage Stock</a>
                    <a style="font-size:11px;" title="Special Discount" class="btn btn-sm green margin-top-10" href="'.url('/admin/dealer-special-discount/'.$dealer['id']).'">Special Discount</a>
                    <a style="font-size:11px;" title="Dealer Users" class="btn btn-sm yellow margin-top-10" href="'.url('/admin/dealer-users/'.$dealer['id']).'">Add-on Users</a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $dealer['id'],
                    ucwords($dealer['business_name']),
                   // $dealer['owner_name'],
                    $dealer['city'],
                    $dealer['owner_mobile'],
                    '<div style="text-align:center;">'.$dealer['customers_count']."</div>",
                    '<div style="text-align:center;">'.$dealer['linked_products_count']."</div>",
                    '<div  id="'.$dealer['id'].'" rel="dealers" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Dealers";
        return View::make('admin.dealers.dealers')->with(compact('title'));
    }

    public function addEditDealer(Request $request,$dealerid=NULL){
        $selAppRoles = array();
    	if(!empty($dealerid)){
    		$dealerdata = Dealer::with(['contact_persons','linked_products'])->where('id',$dealerid)->first();
            $dealerdata = json_decode(json_encode($dealerdata),true);
            $selLinkedProids = array_column($dealerdata['linked_products'],'product_id');
            $selProductTypes = explode(',',$dealerdata['product_types']);
            $selProductTypes = array_unique($selProductTypes);
            if(!empty($dealerdata['app_roles'])){
                $selAppRoles = explode(',',$dealerdata['app_roles']);
            }

            $linkedCustomers = \App\Customer::where('business_model','Dealer')->where('dealer_id',$dealerid)->select('id','name')->where('status',1)->get()->toArray();
            //echo "<pre>"; print_r($selProductTypes); die;
    		$title ="Edit Dealer";
            $ignoreDealer = $dealerdata['id'];
    	}else{
    		$title ="Add Dealer";
	    	$dealerdata =array();
            $ignoreDealer = 0;
            $selLinkedProids = array();
            $selProductTypes = array();
            $linkedCustomers = array();
    	}
        $otherDealers = Dealer::where('id','!=',$ignoreDealer)->get()->toArray();
        //echo "<pre>"; print_r($dealerdata); die;
    	return view('admin.dealers.add-edit-dealer')->with(compact('title','dealerdata','otherDealers','selLinkedProids','selProductTypes','selAppRoles','linkedCustomers'));
    }

    public function saveDealer(Request $request){
    	/*try{*/
            if($request->ajax()){
                $data = $request->all();
                if($data['dealerid']==""){
                    $type ="add";
                    $emailunique = "unique:dealers,email";
                    $mobileunique = "unique:dealers,owner_mobile";
                    $pwdValidation = "bail|required|min:6";
                }else{ 
                    $type ="update";
                    $emailunique = "unique:dealers,email,".$data['dealerid'];
                    $mobileunique = "unique:dealers,owner_mobile,".$data['dealerid'];
                    $pwdValidation = "bail|min:6";
                }
                $validator = Validator::make($request->all(), [
                        'dealer_type'   =>  'bail|required',
                        'business_name'   =>  'bail|required',
                        'name'   =>  'bail|required',
                        'short_name'   =>  'bail|required',
                        'city'   =>  'bail|required',
                        'email'   => 'bail|email|'.$emailunique,
                        //'password' => $pwdValidation,
                        /*'owner_name'   =>  'bail|required',*/
                        //'base_sale_margin_lock'   =>  'bail|required',
                        'owner_mobile' => 'bail|required|numeric|digits:10|'.$mobileunique,
                        'payment_term' => 'bail|numeric',
                        'security_amount' => 'bail|regex:/^\d+(\.\d{1,2})?$/',
                        'credit_multiple' => 'bail|regex:/^\d+(\.\d{1,2})?$/',
                        'credit_allowed' => 'bail|regex:/^\d+(\.\d{1,2})?$/',
                        /*'base_sale_level_to_archive' => 'bail|required_if:base_sale_margin_lock,==,Applicable|nullable|regex:/^\d+(\.\d{1,2})?$/',
                        'freight' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                        'interest_rate_on_security' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|lte:99',
                        'margin_lock' => 'bail|required_if:base_sale_margin_lock,==,Applicable|nullable|regex:/^\d+(\.\d{1,2})?$/|lte:99',
                        'office_phone' => 'bail|numeric',
                        'applicable_from'  => 'bail|required_if:base_sale_margin_lock,==,Applicable|nullable|date_format:Y-m-d',
                        'applicable_to'  => 'bail|required_if:base_sale_margin_lock,==,Applicable|nullable|date_format:Y-m-d',*/
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    unset($data['_token']);
                    if($type =="add"){
                        $dealer = new Dealer; 
                        //$dealer->create($data);
                    }else{
                        $dealer = Dealer::find($data['dealerid']);
                        //$dealer->update($data); 
                    }
                    if(empty($data['password'])){
                        unset($data['password']);
                    }
                    $product_types = implode(',',$data['product_types']);
                    //echo "<pre>"; print_r($data); die;
                    unset($data['product_types']);
                    $data['product_types'] = $product_types;
                    if(isset($data['linked_dealers'])){
                        $linked_dealers = implode(',',$data['linked_dealers']);
                        unset($data['linked_dealers']);
                    }else{
                        $linked_dealers = "";
                    }
                    if(isset($data['linked_products'])){
                        $linked_products = $data['linked_products'];
                        unset($data['linked_products']);
                    }else{
                        $linked_products = array();
                    }
                    $contact_persons = array();
                    if(isset($data['names'])){
                        
                        $contact_persons['names'] = $data['names'];
                        $contact_persons['designations'] = $data['designations'];
                        $contact_persons['mobiles'] = $data['mobiles'];
                        $contact_persons['emails'] = $data['emails'];
                        unset($data['names']);
                        unset($data['designations']);
                        unset($data['mobiles']);
                        unset($data['emails']);
                    }
                    //echo "<pre>"; print_r($contact_persons); die;
                    unset($data['dealerid']);
                    /*if(!empty($data['password'])){
                        $data['password'] = bcrypt($data['password']);
                    }*/
                    foreach($data as $dkey=> $dealerinfo){
                        $dealer->$dkey = $dealerinfo;
                    }
                    $dealer->linked_dealers = $linked_dealers;
                    $dealer->app_roles = implode(',',$data['app_roles']);
                    $dealer->save();
                    /*DealerContactPerson::where('dealer_id',$dealer->id)->delete();
                    if(!empty($contact_persons)){
                        foreach ($contact_persons['names'] as $nkey => $contact_person) {
                            $saveContactPeople = new DealerContactPerson;
                                $saveContactPeople->dealer_id = $dealer->id;
                            $saveContactPeople->name = $contact_person;
                            $saveContactPeople->mobile = $contact_persons['mobiles'][$nkey];
                            $saveContactPeople->email = $contact_persons['emails'][$nkey];
                            $saveContactPeople->designation = $contact_persons['designations'][$nkey];
                            $saveContactPeople->save();
                        }
                    }*/
                    DealerLinkedProduct::where('dealer_id',$dealer->id)->delete();
                    if(!empty($linked_products)){
                        foreach ($linked_products as $key => $linkpro) {
                           $link_product = new DealerLinkedProduct;
                           $link_product->dealer_id = $dealer->id;
                           $link_product->product_id = $linkpro;
                           $link_product->save();
                        }
                    }
                    $redirectTo = url('/admin/dealers?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        /*}catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }*/
    }

    public function dealerIncentives(Request $Request){
        Session::put('active','dealerincentives'); 
        $incentives = DealerIncentive::orderby('start_date','DESC')->groupby('start_date')->pluck('start_date')->toArray();
        /*if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = DealerIncentive::query();
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('dealer_incentives.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $dealerincentive){ 
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-dealer-incentive/'.$dealerincentive['id']).'"> <i class="fa fa-edit"></i>
                    </a>
                    <a title="Clone" class="btn btn-sm yellow margin-top-10" href="'.url('/admin/add-edit-dealer-incentive/?type=clone&id='.$dealerincentive['id']).'"> <i class="fa fa-plus"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $dealerincentive['id'],
                    date('d M Y',strtotime($dealerincentive['start_date'])),
                    date('d M Y',strtotime($dealerincentive['end_date'])),
                    "Rs. " .$dealerincentive['range_from'],
                    "Rs. " .$dealerincentive['range_to'],
                    $dealerincentive['discount']."%", 
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }*/
        $title = "Dealer Incentive";
        return View::make('admin.dealers.dealer-incentives')->with(compact('title','incentives'));
    }

    public function addEditDealerIncentive(Request $request,$dealerincentiveid=NULL){
        $getLastDiscount = array();
        if(!empty($dealerincentiveid)){
            $dealerincentivedata = DealerIncentive::where('id',$dealerincentiveid)->first();
            $title ="Edit Dealer Incentive";
        }else{
            $title ="Add Dealer Incentive";
            $dealerincentivedata =array();
        }
        return view('admin.dealers.add-edit-dealer-incentive')->with(compact('title','dealerincentivedata','getLastDiscount','dealerincentiveid'));
    }

    public function saveDealerIncentive(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['dealerincentiveid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                    /*'start_date' => 'bail|required|date_format:Y-m-d',
                    'end_date' => 'bail|required|date_format:Y-m-d',*/
                    'range_from' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'range_to' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gt:'.$data['range_from'],
                    'discount' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gte:0|lte:99',     
                ]);
                if($validator->passes()) {
                    $data = $request->all();
                    unset($data['_token']);
                    if($type =="add"){
                        $dealerincentive = new DealerIncentive; 
                    }else{
                        $dealerincentive = DealerIncentive::find($data['dealerincentiveid']);
                        //Update Next Slot
                       /* $getNextDis = DealerIncentive::whereDate('start_date',$data['start_date'])->where('end_date',$data['end_date'])->where('range_from',$dealerincentive->range_to +1)->first();
                        $getNextDis = json_decode(json_encode($getNextDis),true);
                        if($getNextDis){
                            if($getNextDis['range_to'] > ($data['range_to'] +1) ){
                                DB::table('dealer_incentives')->where('id',$getNextDis['id'])->update(['range_from'=>$data['range_to']+1]);
                            }else{
                                return response()->json(['status'=>false,'errors'=>array('range_to'=>array('Range To value cannot be updated becuase its exceeded for next slot'))]);
                            }
                        }*/
                    }
                    $dealerincentive->month = $data['month'];
                    $dealerincentive->year = $data['year'];
                    $start_date = $data['year'].'-'.$data['month']."-01";
                    $dealerincentive->start_date = date('Y-m-d',strtotime($start_date));
                    $dealerincentive->range_from = $data['range_from'];
                    $dealerincentive->range_to   = $data['range_to'];
                    $dealerincentive->discount   = $data['discount'];
                    $dealerincentive->save();
                    $redirectTo = url('/admin/dealer-incentives?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }
    }

    public function deleteDealerIncentive($incentiveid){
        DealerIncentive::where('id',$incentiveid)->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function manageDealerStock(Request $request,$dealerid){
        $linkedProducts = DealerLinkedProduct::with('product')->where('dealer_id',$dealerid)->get();
        if($request->isMethod('post')){
            $data = $request->all();
            if(isset($data['stocks'])){
                foreach($data['stocks'] as $pid => $pro_stock){
                    $getStockInhand = \App\DealerProduct::getStockInhand($pid,$dealerid);
                    if(is_object($getStockInhand)){
                        DealerProduct::where('id',$getStockInhand->id)->update(['stock_in_hand'=>$pro_stock]);
                    }else{
                        $dealer_pro = new DealerProduct;
                        $dealer_pro->dealer_id = $dealerid;
                        $dealer_pro->product_id = $pid;
                        $dealer_pro->stock_in_hand = 0;
                        $dealer_pro->in_transit = 0;
                        $dealer_pro->pending_orders = 0;
                        $dealer_pro->pending_customer_orders = 0;
                        $dealer_pro->save();
                    } 
                }
            }
            return redirect()->back()->with('flash_message_success','Dealer product stock has been updated successfully!');
        }
        $title = "Manage Dealer Stock";
        return view('admin.dealers.manage-dealer-stock')->with(compact('title','linkedProducts','dealerid'));
    }

    public function qtyDiscounts(Request $Request){
        Session::put('active','qtyDiscounts'); 
        $products = QtyDiscount::join('products','products.id','=','qty_discounts.product_id')->select('products.product_name','products.id')->groupby('products.id')->get()->toArray();
        /*echo "<pre>"; print_r($products); die;
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = QtyDiscount::join('products','products.id','=','qty_discounts.product_id')->select('qty_discounts.*','products.product_name');
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('qty_discounts.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $productdis){ 
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-qty-discount/'.$productdis['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $num,
                    $productdis['product_name'],
                    $productdis['range_from']."kg",
                    $productdis['range_to']."kg",
                    $productdis['discount']."%", 
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }*/
        $title = "Qty Discounts";
        return View::make('admin.dealers.qty-discounts')->with(compact('title','products'));
    }

    public function addEditQtyDiscount(Request $request,$qtydiscountid=NULL){
        if(!empty($qtydiscountid)){
            $qtydiscountdata = QtyDiscount::where('id',$qtydiscountid)->first();

            $title ="Edit Qty Discount";
        }else{
            $title ="Add Qty Discount";
            $qtydiscountdata =array();
        }
        return view('admin.dealers.add-edit-qty-discount')->with(compact('title','qtydiscountdata','qtydiscountid'));
    }

    public function deleteQtyDiscount($disid){
        QtyDiscount::where('id',$disid)->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function saveProductDiscount(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['qtydiscountid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                    'product_id' => 'bail|required',
                    'range_from' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'range_to' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gt:'.$data['range_from'],
                    'discount' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gt:0|lte:99',     
                ]);
                if($validator->passes()) {
                    $data = $request->all();
                    unset($data['_token']);
                    if($type =="add"){
                        $qtyDis = new QtyDiscount; 
                    }else{
                        $qtyDis = QtyDiscount::find($data['qtydiscountid']);
                    }
                    $qtyDis->product_id = $data['product_id'];
                    /*$qtyDis->month = $data['month'];
                    $qtyDis->year = $data['year'];
                    $start_date = $data['year'].'-'.$data['month']."-01";
                    $qtyDis->start_date = date('Y-m-d',strtotime($start_date));*/
                    $qtyDis->range_from = $data['range_from'];
                    $qtyDis->range_to   = $data['range_to'];
                    $qtyDis->discount   = $data['discount'];
                    $qtyDis->save();
                    $redirectTo = url('/admin/qty-discounts?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }
    }

    public function dealerSpecialDiscount(Request $request,$dealerid){
        $linkedProducts = DealerLinkedProduct::where('dealer_id',$dealerid)->pluck('product_id')->toArray();
        $products = Product::where('status',1)->wherein('id',$linkedProducts)->orderby('product_name','ASC')->get();
        if($request->isMethod('post')){
            $data = $request->all();
            if(isset($data['discounts'])){
                foreach($data['discounts'] as $pid => $disc){
                    $getSpecialDis = \App\DealerSpecialDiscount::getSpecialDis($pid,$dealerid);
                    if(is_object($getSpecialDis)){
                        if(empty($disc) || $disc ==0){
                            DealerSpecialDiscount::where('id',$getSpecialDis->id)->delete();
                        }else{
                            DealerSpecialDiscount::where('id',$getSpecialDis->id)->update(['discount'=>$disc]);
                        }
                    }else{
                        if($disc >0){

                            $dealer_pro = new DealerSpecialDiscount;
                            $dealer_pro->dealer_id = $dealerid;
                            $dealer_pro->product_id = $pid;
                            $dealer_pro->discount = $disc;
                            $dealer_pro->save();
                        }
                    } 
                }
            }
            return redirect()->back()->with('flash_message_success','Dealer special discount has been updated successfully!');
        }
        $title = "Dealer Special Discount";
        return view('admin.dealers.dealer-special-discount')->with(compact('title','products','dealerid','linkedProducts'));
    }

    public function dealerAtod(Request $Request){
        $title = "AToD";
        Session::put('active','dealerAtod'); 
        $atodDiscounts = DealerAtod::orderby('end_date','DESC')->groupby('financial_year')->pluck('financial_year')->toArray();
        return view('admin.dealers.atod.atod')->with(compact('title','atodDiscounts'));
    }

    public function addEditAtod(Request $request,$atodid=NULL){
        $getLastDiscount = array();
        if(!empty($atodid)){
            $atoddata = DealerAtod::where('id',$atodid)->first();
            $title ="Edit AToD";
        }else{
            $title ="Add AToD";
            $atoddata =array();
        }
        return view('admin.dealers.atod.add-edit-atod')->with(compact('title','atoddata','atodid'));
    }

    public function saveAtod(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['atodid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                    'range_from' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/',
                    'range_to' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gt:'.$data['range_from'],
                    'discount' => 'bail|required|regex:/^\d+(\.\d{1,2})?$/|gte:0|lte:99',     
                ]);
                if($validator->passes()) {
                    $data = $request->all();
                    unset($data['_token']);
                    if($type =="add"){
                        $dealerATOD = new DealerAtod; 
                    }else{
                        $dealerATOD = DealerAtod::find($data['atodid']);
                    }
                    $dealerATOD->financial_year = $data['financial_year'];
                    $year_explode = explode('-',$data['financial_year']);
                    $dealerATOD->start_date = $year_explode[0]."-04"."-01";
                    $dealerATOD->end_date = $year_explode[1]."-03"."-31";
                    $dealerATOD->range_from = $data['range_from'];
                    $dealerATOD->range_to   = $data['range_to'];
                    $dealerATOD->discount   = $data['discount'];
                    $dealerATOD->created_by = \Auth::user()->id;
                    $dealerATOD->save();
                    $redirectTo = url('/admin/dealer-atod');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('value'=>$e->getMessage())]);
        }
    }

    public function deleteAtod($atodid){
        DealerAtod::where('id',$atodid)->delete();
        return redirect()->back()->with('flash_message_success','Record has been deleted successfully');
    }

    public function marketProductInfos(Request $Request){
        Session::put('active','marketProductInfos'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = MarketProductInfo::join('customers','customers.id','=','market_product_infos.customer_id')->leftjoin('dealers','dealers.id','=','market_product_infos.dealer_id')->leftjoin('users','users.id','=','market_product_infos.user_id')->select('market_product_infos.*','customers.name as customer_name','dealers.business_name as dealer_business_name','users.name as user_name');
            if(!empty($data['customer_name'])){
                $querys = $querys->where('customers.name','like','%'.$data['customer_name'].'%');
            }
            if(!empty($data['dealer_business_name'])){
                $querys = $querys->where('dealers.business_name','like','%'.$data['dealer_business_name'].'%');
            }
            if(!empty($data['executive'])){
                $querys = $querys->where('users.name','like','%'.$data['executive'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('market_product_infos.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $marketinfo){ 
                $products_info = $marketinfo['product_name']."<br>".'<small>Price : '.$marketinfo['price'].'<br><small>Dosage :'.$marketinfo['dosage'];
                $num = ++$i;
                $records["data"][] = array(      
                    date('d M Y',strtotime($marketinfo['created_at'])),
                    $marketinfo['customer_name'],
                    $marketinfo['dealer_business_name'],
                    $marketinfo['product_category_name'],
                    $marketinfo['make'].'<br><small>Supplier : '.$marketinfo['dealer_name'],  
                    $products_info,  
                    $marketinfo['monthly_consumption'],  
                    $marketinfo['user_name'],  
                    $marketinfo['remarks'], 
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Dealers";
        return View::make('admin.dealers.market_product_infos')->with(compact('title'));
    }

    public function dealerUsers($dealerId){
        $dealerinfo = Dealer::where('id',$dealerId)->first();
        $dealerUsers = Dealer::where('parent_id',$dealerId)->orderby('id','DESC')->where('is_delete',0)->get();
        $dealerUsers =  json_decode(json_encode($dealerUsers),true);
        $title = $dealerinfo->business_name;
        return view('admin.dealers.users.dealer_users')->with(compact('title','dealerUsers','dealerId'));
    }

    public function addEditDealerUser(Request $request,$dealerid=null){
        $selAppRoles = array();
        if(!empty($dealerid)){
            $dealerdata = Dealer::where('id',$dealerid)->first();
            $dealerdata = json_decode(json_encode($dealerdata),true);
            if(!empty($dealerdata['app_roles'])){
                $selAppRoles = explode(',',$dealerdata['app_roles']);
            }
            $title ="Edit";
            $parentDealerId = $dealerdata['parent_id'];
        }else{
            $parentDealerId = $_GET['dealer_id'];
            $title ="Add";
            $dealerdata =array();
        }
        $parentDealer = Dealer::where('id',$parentDealerId)->first();
        $parentShowClass = $parentDealer->show_class;
        $parentDealerAppRoles = explode(',',$parentDealer->app_roles);
        $app_roles = DB::table('app_roles')->where('type','dealer')->wherein('key',$parentDealerAppRoles)->orderby('sort_order','asc')->get();
        $appRoles = json_decode(json_encode($app_roles),true);
        return view('admin.dealers.users.add_edit_dealer_user')->with(compact('title','dealerdata','selAppRoles','parentDealerId','appRoles','parentShowClass'));
    }

    public function saveDealerUser(Request $request){
        if($request->ajax()){
            $data = $request->all();
            if($data['dealerid']==""){
                $type ="add";
                $emailunique = "unique:dealers,email";
                $mobileunique = "unique:dealers,owner_mobile";
            }else{ 
                $type ="update";
                $emailunique = "unique:dealers,email,".$data['dealerid'];
                $mobileunique = "unique:dealers,owner_mobile,".$data['dealerid'];
            }
            $validator = Validator::make($request->all(), [
                    'dealer_type'   =>  'bail|required',
                    'name'   =>  'bail|required',
                    'designation'   =>  'bail',
                    'email'   => 'bail|email|'.$emailunique,
                    'owner_mobile' => 'bail|required|numeric|digits:10|'.$mobileunique,
                ]
            );
            if($validator->passes()) {
                $data = $request->all();
                unset($data['_token']);
                if($type =="add"){
                    $dealer = new Dealer; 
                    //$dealer->create($data);
                }else{
                    $dealer = Dealer::find($data['dealerid']);
                    //$dealer->update($data); 
                }
                $dealer->parent_id = $data['parent_id'];
                $dealer->name = $data['name'];
                $dealer->designation = $data['designation'];
                $dealer->department = $data['department'];
                $dealer->email = $data['email'];
                $dealer->owner_mobile = $data['owner_mobile'];
                $dealer->dealer_type = $data['dealer_type'];
                $dealer->status = $data['status'];
                $dealer->show_class = "No";
                if(isset($data['show_class'])){
                     $dealer->show_class = $data['show_class'];
                }
                $dealer->app_roles = "";
                if(isset($data['app_roles'])){
                    $dealer->app_roles = implode(',',$data['app_roles']);
                }
                $dealer->save();
                $redirectTo = url('/admin/dealer-users/'.$data['parent_id']);
                return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
            }else{
                return response()->json(['status'=>false,'errors'=>$validator->messages()]);
            }
        }
    }

    public function deleteDealerUser($dealerid){
        Dealer::where('id',$dealerid)->update(['status'=>0,'is_delete'=>1]);
        return redirect()->back();
    }
}
