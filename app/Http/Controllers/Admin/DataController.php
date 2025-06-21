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
use App\LostSaleReport;
use App\Category;
use App\Dvr;
use App\Make;
use Validator;
class DataController extends Controller
{
    //
    public function dvrs(Request $Request){
        Session::put('active','dvrs'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Dvr::with('products')->leftjoin('customers','customers.id','=','dvrs.customer_id')->leftjoin('customer_register_requests','customer_register_requests.id','=','dvrs.customer_register_request_id')->join('users','users.id','=','dvrs.user_id')->select('dvrs.*','customers.name as customer_name','users.name as user_name','customer_register_requests.name as customer_register_request_name');
            if(!empty($data['customer_name'])){
                $querys = $querys->where(function($query)use($data){
                    $query->where('customers.name','like','%'.$data['customer_name'].'%')->orwhere('customer_register_requests.name','like','%'.$data['customer_name'].'%');
                });
            }
            if(!empty($data['product_name'])){
                $proIds = Product::where('products.product_name','like','%'.$data['product_name'].'%')->pluck('id')->toArray();
                $dvrIds = DB::table('dvr_products')->wherein('product_id',$proIds)->pluck('dvr_id');
                $dvrIds = json_decode(json_encode($dvrIds),true);
                $querys = $querys->whereIn('dvrs.id',$dvrIds);
            }
            if(!empty($data['executive'])){
                $querys = $querys->where('users.name','like','%'.$data['executive'].'%');
            }
            if(!empty($data['visit_type'])){
                $querys = $querys->where('dvrs.visit_type',$data['visit_type']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('dvrs.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $dvr){ 
                $products_involved = "";
                foreach($dvr['products'] as $product){
                    $products_involved .= $product['productinfo']['product_name'].", ";
                }
                $actionValues='';
                $num = ++$i;
                $records["data"][] = array(      
                    date('d M Y',strtotime($dvr['dvr_date'])),
                    $dvr['customer_name'].$dvr['customer_register_request_name'],
                    $dvr['user_name'],  
                    $dvr['visit_type'],  
                    $dvr['purpose_of_visit'],  
                    rtrim($products_involved,', '),  
                    '', 
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Customer Visit Report";
        return View::make('admin.data.dvrs')->with(compact('title'));
    }


    public function lostSalesInfo(Request $Request){
        Session::put('active','lostSalesInfo'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = LostSaleReport::join('customers','customers.id','=','lost_sale_reports.customer_id')->join('users','users.id','=','lost_sale_reports.created_by')->join('products','products.id','=','lost_sale_reports.product_id')->select('lost_sale_reports.*','customers.name as customer_name','users.name as user_name','products.product_name');
            if(!empty($data['customer_name'])){
                $querys = $querys->where('customers.name','like','%'.$data['customer_name'].'%');
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like','%'.$data['product_name'].'%');
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
                        ->OrderBy('lost_sale_reports.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $lostsale){ 
                $actionValues='
                    <a data-lost_sale_id="'.$lostsale['id'].'" title="View" class="btn btn-sm blue margin-top-10 getSaleReportDetails" href="javascript:;"> <i class="fa fa-file"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    date('d M Y',strtotime($lostsale['report_date'])),
                    $lostsale['customer_name'],
                    $lostsale['product_name'].'<br><small>'.$lostsale['monthly_requirement'].' kg</small>',  
                    $lostsale['reason'],  
                    $lostsale['replaced_by_product_name'].'<br><small>Make:'.$lostsale['replaced_by_company_name'].'</small>',  
                    $lostsale['user_name'],  
                    $lostsale['remarks'], 
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Lost Sale Report";
        return View::make('admin.data.lost_sale_reports')->with(compact('title'));
    }

    public function lostSalesInfoDetail(Request $request){
        $data = $request->all();
        $lost_sale_info = LostSaleReport::join('customers','customers.id','=','lost_sale_reports.customer_id')->join('users','users.id','=','lost_sale_reports.created_by')->join('products','products.id','=','lost_sale_reports.product_id')->select('lost_sale_reports.*','customers.name as customer_name','users.name as user_name','products.product_name')->where('lost_sale_reports.id',$data['lost_sale_id'])->first();
        $lost_sale_info = json_decode(json_encode($lost_sale_info),true);
        return response()->json([
            'view' => (String)View::make('admin.data.lost_sale_detail')->with(compact('lost_sale_info'))
        ]);
    }

    public function categories(Request $Request){
        Session::put('active','categories'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Category::query();
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('categories.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $category){ 
                $checked='';
                if($category['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit Category" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-category/'.$category['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $category['id'],
                    $category['name'],
                    '<div  id="'.$category['id'].'" rel="categories" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Category";
        return View::make('admin.data.categories.categories')->with(compact('title'));
    }

    public function addEditCategory(Request $request,$categoryid=NULL){
        if(!empty($categoryid)){
            $categorydata = Category::where('id',$categoryid)->first();
            $title ="Edit Make";
        }else{
            $title ="Add Make";
            $categorydata =array();
        }
        return view('admin.data.categories.add-edit-category')->with(compact('title','categorydata'));
    }

    public function saveCategory(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['categoryid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'name' => 'bail|required',
                        'status' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $category = new Category; 
                    }else{
                        $category = Category::find($data['categoryid']); 
                    }
                    $category->name = $data['name'];
                    $category->status = $data['status'];
                    $category->save();
                    $redirectTo = url('/admin/categories');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('size'=>$e->getMessage())]);
        }
    }

    public function make(Request $Request){
        Session::put('active','make'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Make::query();
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('makes.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $make){ 
                $checked='';
                if($make['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit Make" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-make/'.$make['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $make['id'],
                    $make['name'],
                    '<div  id="'.$make['id'].'" rel="makes" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Make";
        return View::make('admin.data.make.make')->with(compact('title'));
    }

    public function addEditMake(Request $request,$makeid=NULL){
        if(!empty($makeid)){
            $makedata = Make::where('id',$makeid)->first();
            $title ="Edit Make";
        }else{
            $title ="Add Make";
            $makedata =array();
        }
        return view('admin.data.make.add-edit-make')->with(compact('title','makedata'));
    }

    public function saveMake(Request $request){
        try{
            if($request->ajax()){
                $data = $request->all();
                if($data['makeid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'name' => 'bail|required',
                        'status' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $make = new Make; 
                    }else{
                        $make = Make::find($data['makeid']); 
                    }
                    $make->name = $data['name'];
                    $make->status = $data['status'];
                    $make->save();
                    $redirectTo = url('/admin/make');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('size'=>$e->getMessage())]);
        }
    }
}
