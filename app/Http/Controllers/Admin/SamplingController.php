<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\Sampling;
use App\SamplingItem;
use App\SamplingSaleInvoice;
use App\DealerProduct;
use App\FreeSamplingStock;
use App\UserFreeSampleStock;
use App\Product;
use Session;
use DB;
use PDF;
use Carbon\Carbon;
class SamplingController extends Controller
{
    //
    public function freeSampling(Request $Request){
        Session::put('active','freeSampling'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Sampling::with('sampleitems')->leftjoin('dealers','dealers.id','=','samplings.dealer_id')->where('sample_type','free')->leftjoin('users','users.id','=','samplings.user_id')->leftjoin('customers','customers.id','=','samplings.customer_id')->select('samplings.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','users.name as executive_name','users.email as executive_email','customers.name as customer_name');
            if(!empty($data['id'])){
                $querys = $querys->where('samplings.sample_ref_no_string',$data['sample_ref_no_string']);
            }
            if(!empty($data['status'])){
                if($data['status'] == "completed"){
                    $querys = $querys->wherein('samplings.sample_status',['executed','completed']);
                }else{
                    $querys = $querys->where('samplings.sample_status',$data['status']);
                }
            }else{
                /*$data['status'] = "pending";
                $querys = $querys->where('samplings.sample_status',$data['status']); */
            }
            if(!empty($data['user_type'])){
                $querys = $querys->where('samplings.action','like', '%' .$data['user_type']. '%');
            }
            if(!empty($data['dealer_info'])){
                $keyword = $data['dealer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('dealers.business_name', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.owner_mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.email', 'like', '%' . $keyword . '%');
                  });
            }
            if(!empty($data['employee_info'])){
                $keyword = $data['employee_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('users.name', 'like', '%' . $keyword . '%')
                       ->orWhere('users.email', 'like', '%' . $keyword . '%');
                  });
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('samplings.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $sampleReq){ 
                $actionValues='
                <a href=' .route('sampling.download.pdf', $sampleReq['id']).' class="btn btn-success">Download PDF</a>
                <a target="_blank" title="View Details" class="btn btn-sm green margin-top-10" href="'.url('admin/free-sampling-detail/'.$sampleReq['id']).'"> View
                    </a>';
                $userInfo = "";
                if(!empty($sampleReq['business_name'])){
                    $userInfo = ucwords($sampleReq['business_name']);
                }
                if(!empty($sampleReq['executive_name'])){
                    $userInfo = ucwords($sampleReq['executive_name']);
                }
                if($sampleReq['sample_status'] =="pending"){
                   $sampleReq['sample_status'] = "<b style='color:red;'>Pending Confirmation</b>";
                }elseif($sampleReq['sample_status'] =="approved"){
                   $sampleReq['sample_status'] = "<b>Pending Dispatch</b>"; 
                }elseif($sampleReq['sample_status'] =="executed"){
                    $sampleReq['sample_status'] = "<b style='color:green;'>Completed</b>"; 
                }elseif($sampleReq['sample_status'] =="completed"){
                    $sampleReq['sample_status'] = "<b style='color:green;'>Completed</b>";
                    if(empty($sampleReq['saleinvoices'])){
                        if(!empty($sampleReq['adjust_items'])){
                           $sampleReq['sample_status'] = 'Adjusted'; 
                        }else if(!empty($sampleReq['cancel_items'])){
                           $sampleReq['sample_status'] = 'Cancelled'; 
                        }
                    }else{
                        if(!empty($sampleReq['adjust_items'])){
                           $sampleReq['sample_status'] .= '<br><small> Partially Adjusted</small>'; 
                        }else if(!empty($sampleReq['cancel_items'])){
                           $sampleReq['sample_status'] .= '<br><small>Partially Cancelled</small>'; 
                        }
                    }
                }
                $products = '';
                if($sampleReq['sampleitems']){
                    $products =  '<table class="table table-bordered">
                                    <tr>
                                        <th>Product <br><small>(Pack Size)</small></th>
                                        <th>RQ</th>
                                        <th>AQ</th>
                                        <th>PQ</th>
                                    </tr>';
                    foreach($sampleReq['sampleitems'] as $sampleitem){
                        $item_action = "";
                        if($sampleitem['item_action']=="On Hold"){
                            $item_action = '<span class="badge badge-warning">'.$sampleitem['item_action'].'</span>';
                        }else if($sampleitem['item_action']=="Cancel"){
                            $item_action = '<span class="badge badge-dark">'.$sampleitem['item_action'].'</span>';
                        }else if($sampleitem['item_action']=="Urgent"){
                            $item_action = '<span class="badge badge-danger">'.$sampleitem['item_action'].'</span>';
                        }
                        $sale_invoice_qty = array_sum(array_column($sampleitem['sale_invoice_items'],'qty'));
                        if(empty($sale_invoice_qty)){
                            $sale_invoice_qty = 0;
                        }
                        $pending_qty = $sampleitem['actual_qty'] - $sale_invoice_qty;
                        //$pending_qty = $sampleitem['actual_qty'];
                        if($pending_qty ==0){
                            $item_action = "";
                        }
                        $products .= '<tr>
                                        <td>'.$sampleitem['product']['product_name'].'<br><small>('.$sampleitem['actual_pack_size'].'kg Packing)</small>'.$item_action.'</td>
                                        <td>'.$sampleitem['qty'].'kg</td>
                                        <td>'.$sampleitem['actual_qty'].'kg</td>
                                        <td>'.$pending_qty.'kg</td>
                                    </tr>';
                    }
                    $products .='</table>';
                }
                if($sampleReq['action'] =="user"){
                    $sampleReq['action'] = "executive";
                }
                $records["data"][] = array( 
                	$sampleReq['sample_ref_no_string'].'<br><small>('.
                    date('d M Y',strtotime($sampleReq['created_at'])).')</small>',
                    $userInfo,
                    $sampleReq['customer_name'],
                    $products,
                    $sampleReq['remarks'],
                    ucwords($sampleReq['sample_status']),
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Free Sample Requests";
        return View::make('admin.samplings.free.index')->with(compact('title'));
    }

    public function viewSampling($id)
    {
        $sampleDetails = Sampling::with([
            'customer',
            'user',
            'sampleitems.requested_product',
            'sampleitems.product'
        ])->findOrFail($id);
        //echo "<pre>"; print_r($sampleDetails->toArray()); die;
        /* 
         | Load ONLY ACTIVE products
         | Along with their LATEST dealer price (no future)
        */
        $products = Product::where('status', 1)
            ->with(['pricings' => function ($q) {
                $q->whereDate('price_date', '<=', Carbon::today())
                  ->orderBy('price_date', 'desc');
            }])
            ->orderByDesc('id')
            ->get();

        /*
         | Attach dealer_price to each product (computed attribute)
        */
        $products->each(function ($product) {
            $product->dealer_price = optional($product->pricings->first())->dealer_price ?? 0;
        });

        return view('admin.samplings.free.show', compact(
            'sampleDetails',
            'products'
        ));
    }


    public function paidSampling(Request $Request){
        Session::put('active','paidSampling'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Sampling::with(['sampleitems'])->leftjoin('dealers','dealers.id','=','samplings.dealer_id')->where('sample_type','paid')->select('samplings.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email');
            if(!empty($data['id'])){
                $querys = $querys->where('samplings.sample_ref_no_string',$data['sample_ref_no_string']);
            }
            if(!empty($data['status'])){
                if($data['status'] == "completed"){
                    $querys = $querys->wherein('samplings.sample_status',['executed','completed']);
                }else{
                    $querys = $querys->where('samplings.sample_status',$data['status']);
                }
            }
            if(!empty($data['dealer_info'])){
                $keyword = $data['dealer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('dealers.business_name', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.owner_mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.email', 'like', '%' . $keyword . '%');
                  });
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('samplings.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $sampleReq){ 
                $actionValues='<a target="_blank" title="View Details" class="btn btn-sm green margin-top-10" href="'.url('admin/paid-sampling-detail/'.$sampleReq['id']).'"> View
                    </a>';
                $userInfo = "";
                if(!empty($sampleReq['business_name'])){
                    $userInfo = ucwords($sampleReq['business_name']);
                }
                if(!empty($sampleReq['executive_name'])){
                    $userInfo = ucwords($sampleReq['executive_name']);
                }
                if($sampleReq['sample_status'] =="pending"){
                   $sampleReq['sample_status'] = "<b style='color:red;'>Pending Confirmation</b>"; 
                }elseif($sampleReq['sample_status'] =="approved"){
                   $sampleReq['sample_status'] = "<b>Pending Dispatch</b>"; 
                }elseif($sampleReq['sample_status'] =="executed"){
                    $sampleReq['sample_status'] = "<b style='color:green;'>Completed</b>"; 
                }elseif($sampleReq['sample_status'] =="completed"){
                    $sampleReq['sample_status'] = "<b style='color:green;'>Completed</b>";
                    if(empty($sampleReq['saleinvoices'])){
                        if(!empty($sampleReq['adjust_items'])){
                           $sampleReq['sample_status'] = 'Adjusted'; 
                        }else if(!empty($sampleReq['cancel_items'])){
                           $sampleReq['sample_status'] = 'Cancelled'; 
                        }
                    }else{
                        if(!empty($sampleReq['adjust_items'])){
                           $sampleReq['sample_status'] .= '<br><small> Partially Adjusted</small>'; 
                        }else if(!empty($sampleReq['cancel_items'])){
                           $sampleReq['sample_status'] .= '<br><small>Partially Cancelled</small>'; 
                        }
                    }
                }
                if($sampleReq['sampleitems']){
                    $products =  '<table class="table table-bordered">
                                    <tr>
                                        <th>Product<br><small>(Pack Size)</small></th>
                                        <th>RQ</th>
                                        <th>AQ</th>
                                        <th>PQ</th>
                                    </tr>';
                    foreach($sampleReq['sampleitems'] as $sampleitem){
                        $item_action = "";
                        if($sampleitem['item_action']=="On Hold"){
                            $item_action = '<span class="badge badge-warning">'.$sampleitem['item_action'].'</span>';
                        }else if($sampleitem['item_action']=="Cancel"){
                            $item_action = '<span class="badge badge-dark">'.$sampleitem['item_action'].'</span>';
                        }else if($sampleitem['item_action']=="Urgent"){
                            $item_action = '<span class="badge badge-danger">'.$sampleitem['item_action'].'</span>';
                        }
                        $sale_invoice_qty = array_sum(array_column($sampleitem['sale_invoice_items'],'qty'));
                        if(empty($sale_invoice_qty)){
                            $sale_invoice_qty = 0;
                        }
                        $pending_qty = $sampleitem['actual_qty'] - $sale_invoice_qty;
                        //$pending_qty = $sampleitem['actual_qty'];
                        if($pending_qty ==0){
                            $item_action = "";
                        }
                        $products .= '<tr>
                                        <td>'.$sampleitem['product']['product_name'].'<br><small>('.$sampleitem['actual_pack_size'].'kg Packing)</small>'.$item_action.'</td>
                                        <td>'.$sampleitem['qty'].'kg</td>
                                        <td>'.$sampleitem['actual_qty'].'kg</td>
                                        <td>'.$pending_qty.'kg</td>
                                    </tr>';
                    }
                    $products .='</table>';
                }
                $records["data"][] = array( 
                	$sampleReq['sample_ref_no_string'].'<br><small>('.
                    date('d M Y',strtotime($sampleReq['created_at'])).')</small>',
                    $userInfo,
                    $sampleReq['request_type'],
                    $products,
                    $sampleReq['remarks'],
                    ucwords($sampleReq['sample_status']).'<br><small>('.ucwords($sampleReq['required_through']).')</small>',
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Paid Sample Requests";
        return View::make('admin.samplings.paid-sampling')->with(compact('title'));
    }

    public function freeSamplingDetail($samplingid){
        $sampleDetails = Sampling::with(['dealer','user','sampleitems','sale_invoices'])->where('id',$samplingid)->first();
        $sampleDetails = json_decode(json_encode($sampleDetails),true);
        //echo "<pre>"; print_r($sampleDetails); die;
        $linkedProducts = \App\DealerLinkedProduct::where('dealer_id',$sampleDetails['dealer_id'])->pluck('product_id')->toArray();
        $title = "Free Sampling Detail";
        return view('admin.samplings.free-sampling-detail')->with(compact('sampleDetails','title','linkedProducts'));
    }

    public function paidSamplingDetail($samplingid){
        $sampleDetails = Sampling::with(['dealer','user','sampleitems','sale_invoices'])->where('id',$samplingid)->first();
        $sampleDetails = json_decode(json_encode($sampleDetails),true);
        //echo "<pre>"; print_r($sampleDetails); die;
        $linkedProducts = \App\DealerLinkedProduct::where('dealer_id',$sampleDetails['dealer_id'])->pluck('product_id')->toArray();
        $title = "Paid Sampling Detail";
        return view('admin.samplings.paid-sampling-detail')->with(compact('sampleDetails','title','linkedProducts'));
    }

    public function markUrgentSampleItem(Request $request){
        if($request->ajax()){
            $data = $request->all();
            SamplingItem::where('id',$data['orderitemid'])->update(['item_action'=>$data['value']]);
            return response()->json(['status'=>true]);
        }
    }

    public function UpdateSamplingStatus(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            Sampling::where('id',$data['sampling_id'])->update(['sample_status'=>$data['sample_status'],'comments'=>$data['comments'],'reason'=> $data['reason']]);
            return redirect()->back()->with('flash_message_success','Status has been updated successfully');
        }
    }

    public function updateSamplingQty(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $sampleDetails = Sampling::with(['dealer','user','sampleitems'])->where('id',$data['sampling_id'])->first();
            $sampleDetails = json_decode(json_encode($sampleDetails),true);
            
            $subtotal = 0;
            if($sampleDetails['sample_type'] =='free'){
                //nothing to do here in case of free samplings
            }else{
                foreach($data['item_ids'] as $ikey=> $itemid){
                    $itemDetails = SamplingItem::find($itemid);
                    if($data['actual_qtys'][$ikey]>$itemDetails['qty']){
                        return redirect()->back()->with('flash_message_error','You have entered wrong qty');
                    }
                    /*if($data['actual_pack_sizes'][$ikey]>$itemDetails['pack_size']){
                        return redirect()->back()->with('flash_message_error','You have entered wrong pack size');
                    }*/
                    if(!empty($sampleDetails['dealer'])){
                        if($data['product_links'][$ikey] == 0){
                            if($data['actual_qtys'][$ikey] > 0){
                                return redirect()->back()->with('flash_message_error','You can not accept an order from non linked product');
                            }
                        }
                    }
                }
            }
            //echo "<pre>"; print_r($data); die;
            DB::beginTransaction();
            foreach($data['item_ids'] as $ikey=> $itemid){
                $itemDetails = SamplingItem::find($itemid);
                $itemDetails->actual_qty = $data['actual_qtys'][$ikey];
                $itemDetails->actual_pack_size = $data['actual_pack_sizes'][$ikey];
                $itemDetails->comments = $data['comments'][$ikey];
                $itemDetails->dispatched_qty = 0;
                $itemDetails->save();
                $subtotal +=  $itemDetails->net_price * $data['actual_qtys'][$ikey];
                if($sampleDetails['sample_type'] =='paid'){
                    //Create or update dealer Pending orders
                    $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$sampleDetails['dealer_id'],'product_id'=>$itemDetails->product_id])->first();
                    if($dealerProd){
                        $dealerProduct =  DealerProduct::find($dealerProd->id);
                        $pendingOrders = $dealerProd->pending_orders;
                    }else{
                        $dealerProduct = new DealerProduct;
                        $dealerProduct->dealer_id = $sampleDetails['dealer_id'];
                        $dealerProduct->product_id = $itemDetails->product_id;
                        $pendingOrders = 0;
                    }
                    $dealerProduct->pending_orders = $pendingOrders + $data['actual_qtys'][$ikey];
                    $dealerProduct->save();
                }else{
                    $freeSamplingStock = DB::table('free_sampling_stocks')->where(['product_id'=>$itemDetails->product_id]);
                    if(!empty($sampleDetails['dealer'])){
                        $freeSamplingStock = $freeSamplingStock->where('dealer_id',$sampleDetails['dealer_id']);
                    }else{
                        $freeSamplingStock = $freeSamplingStock->where('user_id',$sampleDetails['user_id']);
                    }
                    $freeSamplingStock = $freeSamplingStock->first();
                    if($freeSamplingStock){
                        $sampleProd =  FreeSamplingStock::find($freeSamplingStock->id);
                        $pendingOrders = $freeSamplingStock->pending_orders;
                    }else{
                        $sampleProd = new FreeSamplingStock;
                        $sampleProd->dealer_id = $sampleDetails['dealer_id'];
                        $sampleProd->user_id = $sampleDetails['user_id'];
                        $sampleProd->customer_id = $sampleDetails['customer_id'];
                        $sampleProd->product_id = $itemDetails->product_id;
                        $pendingOrders = 0;
                    }
                    $sampleProd->pending_orders = $pendingOrders + $data['actual_qtys'][$ikey];
                    $sampleProd->save();
                }
            }
            $updateSampling = Sampling::find($data['sampling_id']);
            $updateSampling->subtotal = $subtotal;
            $gstVal = (($subtotal * $updateSampling->gst_per)/100);
            $updateSampling->gst = $gstVal;
            $updateSampling->grand_total = $subtotal + $gstVal;
            $updateSampling->sample_edited = 'yes';
            $updateSampling->sample_status = 'approved';
            if(isset($data['required_through'])){
                $updateSampling->required_through = $data['required_through'];
            }
            $updateSampling->save();
            DB::commit();
            if(isset($data['source'])){
                return redirect::to('/admin/paid-sampling')->with('flash_message_success','Qty has been updates successfully');
            }else{
                return redirect::to('/admin/free-sampling')->with('flash_message_success','Qty has been updates successfully');
            }
        }
    }

     public function samplingDispatchPlanning(Request $Request){
        Session::put('active','samplingDispatchPlanning'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = SamplingItem::with('sampling')->join('products','products.id','=','sampling_items.product_id')->join('samplings','samplings.id','=','sampling_items.sampling_id')->leftjoin('dealers','dealers.id','=','samplings.dealer_id')->leftjoin('users','users.id','=','samplings.user_id')->leftjoin('customers','customers.id','=','samplings.customer_id')->select('samplings.id','sampling_items.id as order_item_id','samplings.created_at','samplings.dealer_id','dealers.business_name','samplings.sample_type','samplings.required_through','sampling_items.sampling_id','sampling_items.product_id','sampling_items.actual_qty','sampling_items.dispatched_qty','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','products.product_name','products.product_code','sampling_items.is_urgent','sampling_items.item_action','customers.name as customer_name','users.name as executive_name','users.mobile as executive_mobile','sampling_items.actual_pack_size')->where('samplings.sample_status','approved')->whereColumn('sampling_items.actual_qty','!=','sampling_items.dispatched_qty');
            if(!empty($data['dealer_info'])){
                $keyword = $data['dealer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('dealers.business_name', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.owner_mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.email', 'like', '%' . $keyword . '%');
                });
            }
            if(!empty($data['customer_info'])){
                $keyword = $data['customer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('customers.name', 'like', '%' . $keyword . '%');
                });
            }
            if(!empty($data['user_type'])){
                if($data['user_type'] =="Dealer"){
                    $querys = $querys->where('dealers.business_name','!=','');
                }else{
                    $querys = $querys->where('users.name','!=','');
                }   
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like', '%' .$data['product_name']. '%');
            }
            if(!empty($data['po_no'])){
                $querys = $querys->where('samplings.sample_ref_no_string',$data['po_no']);
            }
            if(!empty($data['urgent'])){
                $querys = $querys->where('sampling_items.item_action','Urgent');
            }
            if(!empty($data['date'])){
                $querys = $querys->whereDate('sampling_items.created_at',$data['date']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('sampling_items.created_at','ASC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $poInfo){ 
                $userInfo = "";
                if(!empty($poInfo['business_name'])){
                    $userInfo = ucwords($poInfo['business_name']);
                }else{
                    $userInfo = ucwords($poInfo['executive_name']);
                }
                $actionValues = "";
                if($poInfo['item_action'] =="" || $poInfo['item_action'] =="Urgent"){
                    $actionValues='<a style="display:none;" title="Update Status" class="btn btn-sm green margin-top-10 getProductBatches" data-orderitemid="'.$poInfo['order_item_id'].'" href="javascript:;"> Update</a>
                    <a title="Update Status" class="btn btn-sm green margin-top-10 openDispatchItemModal" data-username="'.$userInfo.'" data-productname="'.$poInfo['product_name'].'" data-orderitemid="'.$poInfo['order_item_id'].'" href="javascript:;"> Update</a>';
                }
                
                $item_action = "";
                if($poInfo['item_action'] != ''){
                    if($poInfo['item_action']=="On Hold"){
                        $item_action = '<span class="badge badge-warning">'.$poInfo['item_action'].'</span>';
                    }else if($poInfo['item_action']=="Cancel"){
                        $item_action = '<span class="badge badge-dark">'.$poInfo['item_action'].'</span>';
                    }else if($poInfo['item_action']=="Urgent"){
                        $item_action = '<span class="badge badge-danger">'.$poInfo['item_action'].'</span>';
                    }
                }
                $type = "Executive";
                if(!empty($poInfo['business_name'])){
                    $type = "Dealer";
                }
                $records["data"][] = array(
                    ucwords($poInfo['sample_type']),
                    $poInfo['sampling']['sample_ref_no_string'].'<br><small>'.
                    date('d M Y',strtotime($poInfo['created_at'])).'<small>',  
                    $type,  
                    $userInfo,
                    $poInfo['product_name'].'<br><small>('.$poInfo['product_code'].')</small>'.$item_action,
                    $poInfo['actual_qty'] - $poInfo['dispatched_qty'].'kg <br><small>('.$poInfo['actual_pack_size'].'kg Packing)</small>',
                    ucwords($poInfo['required_through']),
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Sample Dispatch Planning";
        return View::make('admin.samplings.dispatch-planning')->with(compact('title'));
    }


    public function updateProSampleDispatchQty(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $orderitemDetails = SamplingItem::details($data['order_item_id']);
            $requiredQty = $orderitemDetails['actual_qty'] - $orderitemDetails['dispatched_qty'];
            $stocks = $data['issue_stock'];
            $requestedStock = $data['issue_stock'];
            if(!is_numeric($requestedStock)){
                return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Please enter the issue stock')]);
            }
            if($requestedStock > $requiredQty){
                return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Your requested issue qty must be less then or equal to '.$requiredQty)]);
            }else{
                $poinfo = Sampling::where('id',$orderitemDetails['sampling_id'])->first();
                DB::beginTransaction();
                //Create Sale Invoice
                $createSO = new SamplingSaleInvoice;
                //$createSO->sale_invoice_date = date('Y-m-d');
                $createSO->sampling_id =  $orderitemDetails['sampling_id'];
                $createSO->sampling_item_id =  $data['order_item_id'];
                $createSO->dealer_id =  $poinfo->dealer_id;
                $createSO->user_id =  $poinfo->user_id;
                $createSO->customer_id =  $poinfo->customer_id;
                $createSO->batch_no = $data['batch_no'];
                $createSO->product_id = $orderitemDetails['product_id'];
                $createSO->qty = $requestedStock;
                $createSO->price = $orderitemDetails['net_price'];
                $createSO->subtotal = $orderitemDetails['net_price'] * $requestedStock;
                
                $totalPrice = $orderitemDetails['net_price'] * $requestedStock;
                $totatSaleAmt = $orderitemDetails['net_price'] * $requestedStock;
                //Update sale invoice 
                $data['gst'] = 18;
                $calGST =  ($totatSaleAmt *$data['gst']) /100;
                $totatSaleAmt = $totatSaleAmt + $calGST;
                $createSO->price =  $totalPrice;
                $createSO->gst   = $calGST;
                $createSO->gst_per   = $data['gst'];
                $createSO->grand_total = $totatSaleAmt;
                $createSO->save();
                //Update PO
                SamplingItem::where('id',$orderitemDetails['id'])->increment('dispatched_qty',$requestedStock);
                //Decrement Stock
                Product::where('id',$orderitemDetails['product_id'])->decrement('current_stock',$requestedStock);
                $getAllPO= SamplingItem::where('sampling_id',$orderitemDetails['sampling_id'])->whereColumn('actual_qty','!=','dispatched_qty')->count();
                if($getAllPO == 0){
                    Sampling::where('id',$orderitemDetails['sampling_id'])->update(['sample_status'=>'executed']);
                }
                DB::commit();
                return response()->json([
                    'status' =>true,
                    'url'  => url('admin/sampling-dispatch-planning')
                ]);
            }
        }
    }

    public function sampleFinalizeDo($type){
        Session::put('active','sampleFinalizeDo'); 
        $title = "Finalize D.O.";
        $dealers = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->join('dealers','dealers.id','=','sampling_sale_invoices.dealer_id')->where('sampling_sale_invoices.do_number','')->where('sampling_sale_invoices.invoice_no','')->where('samplings.sample_type',$type)->whereNull('sampling_sale_invoices.user_id')->groupby('sampling_sale_invoices.dealer_id')->select('sampling_sale_invoices.dealer_id','dealers.business_name as name')->get()->toArray();
        $executives = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->join('users','users.id','=','sampling_sale_invoices.user_id')->where('sampling_sale_invoices.do_number','')->where('sampling_sale_invoices.invoice_no','')->whereNull('sampling_sale_invoices.dealer_id')->where('samplings.sample_type',$type)->groupby('sampling_sale_invoices.user_id')->select('sampling_sale_invoices.user_id','users.name as name')->get()->toArray();
        $users = array_merge($dealers,$executives);
        $keys = array_column($users, 'name');
        array_multisort($keys, SORT_ASC, $users);
        //echo "<pre>"; print_r($users); die;
        return view('admin.samplings.finalize-do')->with(compact('title','users','type'));
    }

    public function undoSampleFinalizeDO($saleInvoiceid,$samplingitemid){
        $SamplingSaleInvoice = SamplingSaleInvoice::where('id',$saleInvoiceid)->first();
        $details = SamplingItem::find($samplingitemid);
        SamplingItem::where('id',$samplingitemid)->decrement('dispatched_qty',$SamplingSaleInvoice->qty);
        Product::where('id',$details->product_id)->increment('current_stock',$SamplingSaleInvoice->qty);
        SamplingSaleInvoice::where('id',$saleInvoiceid)->delete();
        return redirect::to('/admin/sample-finalize-do/paid')->with('flash_message_success','Updated successfully');
    }

    public function samplingGenerateDoNumbers(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
            $lastDoNumber = SamplingSaleInvoice::orderby('do_number','DESC')->where('do_number','>',0)->where('do_financial_year',financialYear())->first();
            if(is_object($lastDoNumber)){
                $doNumber = $lastDoNumber->do_number +1;
            }else{
                $doNumber = 1;
            }
            foreach($saleInvoiceIds as $invoiceid){
                $saleInvoice = SamplingSaleInvoice::find($invoiceid);
                $saleInvoice->do_number = $doNumber;
                $saleInvoice->do_ref_no = "S-".$doNumber.'/'.financialYear();
                $saleInvoice->do_financial_year = financialYear();
                $saleInvoice->do_date = date('Y-m-d H:i:s');
                $saleInvoice->save();
            }
            Session::flash('flash_message_success','DO number has been generated successfully');
            return response()->json([
                'status' =>true,
                'url'    =>  url('/admin/sample-finalize-do/'.$data['type'])
            ]);
        }
    }

    public function sampleDoReady(){
        Session::put('active','sampleDoReady'); 
        $title = "D.O. Ready";
        $users = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->leftjoin('dealers','dealers.id','=','sampling_sale_invoices.dealer_id')->leftjoin('users','users.id','=','sampling_sale_invoices.user_id')->where('sampling_sale_invoices.invoice_no','')->where('sampling_sale_invoices.do_number','!=','')->select('sampling_sale_invoices.dealer_id','sampling_sale_invoices.user_id','sampling_sale_invoices.do_ref_no','sampling_sale_invoices.do_date','samplings.required_through','samplings.sample_type','dealers.business_name as dealer_name','users.name as executive_name',DB::RAW("CONCAT(COALESCE(dealers.business_name,''),COALESCE(users.name,'')) AS name"))->groupby('sampling_sale_invoices.do_ref_no')->get()->toArray();
        $keys = array_column($users, 'do_ref_no');
        array_multisort($keys, SORT_ASC, $users);
        //echo "<pre>"; print_r($users); die;
        return view('admin.samplings.sample-do-ready')->with(compact('title','users'));
    }

    public function updateBulkSampleSaleInvoice(Request $request){
        if($request->all()){
            $data = $request->all();
            $invoice_no_details = SamplingSaleInvoice::where('invoice_no',$data['invoice_number'])->first();
            if(is_object($invoice_no_details)){
                $invoice_no_details = json_decode(json_encode($invoice_no_details),true);
                if(!empty($data['dealer_id']) &&  !empty($invoice_no_details['dealer_id'])){
                    if($invoice_no_details['dealer_id'] != $data['dealer_id']){
                        return redirect::to('admin/do-ready')->with('flash_message_error','This Invoice no has already been used.'); 
                    }
                }elseif(!empty($data['user_id']) &&  !empty($lr_no_detials['user_id'])){
                    if($lr_no_detials['user_id'] != $data['user_id']){
                        return redirect::to('admin/do-ready')->with('flash_message_error','This Invoice no has already been used.'); 
                    }
                }
            }
            $data = $request->all();
            $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
            foreach($saleInvoiceIds as $saleInvoice){
                $saleInv = SamplingSaleInvoice::find($saleInvoice);
                $saleInv->sale_invoice_date = $data['sale_invoice_date'];
                $saleInv->invoice_no = $data['invoice_number'];
                $saleInv->save();
            }
            Session::flash('flash_message_success','Details has been updated successfully');
            return redirect::to('/admin/sample-do-ready');
        }
    }


    public function sampleBillReady(){
        Session::put('active','sampleBillReady'); 
        $title = "Bill Ready";
        $users = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->leftjoin('dealers','dealers.id','=','sampling_sale_invoices.dealer_id')->leftjoin('users','users.id','=','sampling_sale_invoices.user_id')->where('sampling_sale_invoices.invoice_no','!=','')->where('sampling_sale_invoices.transport_name','')->select('sampling_sale_invoices.dealer_id','sampling_sale_invoices.sale_invoice_date','sampling_sale_invoices.user_id','sampling_sale_invoices.invoice_no','sampling_sale_invoices.sale_invoice_date','samplings.required_through','samplings.sample_type','dealers.business_name as dealer_name','users.name as executive_name',DB::RAW("CONCAT(COALESCE(dealers.business_name,''),COALESCE(users.name,'')) AS name"))->groupby('sampling_sale_invoices.invoice_no')->get()->toArray();
        $keys = array_column($users, 'sale_invoice_date');
        array_multisort($keys, SORT_ASC, $users);
        //echo "<pre>"; print_r($users); die;
        return view('admin.samplings.sample-bill-ready')->with(compact('title','users'));
    }

    public function updateBulkSampleLrSaleInvoice(Request $request){
        if($request->all()){
            $data = $request->all();
            $lr_no_detials = SamplingSaleInvoice::where('lr_no',$data['lr_no'])->first();
            if(is_object($lr_no_detials)){
                $lr_no_detials = json_decode(json_encode($lr_no_detials),true);
                if(!empty($data['dealer_id']) &&  !empty($lr_no_detials['dealer_id'])){
                    if($lr_no_detials['dealer_id'] != $data['dealer_id']){
                        return redirect::to('admin/sample-bill-ready')->with('flash_message_error','This LR no has already been used.'); 
                    }
                }elseif(!empty($data['user_id']) &&  !empty($lr_no_detials['user_id'])){
                    if($lr_no_detials['user_id'] != $data['user_id']){
                        return redirect::to('admin/sample-bill-ready')->with('flash_message_error','This LR no has already been used.'); 
                    }
                }
            }
            $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
            foreach($saleInvoiceIds as $saleInvoice){
                $saleInv = SamplingSaleInvoice::with('sampling')->find($saleInvoice);
                $saleInv->dispatch_date = $data['dispatch_date'];
                $saleInv->lr_no = $data['lr_no'];
                $saleInv->transport_name = $data['transport_name'];
                $saleInv->save();
                //Manage Stock here
                if($saleInv->sampling->sample_type == "free")
                {
                    if(!empty($saleInv->user_id)){
                        //for executive
                        $freeSamplingStock = DB::table('free_sampling_stocks')->where(['product_id'=>$saleInv->product_id,'user_id'=>$saleInv->user_id])->first();
                    }else if(!empty($saleInv->dealer_id)){
                        //for executive
                        $freeSamplingStock = DB::table('free_sampling_stocks')->where(['product_id'=>$saleInv->product_id,'dealer_id'=>$saleInv->dealer_id])->first();
                    }
                    $freesampleProd =  FreeSamplingStock::find($freeSamplingStock->id);
                    $pendingOrders = $freesampleProd->pending_orders;
                    $freesampleProd->pending_orders = $pendingOrders- $saleInv->qty;
                    $freesampleProd->in_transit = $freesampleProd->in_transit + $saleInv->qty;
                    $freesampleProd->save();
                
                }else{
                    //for paid samplings
                    $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$saleInv->dealer_id,'product_id'=>$saleInv->product_id])->first();
                    if(is_object($dealerProd)) {
                        $updatedealerProd = DealerProduct::find($dealerProd->id);
                        $pendingOrders = $updatedealerProd->pending_orders;
                        $updatedealerProd->pending_orders = $pendingOrders- $saleInv->qty;
                        $updatedealerProd->in_transit = $updatedealerProd->in_transit + $saleInv->qty;
                        $updatedealerProd->save();
                    }
                }
            }
            Session::flash('flash_message_success','Details has been updated successfully');
            return redirect::to('/admin/sample-bill-ready');
        }
    }

    public function sampleDispatchedMaterial(Request $request){
        $data = $request->all();
        Session::put('active','sampleDispatchedMaterial'); 
        $title = "Dispatched Sample";
        $users = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->leftjoin('dealers','dealers.id','=','sampling_sale_invoices.dealer_id')->leftjoin('users','users.id','=','sampling_sale_invoices.user_id')->where('sampling_sale_invoices.invoice_no','!=','')->where('sampling_sale_invoices.lr_no','!=','')->select('sampling_sale_invoices.dealer_id','sampling_sale_invoices.dispatch_date','sampling_sale_invoices.sale_invoice_date','sampling_sale_invoices.user_id','sampling_sale_invoices.invoice_no','sampling_sale_invoices.lr_no','sampling_sale_invoices.batch_no','samplings.sample_ref_no_string','samplings.required_through','samplings.sample_type','dealers.business_name as dealer_name','users.name as executive_name',DB::RAW("CONCAT(COALESCE(dealers.business_name,''),COALESCE(users.name,'')) AS name"))->orderby('dispatch_date','DESC');
            if(isset($data['product_id'])&& !empty($data['product_id'])){
                $users = $users->where('sampling_sale_invoices.product_id',$data['product_id']);
            }
            if(isset($data['batch_no'])&& !empty($data['batch_no'])){
                $users = $users->where('sampling_sale_invoices.batch_no',$data['batch_no']);
            }
            if(isset($data['name'])){
                $users = $users->where(function($query)use($data){
                    $query->where('users.name','like', '%' .$data['name']. '%')->where('sampling_sale_invoices.dispatch_date','!=','0000-00-00');
                })->orwhere(function($query)use($data){
                    $query->where('dealers.business_name','like', '%' .$data['name']. '%')->where('sampling_sale_invoices.dispatch_date','!=','0000-00-00');
                });
            }
            $users = $users->groupby('sampling_sale_invoices.invoice_no')->simplePaginate(500);
        //echo "<pre>"; print_r(json_decode(json_encode($users),true)); die;
        return view('admin.samplings.sample-dispatched-material')->with(compact('title','users','data'));
    }

    public function downloadPdf($id)
    {
        $sampling = Sampling::with([
            'sampleitems',
            'customer',
            'user'      // executive
        ])->findOrFail($id);

        $pdf = PDF::loadView('admin.samplings.free.pdf', compact('sampling'))
            ->setPaper('A4', 'portrait');

        return $pdf->download(
            'Sampling-' . $sampling->sample_ref_no_string . '.pdf'
        );
    }
}
