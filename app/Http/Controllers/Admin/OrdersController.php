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
use App\PurchaseOrder;
use App\PurchaseOrderItem;
use App\DealerIncentive;
use App\BatchSheet;
use App\SaleInvoice;
use App\SaleInvoiceItem;
use App\PurchaseOrderAdjustment;
use App\Product;
use App\CustomerProduct;
use App\DealerProduct;
use Validator;
use Carbon\Carbon;
class OrdersController extends Controller
{
    //
    public function customerOrders(Request $Request){
        Session::put('active','customerOrders'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = PurchaseOrder::with('orderitems')->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->join('customers','customers.id','=','purchase_orders.customer_id')->wherein('purchase_orders.action',['dealer_customer','customer_employee'])->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','customers.name as customer_name','customers.mobile as customer_mobile','customers.email as customer_email');
            if(!empty($data['id'])){
                $querys = $querys->where('purchase_orders.id',$data['id']);
            }
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
                    $query->where('customers.name', 'like', '%' . $keyword . '%')
                       ->orWhere('customers.mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('customers.email', 'like', '%' . $keyword . '%');
                });
            }
            if(!empty($data['customer_purchase_order_no'])){
                $querys = $querys->where('purchase_orders.customer_purchase_order_no',$data['customer_purchase_order_no']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('purchase_orders.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $customerpo){ 
                $actionValues='<a target="_blank" title="View Details" class="btn btn-sm green margin-top-10" href="'.url('admin/customer-purchase-order-detail/'.$customerpo['id']).'"> View
                    </a>';

                if($customerpo['orderitems']){
                    $products =  '<table class="table table-bordered">
                                    <tr>
                                        <th>Product</th>
                                        <th>OQ</th>
                                    </tr>';
                    foreach($customerpo['orderitems'] as $orderitem){
                        $products .= '<tr>
                                        <td>'.$orderitem['product']['product_name'].'</td>
                                        <td>'.$orderitem['actual_qty'].'</td>
                                    </tr>';
                    }
                    $products .='</table>';
                }
                $dealerInfo = "";
                if(!empty($customerpo['business_name'])){
                	$dealerInfo = '<b>Name :- </b>'.ucwords($customerpo['business_name']).'<br> <b>Email :- </b>'.$customerpo['dealer_email'].'<br> <b>Mobile :- </b>'.$customerpo['dealer_mobile'];
                    $dealerInfo = ucwords($customerpo['business_name']);
                }
                $customerInfo = '<b>Name :- </b>'.ucwords($customerpo['customer_name']).'<br> <b>Email :- </b>'.$customerpo['customer_email'].'<br> <b>Mobile :- </b>'.$customerpo['customer_mobile'];
                $customerInfo = ucwords($customerpo['customer_name']);
                if($customerpo['action'] == "dealer_customer"){
                	$pocreatedby = "Dealer";
                }else{
                	$pocreatedby = "Customer";
                }
                $records["data"][] = array(      
                    date('d M Y',strtotime($customerpo['created_at'])),
                    $customerInfo,
                    $dealerInfo,
                    $customerpo['customer_purchase_order_no'],
                    $products,
                    $customerpo['mode'],
                    $customerpo['remarks'],
                    $pocreatedby,
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Customer Orders";
        return View::make('admin.orders.customer-orders')->with(compact('title'));
    }

    public function customerPurchaseOrderDetail($id){
        $title = "Customer Purchase Order Detail";
        $poDetail = PurchaseOrder::with(['orderitems','sale_invoices'])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->where('purchase_orders.id',$id)->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','customers.name as customer_name','customers.mobile as customer_mobile','customers.email as customer_email')->first();
        $poDetail = json_decode(json_encode($poDetail),true);
        // /echo "<pre>"; print_r($poDetail); die;
        return view('admin.orders.customer-purchase-order-detail')->with(compact('title','poDetail'));
    }

    public function traderOrders(Request $Request){
        Session::put('active','traderOrders'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = PurchaseOrder::with(['adjust_cancel_items','saleinvoices','adjust_items','cancel_items'])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->wherein('purchase_orders.action',['dealer'])->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email')->where('trader_po',1);
            if(!empty($data['id'])){
                $querys = $querys->where('purchase_orders.po_ref_no_string',$data['id']);
            }
            if(!empty($data['dealer_info'])){
                $keyword = $data['dealer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('dealers.business_name', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.owner_mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.email', 'like', '%' . $keyword . '%');
                  });
            }
            if(!empty($data['customer_purchase_order_no'])){
                $querys = $querys->where('purchase_orders.customer_purchase_order_no',$data['customer_purchase_order_no']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('purchase_orders.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $traderpo){ 
                $actionValues='<a target="_blank" title="View Details" class="btn btn-sm green margin-top-10" href="'.url('admin/trader-purchase-order-detail/'.$traderpo['id']).'"> View
                    </a>';
                $dealerInfo = "";
                if(!empty($traderpo['business_name'])){
                    $dealerInfo = ucwords($traderpo['business_name']).'<br>'.$traderpo['dealer_email'].'<br>'.$traderpo['dealer_mobile'];
                    $dealerInfo = ucwords($traderpo['business_name']);
                }
                if($traderpo['po_status'] =="pending"){
                   $traderpo['po_status'] = "Pending Approval"; 
                }elseif($traderpo['po_status'] =="approved"){
                   $traderpo['po_status'] = "Sales Pending"; 
                }elseif($traderpo['po_status'] =="executed"){
                    $traderpo['po_status'] = "Completed"; 
                }
                $records["data"][] = array(      
                    $traderpo['po_ref_no_string'],
                    $dealerInfo,
                    $traderpo['remarks'],
                    ucwords($traderpo['po_status']),
                    date('d M Y',strtotime($traderpo['created_at'])),
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Trader Purchase Orders";
        return View::make('admin.orders.trader-orders')->with(compact('title'));
    }

    public function traderPurchaseOrderDetail($id){
        $title = "Trader Purchase Order Detail";
        $poDetail = PurchaseOrder::with(['orderitems','sale_invoices','adjust_cancel_items','saleinvoices','adjust_items','cancel_items'])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->where('purchase_orders.id',$id)->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','customers.name as customer_name','customers.mobile as customer_mobile','customers.email as customer_email')->first();
        $poDetail = json_decode(json_encode($poDetail),true);
        //echo "<pre>"; print_r($poDetail); die;
        return view('admin.orders.trader-purchase-order-detail')->with(compact('title','poDetail'));
    }

    public function UpdateTraderPoPoQty(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            DB::beginTransaction();
            $subtotal = 0;
            foreach($data['item_ids'] as $ikey=> $itemid){
                $itemDetails = PurchaseOrderItem::find($itemid);
                if($data['actual_qtys'][$ikey]>$itemDetails['qty']){
                    return redirect()->back()->with('flash_message_error','You have entered wrong qty');
                }
            }
            foreach($data['item_ids'] as $ikey=> $itemid){
                $itemDetails = PurchaseOrderItem::find($itemid);
                $itemDetails->actual_qty = $data['actual_qtys'][$ikey];
                $itemDetails->comments = $data['comments'][$ikey];
                $itemDetails->dispatched_qty = 0;
                $itemDetails->save();
                $subtotal +=  $itemDetails->product_price * $data['actual_qtys'][$ikey];
            }
            $updatePo = PurchaseOrder::find($data['purchase_order_id']);
            $updatePo->price = $subtotal;
            $gstVal = (($subtotal * $updatePo->gst_per)/100);
            $updatePo->gst = $gstVal;
            $updatePo->grand_total = $subtotal + $gstVal;
            $updatePo->po_edited = 'yes';
            $updatePo->po_status = 'approved';
            $updatePo->save();
            DB::commit();
            return redirect()->back()->with('flash_message_success','Purchase Order Qty has been updates successfully');
        }
    }

    public function openTraderSaleInvoice(Request $request){
        $data = $request->all();
        $poDetail = PurchaseOrder::with(['orderitems','sale_invoices','adjust_cancel_items','saleinvoices','adjust_items','cancel_items'])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->where('purchase_orders.id',$data['purchase_order_id'])->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','customers.name as customer_name','customers.mobile as customer_mobile','customers.email as customer_email')->first();
        $poDetail = json_decode(json_encode($poDetail),true);
        return response()->json([
            'view' => (String)View::make('admin.orders.trader-sale-invoice-modal')->with(compact('poDetail','data')),
        ]);
    }

    public function createTraderSaleInvoice(Request $request){
        $data = $request->all();
        $poDetail = PurchaseOrder::with(['orderitems'])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->where('purchase_orders.id',$data['purchase_order_id'])->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','customers.name as customer_name','customers.mobile as customer_mobile','customers.email as customer_email')->first();
        $poDetail = json_decode(json_encode($poDetail),true);
        foreach($poDetail['orderitems'] as $orderitemDetails){
            $createSO = new SaleInvoice;
            $createSO->purchase_order_id =  $orderitemDetails['purchase_order_id'];
            $createSO->dealer_id =  $poDetail['dealer_id'];
            $data['gst'] = 18;
            $createSO->gst_per = $data['gst'];
            $createSO->save();
            //Create Sale Invoice Item
            $saleOrderitem = new SaleInvoiceItem;
            $saleOrderitem->sale_invoice_id = $createSO->id;
            $saleOrderitem->purchase_order_item_id = $orderitemDetails['id'];
            $saleOrderitem->product_id = $orderitemDetails['product_id'];
            $saleOrderitem->qty = $orderitemDetails['actual_qty'];
            $saleOrderitem->price = $orderitemDetails['product_price'];
            $saleOrderitem->subtotal = $orderitemDetails['product_price'] * $orderitemDetails['actual_qty'];
            $saleOrderitem->save();
            $totalPrice = $orderitemDetails['product_price'] * $orderitemDetails['actual_qty'];
            $totatSaleAmt = $orderitemDetails['product_price'] * $orderitemDetails['actual_qty'];
            //Update sale invoice 
            $calGST =  ($totatSaleAmt *$data['gst']) /100;
            $totatSaleAmt = $totatSaleAmt + $calGST;
            $updateSO = SaleInvoice::find($createSO->id);
            $updateSO->price =  $totalPrice;
            $updateSO->gst   = $calGST;
            $updateSO->grand_total = $totatSaleAmt;
            $updateSO->save();
        }
        PurchaseOrder::where('id',$data['purchase_order_id'])->update(['po_status'=>'executed']);
        return redirect()->back()->with('flash_message_success','Sale Invoice has been created successfully');
    }

    public function dealerOrders(Request $Request){
        Session::put('active','dealerOrders'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = PurchaseOrder::with(['adjust_cancel_items','saleinvoices','adjust_items','cancel_items','orderitems'=>function($query){
                $query->with('sale_invoice_items');
            }])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->wherein('purchase_orders.action',['dealer'])->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email');
            if(!empty($data['id'])){
                $querys = $querys->where('purchase_orders.po_ref_no_string',$data['id']);
            }
            if(!empty($data['status'])){
                if($data['status'] == "completed"){
                    $querys = $querys->wherein('purchase_orders.po_status',['executed','completed']);
                }else{
                    $querys = $querys->where('purchase_orders.po_status',$data['status']);
                }
            }else{
                /*$data['status'] = "pending";
                $querys = $querys->where('purchase_orders.po_status',$data['status']); */
            }
            if(!empty($data['dealer_info'])){
                $keyword = $data['dealer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('dealers.business_name', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.owner_mobile', 'like', '%' . $keyword . '%')
                       ->orWhere('dealers.email', 'like', '%' . $keyword . '%');
                  });
            }
            if(!empty($data['customer_purchase_order_no'])){
                $querys = $querys->where('purchase_orders.customer_purchase_order_no',$data['customer_purchase_order_no']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('purchase_orders.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $dealerpo){ 
                $actionValues='<a target="_blank" title="View Details" class="btn btn-sm green margin-top-10" href="'.url('admin/dealer-purchase-order-detail/'.$dealerpo['id']).'"> View
                    </a>';
                $dealerInfo = "";
                if(!empty($dealerpo['business_name'])){
                    $dealerInfo = ucwords($dealerpo['business_name']).'<br>'.$dealerpo['dealer_email'].'<br>'.$dealerpo['dealer_mobile'];
                    $dealerInfo = ucwords($dealerpo['business_name']);
                }
                if($dealerpo['po_status'] =="pending"){
                   $dealerpo['po_status'] = "<b style='color:red;'>Pending Approval</b>"; 
                }elseif($dealerpo['po_status'] =="approved"){
                   $dealerpo['po_status'] = "<b>Sales Pending</b>"; 
                }elseif($dealerpo['po_status'] =="executed"){
                    $dealerpo['po_status'] = "<b style='color:green;'>Completed</b>"; 
                }elseif($dealerpo['po_status'] =="completed"){
                    $dealerpo['po_status'] = "<b style='color:green;'>Completed</b>";
                    if(empty($dealerpo['saleinvoices'])){
                        if(!empty($dealerpo['adjust_items'])){
                           $dealerpo['po_status'] = 'Adjusted'; 
                        }else if(!empty($dealerpo['cancel_items'])){
                           $dealerpo['po_status'] = 'Cancelled'; 
                        }
                    }else{
                        if(!empty($dealerpo['adjust_items'])){
                           $dealerpo['po_status'] .= '<br><small> Partially Adjusted</small>'; 
                        }else if(!empty($dealerpo['cancel_items'])){
                           $dealerpo['po_status'] .= '<br><small>Partially Cancelled</small>'; 
                        }
                    }
                }
                if($dealerpo['orderitems']){
                    $products =  '<table class="table table-bordered">
                                    <tr>
                                        <th>Product</th>
                                        <th>OQ</th>
                                        <th>PQ</th>
                                    </tr>';
                    foreach($dealerpo['orderitems'] as $orderitem){
                        /*$item_action = "";
                        if($orderitem['item_action']=="On Hold"){
                            $item_action = '<span class="badge badge-warning">'.$orderitem['item_action'].'</span>';
                        }else if($orderitem['item_action']=="Cancel"){
                            $item_action = '<span class="badge badge-dark">'.$orderitem['item_action'].'</span>';
                        }else if($orderitem['item_action']=="Urgent"){
                            $item_action = '<span class="badge badge-danger">'.$orderitem['item_action'].'</span>';
                        }*/
                        $item_action = "";

                        if (!empty($orderitem['item_action'])) {
                            if ($orderitem['item_action'] == "On Hold") {
                                // If on_hold_until is null => always on hold
                                if (empty($orderitem['on_hold_until'])) {
                                    $item_action = '<span class="badge badge-warning">' . $orderitem['item_action'] . '</span>';
                                }
                                // If future date => show with date
                                elseif (Carbon::parse($orderitem['on_hold_until'])->isFuture()) {
                                    $item_action = '<span class="badge badge-warning">' . $orderitem['item_action'] . '</span>';
                                    $item_action .= ' (' . Carbon::parse($orderitem['on_hold_until'])->format('d M Y') . ')';
                                }
                                // If past date => treat as no action
                                else {
                                    $orderitem['item_action'] = ""; // Allow update buttons to show
                                }
                            } else if ($orderitem['item_action'] == "Cancel") {
                                $item_action = '<span class="badge badge-dark">' . $orderitem['item_action'] . '</span>';
                            } else if ($orderitem['item_action'] == "Urgent") {
                                $item_action = '<span class="badge badge-danger">' . $orderitem['item_action'] . '</span>';
                            }
                        }

                        $sale_invoice_qty = array_sum(array_column($orderitem['sale_invoice_items'],'qty'));
                        if(empty($sale_invoice_qty)){
                            $sale_invoice_qty = 0;
                        }
                        $pending_qty = $orderitem['actual_qty'] - $sale_invoice_qty;
                        if($pending_qty ==0){
                            $item_action = "";
                        }
                        $products .= '<tr>
                                        <td>'.$orderitem['product']['product_name'].$item_action.'</td>
                                        <td>'.$orderitem['actual_qty'].'</td>
                                        <td>'.$pending_qty.'</td>
                                    </tr>';
                    }
                    $products .='</table>';
                }
                $is_tradrer = '';
                if($dealerpo['trader_po'] == 1){
                    $is_tradrer = '<br><b>Trader</b>';
                }
                $records["data"][] = array(      
                    date('d M Y',strtotime($dealerpo['created_at'])),
                    $dealerInfo,
                    $dealerpo['po_ref_no_string'].$is_tradrer,
                    $products,
                    $dealerpo['remarks'],
                    ucwords($dealerpo['po_status']),
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Dealer Orders";
        return View::make('admin.orders.dealer-orders')->with(compact('title'));
    }

    public function dealerPurchaseOrderDetail($id){
        $title = "Dealer Purchase Order Detail";
        $poDetail = PurchaseOrder::with(['orderitems','sale_invoices','adjust_cancel_items','saleinvoices','adjust_items','cancel_items'])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->where('purchase_orders.id',$id)->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','customers.name as customer_name','customers.mobile as customer_mobile','customers.email as customer_email')->first();
        $poDetail = json_decode(json_encode($poDetail),true);
        //echo "<pre>"; print_r($poDetail); die;
        $linkedProducts = \App\DealerLinkedProduct::where('dealer_id',$poDetail['dealer_id'])->pluck('product_id')->toArray();

        // 1. Get product IDs from the current Purchase Order
        $currentProductIds = array_column($poDetail['orderitems'], 'product_id');

        // 2. Get all Purchase Order IDs of the dealer
        $dealerPOIds = \App\PurchaseOrder::where('dealer_id', $poDetail['dealer_id'])
            ->pluck('id')
            ->toArray();

        // 3. Get counts ONLY for products from current Purchase Order
        $productCounts = \App\PurchaseOrderItem::whereIn('purchase_order_id', $dealerPOIds)
            ->whereIn('product_id', $currentProductIds) // Filter only current products
            ->select('product_id', \DB::raw('count(*) as total'))
            ->groupBy('product_id')
            ->pluck('total', 'product_id')
            ->toArray();

        // Test output
        //echo "<pre>"; print_r($productCounts); die;


        return view('admin.orders.dealer-purchase-order-detail')->with(compact('title','poDetail','linkedProducts','productCounts'));
    }

    public function linkDealerProduct(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $dealer_link_pro = new \App\DealerLinkedProduct;
            $dealer_link_pro->product_id = $data['productid'];
            $dealer_link_pro->dealer_id = $data['dealerid'];
            $dealer_link_pro->save();
            return response()->json(['status'=>true]);
        }
    }

    public function markUrgentPoItem(Request $request){
        if($request->ajax()){
            $data = $request->all();
            PurchaseOrderItem::where('id',$data['orderitemid'])->update(['item_action'=>$data['value'],'on_hold_until'=> NULL]);
            return response()->json(['status'=>true]);
        }
    }

    public function markPoItemOnHold(Request $request){
        if ($request->ajax()) {
            PurchaseOrderItem::where('id', $request->orderitemid)
                ->update(['on_hold_until' => $request->on_hold_until]);
            return response()->json(['status' => true]);
        }
        return response()->json(['status' => false]);
    }


    public function UpdateDealerPoStatus(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            PurchaseOrder::where('id',$data['purchase_order_id'])->update(['po_status'=>$data['po_status'],'comments'=>$data['comments'],'reason'=> $data['reason']]);
            return redirect()->back()->with('flash_message_success','Purchase Order Status has been updated successfully');
        }
    }

    public function openPoAdjustModal(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $poDetail = PurchaseOrder::with(['orderitems'])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->where('purchase_orders.id',$data['po_id'])->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','customers.name as customer_name','customers.mobile as customer_mobile','customers.email as customer_email')->first();
            $poDetail = json_decode(json_encode($poDetail),true);
            //echo "<pre>"; print_r($poDetail); die;
            return response()->json([
                'view' => (String)View::make('admin.orders.po-adjust-modal')->with(compact('poDetail','data')),
            ]);
        }
    }

    public function updatePoAdjustment(Request $request){
        $data = $request->all();
        $poDetail = PurchaseOrder::with(['orderitems'])->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->where('purchase_orders.id',$data['purchase_order_id'])->select('purchase_orders.*','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','customers.name as customer_name','customers.mobile as customer_mobile','customers.email as customer_email')->first();
        $poDetail = json_decode(json_encode($poDetail),true);

        foreach($poDetail['orderitems'] as $itemInfo){
            $poitem = PurchaseOrderItem::find($itemInfo['id']);
            $po_item_sale_qty = PurchaseOrderItem::po_item_sale_qty($itemInfo['id']);
            $adjust_qty = $itemInfo['actual_qty'] -$po_item_sale_qty;
            if($adjust_qty >0){
                $poadjust = new PurchaseOrderAdjustment; 
                $poadjust->type = $data['status'];
                $poadjust->purchase_order_id = $data['purchase_order_id'];
                $poadjust->purchase_order_item_id = $itemInfo['id'];
                $poadjust->qty = $adjust_qty;
                $poadjust->adjustment_by = 'admin';
                $poadjust->ref_id    = \Auth::user()->id;
                if($data['reason'] == "Others"){
                    $poadjust->reason    = $data['comments'];
                }else{
                    $poadjust->reason    = $data['reason'];
                }
                $poadjust->save();
                $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$poDetail['dealer_id'],'product_id'=>$poitem->product_id])->first();
                if($dealerProd){
                    $updatedealerProd = DealerProduct::find($dealerProd->id);
                    $updatedealerProd->pending_customer_orders = $dealerProd->pending_customer_orders - $adjust_qty;
                    $updatedealerProd->save();
                }
            }
        }
        PurchaseOrder::where('id',$data['purchase_order_id'])->update(['po_status'=>'completed']);
        return redirect()->back()->with('Request has been recorded successfully');
    }

    public function UpdateDealerPoQty(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            DB::beginTransaction();
            $subtotal = 0;
            foreach($data['item_ids'] as $ikey=> $itemid){
                $itemDetails = PurchaseOrderItem::find($itemid);
                if($data['actual_qtys'][$ikey]>$itemDetails['qty']){
                    return redirect()->back()->with('flash_message_error','You have entered wrong qty');
                }
                if($data['product_links'][$ikey] == 0){
                    if($data['actual_qtys'][$ikey] > 0){
                        return redirect()->back()->with('flash_message_error','You can not accept an order from non linked product');
                    }
                }

            }
            //echo "<pre>"; print_r($data); die;
            foreach($data['item_ids'] as $ikey=> $itemid){
                $itemDetails = PurchaseOrderItem::find($itemid);
                $itemDetails->actual_qty = $data['actual_qtys'][$ikey];
                $itemDetails->comments = $data['comments'][$ikey];
                $itemDetails->dispatched_qty = 0;
                $itemDetails->save();
                $subtotal +=  $itemDetails->product_price * $data['actual_qtys'][$ikey];

                //Create or update dealer Pending orders
                $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$data['dealer_id'],'product_id'=>$itemDetails->product_id])->first();
                if($dealerProd){
                    $dealerProduct =  DealerProduct::find($dealerProd->id);
                    $pendingOrders = $dealerProd->pending_orders;
                }else{
                    $dealerProduct = new DealerProduct;
                    $dealerProduct->dealer_id = $data['dealer_id'];
                    $dealerProduct->product_id = $itemDetails->product_id;
                    $pendingOrders = 0;
                }
                $dealerProduct->pending_orders = $pendingOrders + $data['actual_qtys'][$ikey];
                $dealerProduct->save();
            }
            $updatePo = PurchaseOrder::find($data['purchase_order_id']);
            $updatePo->price = $subtotal;
            $gstVal = (($subtotal * $updatePo->gst_per)/100);
            $updatePo->gst = $gstVal;
            $updatePo->grand_total = $subtotal + $gstVal;
            $updatePo->po_edited = 'yes';
            $updatePo->po_status = 'approved';
            $updatePo->save();
            DB::commit();
            return redirect::to('/admin/dealer-orders')->with('flash_message_success','Purchase Order Qty has been updates successfully');
        }
    }

    public function POdispatchPlanning(Request $Request){
        Session::put('active','POdispatchPlanning'); 
        if($Request->ajax()){

            $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
            $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
            $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
            $conditions = array();
            $data = $Request->input();
            $querys = PurchaseOrderItem::with('purchase_order')->join('products','products.id','=','purchase_order_items.product_id')->join('purchase_orders','purchase_orders.id','=','purchase_order_items.purchase_order_id')->leftjoin('dealers','dealers.id','=','purchase_orders.dealer_id')->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->select('purchase_orders.id','purchase_order_items.id as order_item_id','purchase_orders.created_at','purchase_orders.dealer_id','dealers.business_name','purchase_order_items.purchase_order_id','purchase_order_items.product_id','purchase_order_items.actual_qty','purchase_order_items.dispatched_qty','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','products.product_name','products.product_code','purchase_order_items.is_urgent','purchase_order_items.item_action','purchase_order_items.on_hold_until','customers.name as customer_name')->where('purchase_orders.po_status','approved')->whereColumn('purchase_order_items.actual_qty','!=','purchase_order_items.dispatched_qty')->wherein('purchase_order_items.purchase_order_id',$poids);
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
                    $querys = $querys->where('customers.name','!=','');
                }   
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like', '%' .$data['product_name']. '%');
            }
            if(!empty($data['po_no'])){
                $querys = $querys->where('purchase_orders.po_ref_no_string',$data['po_no']);
            }
            if(!empty($data['urgent'])){
                $querys = $querys->where('purchase_order_items.item_action','Urgent');
            }
            if(!empty($data['date'])){
                $querys = $querys->whereDate('purchase_order_items.created_at',$data['date']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('purchase_order_items.created_at','ASC')
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
                    $userInfo = ucwords($poInfo['customer_name']);
                }
                $item_action = "";
                $actionValues = "";

                if (!empty($poInfo['item_action'])) {
                    if ($poInfo['item_action'] == "On Hold") {
                        // If on_hold_until is null => always on hold
                        if (empty($poInfo['on_hold_until'])) {
                            $item_action = '<span class="badge badge-warning">' . $poInfo['item_action'] . '</span>';
                        }
                        // If future date => show with date
                        elseif (Carbon::parse($poInfo['on_hold_until'])->isFuture()) {
                            $item_action = '<span class="badge badge-warning">' . $poInfo['item_action'] . '</span>';
                            $item_action .= ' (' . Carbon::parse($poInfo['on_hold_until'])->format('d M Y') . ')';
                        }
                        // If past date => treat as no action
                        else {
                            $poInfo['item_action'] = ""; // Allow update buttons to show
                        }
                    } else if ($poInfo['item_action'] == "Cancel") {
                        $item_action = '<span class="badge badge-dark">' . $poInfo['item_action'] . '</span>';
                    } else if ($poInfo['item_action'] == "Urgent") {
                        $item_action = '<span class="badge badge-danger">' . $poInfo['item_action'] . '</span>';
                    }
                }

                // Show update buttons only if status is empty or "Urgent"
                if ($poInfo['item_action'] == "" || $poInfo['item_action'] == "Urgent") {
                    $actionValues = '
                        <a style="display:none;" title="Update Status" class="btn btn-sm green margin-top-10 getProductBatches" data-orderitemid="' . $poInfo['order_item_id'] . '" href="javascript:;">Update</a>
                        <a title="Update Status" class="btn btn-sm green margin-top-10 openDispatchItemModal" data-username="' . $userInfo . '" data-productname="' . $poInfo['product_name'] . '" data-orderitemid="' . $poInfo['order_item_id'] . '" href="javascript:;">Update</a>';
                }

                
                $type = "Customer";
                if(!empty($poInfo['business_name'])){
                    $type = "Dealer";
                }
                $records["data"][] = array( 
                    $poInfo['purchase_order']['po_ref_no_string'].'<br><small>'.
                    date('d M Y',strtotime($poInfo['created_at'])).'<small>',  
                    $type,  
                    $userInfo,
                    $poInfo['product_name'].'<br><small>('.$poInfo['product_code'].')</small>'.$item_action,
                    $poInfo['actual_qty'] - $poInfo['dispatched_qty'].'<br><small>('.$poInfo['actual_qty'].')</small>',
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Purchase Order Dispatch Planning";
        return View::make('admin.orders.po-dispatch-planning')->with(compact('title'));
    }

    public function getProductBatches(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $orderitemDetails = PurchaseOrderItem::details($data['orderitemid']);
            $batchSheets = BatchSheet::where('product_id',$orderitemDetails['product_id'])->where(['status'=>'QC Approved'])->where('remaining_stock','>',0)->get();
            $batchSheets = json_decode(json_encode($batchSheets),true);
            //echo "<pre>"; print_r($batchSheets); die;
            $orderitemid = $data['orderitemid'];
            return response()->json([
                'view' => (String)View::make('admin.orders.partials.product-batch-sheet-modal')->with(compact('batchSheets','orderitemid','data')),
            ]);
        }
    }

    public function undoFinalizeDo($saleInvoiceid,$poitemid){
        $saleInvoiceItem = SaleInvoiceItem::where('sale_invoice_id',$saleInvoiceid)->first();
        $details = PurchaseOrderItem::find($poitemid);
        PurchaseOrderItem::where('id',$poitemid)->decrement('dispatched_qty',$saleInvoiceItem->qty);
        Product::where('id',$details->product_id)->increment('current_stock',$saleInvoiceItem->qty);
        SaleInvoice::where('id',$saleInvoiceid)->delete();
        return redirect::to('/admin/finalize-do')->with('flash_message_success','Updated successfully');
    } 

    public function UpdateProDispatchQty(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $orderitemDetails = PurchaseOrderItem::details($data['order_item_id']);
            $requiredQty = $orderitemDetails['actual_qty'] - $orderitemDetails['dispatched_qty'];
            $stocks = $data['issue_stock'];
            $requestedStock = $data['issue_stock'];
            if(!is_numeric($requestedStock)){
                return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Please enter the issue stock')]);
            }
            if($requestedStock > $requiredQty){
                return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Your requested issue qty must be less then or equal to '.$requiredQty)]);
            }else{
                $poinfo = PurchaseOrder::where('id',$orderitemDetails['purchase_order_id'])->first();
                DB::beginTransaction();
                //Create Sale Invoice
                $createSO = new SaleInvoice;
                //$createSO->sale_invoice_date = date('Y-m-d');
                $createSO->purchase_order_id =  $orderitemDetails['purchase_order_id'];
                $createSO->dealer_id =  $poinfo->dealer_id;
                $createSO->customer_id =  $poinfo->customer_id;
                $data['gst'] = 18;
                $createSO->gst_per = $data['gst'];
                $createSO->save();
                //Create Sale Invoice Item
                $saleOrderitem = new SaleInvoiceItem;
                $saleOrderitem->sale_invoice_id = $createSO->id;
                $saleOrderitem->purchase_order_item_id = $data['order_item_id'];
                $saleOrderitem->batch_no = $data['batch_no'];
                $saleOrderitem->product_id = $orderitemDetails['product_id'];
                $saleOrderitem->qty = $requestedStock;
                $saleOrderitem->price = $orderitemDetails['product_price'];
                $saleOrderitem->subtotal = $orderitemDetails['product_price'] * $requestedStock;
                $saleOrderitem->save();
                $totalPrice = $orderitemDetails['product_price'] * $requestedStock;
                $totatSaleAmt = $orderitemDetails['product_price'] * $requestedStock;
                //Update sale invoice 
                $calGST =  ($totatSaleAmt *$data['gst']) /100;
                $totatSaleAmt = $totatSaleAmt + $calGST;
                $updateSO = SaleInvoice::find($createSO->id);
                $updateSO->price =  $totalPrice;
                $updateSO->gst   = $calGST;
                $updateSO->grand_total = $totatSaleAmt;
                $updateSO->save();
                //Update PO
                PurchaseOrderItem::where('id',$orderitemDetails['id'])->increment('dispatched_qty',$requestedStock);
                //Decrement Stock
                Product::where('id',$orderitemDetails['product_id'])->decrement('current_stock',$requestedStock);
                $getAllPO= PurchaseOrderItem::where('purchase_order_id',$orderitemDetails['purchase_order_id'])->whereColumn('actual_qty','!=','dispatched_qty')->count();
                if($getAllPO == 0){
                    PurchaseOrder::where('id',$orderitemDetails['purchase_order_id'])->update(['po_status'=>'executed']);
                }
                DB::commit();
                return response()->json([
                    'status' =>true,
                    'url'  => url('/admin/po-dispatch-planning?dealer_info='.$data['dealer_info'].'&product_name='.$data['product_name'])
                ]);
            }
        }
    }


    public function UpdateProductDispatchQty(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $orderitemDetails = PurchaseOrderItem::details($data['order_item_id']);
            $requiredQty = $orderitemDetails['actual_qty'] - $orderitemDetails['dispatched_qty'];
            if(isset($data['stocks'])){
                $stocks = array_filter($data['stocks']);
                if(!empty($stocks)){
                    $totalRemaningStock = 0;
                    $requestedStock = array_sum($stocks);
                    foreach($stocks as $key=> $stock){
                        $batchDetails = BatchSheet::where('id',$data['batch_ids'][$key])->first();
                        $batchDetails = json_decode(json_encode($batchDetails),true);
                        if(!empty($batchDetails)){
                            if($stock >$batchDetails['remaining_stock'])
                            {
                                return response()->json(['status'=>false,'errors'=> array($batchDetails['id']=>'Please enter valid issue qty')]);
                            }else{
                                $totalRemaningStock += $batchDetails['remaining_stock']; 
                            }
                        } 
                    }
                    if($requestedStock > $requiredQty){
                        return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Your requested issue qty must be less then or equal to '.$requiredQty)]);
                    }
                }else{
                    return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Please enter valid issue qty')]); 
                }
                /*if($requiredQty > $totalRemaningStock){
                    return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Available Stock not present at the moment')]); 
                }*/
                $poinfo = PurchaseOrder::where('id',$orderitemDetails['purchase_order_id'])->first();
                DB::beginTransaction();
                //Create Sale Invoice
                $createSO = new SaleInvoice;
                //$createSO->sale_invoice_date = date('Y-m-d');
                $createSO->purchase_order_id =  $orderitemDetails['purchase_order_id'];
                $createSO->dealer_id =  $poinfo->dealer_id;
                $data['gst'] = 18;
                $createSO->gst_per = $data['gst'];
                $createSO->save();
                //Create Sale Invoice Item
                $saleOrderitem = new SaleInvoiceItem;
                $saleOrderitem->sale_invoice_id = $createSO->id;
                $saleOrderitem->purchase_order_item_id = $data['order_item_id'];
                $saleOrderitem->product_id = $orderitemDetails['product_id'];
                $saleOrderitem->qty = $requestedStock;
                $saleOrderitem->price = $orderitemDetails['product_price'];
                $saleOrderitem->subtotal = $orderitemDetails['product_price'] * $requestedStock;
                $saleOrderitem->save();
                $totalPrice = $orderitemDetails['product_price'] * $requestedStock;
                $totatSaleAmt = $orderitemDetails['product_price'] * $requestedStock;
                //Update sale invoice 
                $calGST =  ($totatSaleAmt *$data['gst']) /100;
                $totatSaleAmt = $totatSaleAmt + $calGST;
                $updateSO = SaleInvoice::find($createSO->id);
                $updateSO->price =  $totalPrice;
                $updateSO->gst   = $calGST;
                $updateSO->grand_total = $totatSaleAmt;
                $updateSO->save();
                //Update PO
                PurchaseOrderItem::where('id',$orderitemDetails['id'])->increment('dispatched_qty',$requestedStock);
                foreach($stocks as $key=> $stock){
                    $batchid = $data['batch_ids'][$key];
                    BatchSheet::where('id',$batchid)->decrement('remaining_stock',$stock);
                }
                //Decrement Stock
                Product::where('id',$orderitemDetails['product_id'])->decrement('current_stock',$requestedStock);
                $getAllPO= PurchaseOrderItem::where('purchase_order_id',$orderitemDetails['purchase_order_id'])->whereColumn('actual_qty','!=','dispatched_qty')->count();
                if($getAllPO == 0){
                    PurchaseOrder::where('id',$orderitemDetails['purchase_order_id'])->update(['po_status'=>'executed']);
                }
                DB::commit();
                return response()->json([
                    'status' =>true,
                    'url'  => url('/admin/po-dispatch-planning?dealer_info='.$data['dealer_info'].'&product_name='.$data['product_name'])
                ]);
            }else{
                return response()->json(['status'=>false,'errors'=> array('total_stock_error'=>'Batches not avaialable to create DO')]); 
            }
        }
    } 


    public function finalizeDo(Request $request){
        Session::put('active','finalizeDo'); 
        $title = "Finalize D.O.";
        $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
        $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();

        $dealers = SaleInvoice::wherein('sale_invoices.purchase_order_id',$getDealerPoids)->join('dealers','dealers.id','=','sale_invoices.dealer_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->select('sale_invoices.dealer_id','purchase_orders.action','dealers.business_name as name')->where('sale_invoices.do_number','')->where('sale_invoices.dealer_invoice_no','')->groupby('sale_invoices.dealer_id')->get()->toArray();
        $customers = SaleInvoice::wherein('sale_invoices.purchase_order_id',$getDirectCustomerPoids)->join('customers','customers.id','=','sale_invoices.customer_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->select('sale_invoices.customer_id','purchase_orders.action','customers.name')->where('sale_invoices.do_number','')->where('sale_invoices.dealer_invoice_no','')->groupby('sale_invoices.customer_id')->get()->toArray();
        $users = array_merge($dealers,$customers);
        $keys = array_column($users, 'name');
        array_multisort($keys, SORT_ASC, $users);
        //echo "<pre>"; print_r($users); die;
        return view('admin.orders.order_samplings.finalize_do')->with(compact('title','users'));
    }

    public function DoReady(Request $request){
        Session::put('active','DoReady'); 
        $title = "DO Ready";
        $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
        $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
        $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
        $users = SaleInvoice::wherein('sale_invoices.purchase_order_id',$poids)->leftjoin('dealers','dealers.id','=','sale_invoices.dealer_id')->leftjoin('customers','customers.id','=','sale_invoices.customer_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->select('sale_invoices.dealer_id','sale_invoices.customer_id','purchase_orders.action','sale_invoices.do_number','sale_invoices.do_ref_no','sale_invoices.do_date',DB::RAW("CONCAT(COALESCE(dealers.business_name,''),COALESCE(customers.name,'')) AS name"))->where('sale_invoices.dealer_invoice_no','')->where('sale_invoices.do_number','!=','')->where('sale_invoices.do_ref_no','!=','')->groupby('sale_invoices.do_number')->orderby('do_ref_no','ASC')->get()->toArray();
        //echo "<pre>"; print_r($users); die;
        return view('admin.orders.order_samplings.do_ready')->with(compact('title','users'));
    }

    public function BillReady(Request $Request){
        Session::put('active','BillReady');
        $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
        $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
        $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
        $users = SaleInvoice::wherein('sale_invoices.purchase_order_id',$poids)->leftjoin('dealers','dealers.id','=','sale_invoices.dealer_id')->leftjoin('customers','customers.id','=','sale_invoices.customer_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->select('sale_invoices.dealer_id','sale_invoices.customer_id','purchase_orders.action','sale_invoices.do_number','sale_invoices.do_ref_no','sale_invoices.dealer_invoice_no','sale_invoices.sale_invoice_date',DB::RAW("CONCAT(COALESCE(dealers.business_name,''),COALESCE(customers.name,'')) AS name"))->where('sale_invoices.dealer_invoice_no','!=','')->where('sale_invoices.transport_name','')->groupby('sale_invoices.dealer_invoice_no')->orderby('sale_invoice_date','ASC')->get()->toArray();
        $title = "Bill Ready";
        //echo "<pre>"; print_r($users); die;
        return view('admin.orders.order_samplings.bill_ready')->with(compact('title','users'));
    }

    public function finalizeDo_OLD(Request $Request){
        Session::put('active','finalizeDo'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
            $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
            $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
            $querys = SaleInvoice::join('sale_invoice_items','sale_invoice_items.sale_invoice_id','=','sale_invoices.id')->join('purchase_order_items','purchase_order_items.id','=','sale_invoice_items.purchase_order_item_id')->join('products','products.id','=','sale_invoice_items.product_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->leftjoin('dealers','dealers.id','=','sale_invoices.dealer_id')->leftjoin('customers','customers.id','=','sale_invoices.customer_id')->wherein('purchase_orders.id',$poids)->select('sale_invoice_items.id as item_id','sale_invoice_items.qty','sale_invoice_items.batch_no','sale_invoices.id as sale_invoice_id','purchase_orders.po_ref_no_string','purchase_orders.created_at','sale_invoices.created_at as sale_invoice_date','purchase_orders.dealer_id','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','products.product_name','products.product_code','sale_invoices.purchase_order_id','purchase_order_items.dealer_price','customers.name as customer_name','purchase_order_items.id as po_item_id')->where('sale_invoices.do_number','')->where('sale_invoices.dealer_invoice_no','');
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
                    $querys = $querys->where('customers.name','!=','');
                }   
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like', '%'.$data['product_name'].'%');
            }
            if(!empty($data['date'])){
                $querys = $querys->whereDate('sale_invoices.created_at',$data['date']);
            }
            if(!empty($data['po_date'])){
                $querys = $querys->whereDate('purchase_orders.created_at',$data['po_date']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('sale_invoices.created_at','ASC')
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
                    $userInfo = ucwords($poInfo['customer_name']);
                }
                $actionValues = '<a class="btn btn-xs btn-danger" href="'.url('admin/undo-finalize-do/'.$poInfo['sale_invoice_id'].'/'.$poInfo['po_item_id']).'"><i class="fa fa-times"></i></a>';
                $do_checkbox = '<input type="checkbox" data-saleinvoice="'.$poInfo['sale_invoice_id'].'" class="getDoReady" value="'.$poInfo['sale_invoice_id'].'">';
                $type = "Customer";
                if(!empty($poInfo['business_name'])){
                    $type = "Dealer";
                }
                $records["data"][] = array(  
                    /*$poInfo['po_ref_no_string'].'<br><small>('.date('d M Y',strtotime($poInfo['created_at'])).')</small>', */
                    $type,  
                    $userInfo,
                    $poInfo['product_name'].'<br><small>('.$poInfo['product_code'].')</small>',
                    //$poInfo['dealer_price'],
                    $poInfo['qty'],
                    //$poInfo['batch_no'],
                    $do_checkbox.'<br>'.$actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Finalize DO";
        return View::make('admin.orders.finalize-do')->with(compact('title'));
    }


    public function DoReady_old(Request $Request){
        Session::put('active','DoReady'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
            $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
            $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
            $querys = SaleInvoice::join('sale_invoice_items','sale_invoice_items.sale_invoice_id','=','sale_invoices.id')->join('purchase_order_items','purchase_order_items.id','=','sale_invoice_items.purchase_order_item_id')->join('products','products.id','=','sale_invoice_items.product_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->leftjoin('dealers','dealers.id','=','sale_invoices.dealer_id')->leftjoin('customers','customers.id','=','sale_invoices.customer_id')->wherein('purchase_orders.id',$poids)->select('sale_invoice_items.id as item_id','sale_invoice_items.qty','sale_invoice_items.batch_no','sale_invoices.id as sale_invoice_id','purchase_orders.po_ref_no_string','purchase_orders.created_at','sale_invoices.created_at as sale_invoice_date','purchase_orders.dealer_id','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','products.product_name','products.product_code','sale_invoices.purchase_order_id','purchase_order_items.net_price','sale_invoices.do_number','sale_invoices.do_date','sale_invoices.do_ref_no','customers.name as customer_name')->where('sale_invoices.dealer_invoice_no','')->where('sale_invoices.do_number','!=','');
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
                    $querys = $querys->where('customers.name','!=','');
                }   
            }
            if(!empty($data['po_no'])){
                $querys = $querys->where('purchase_orders.po_ref_no_string',$data['po_no']);
            }
            if(!empty($data['do_no'])){
                $querys = $querys->where('sale_invoices.do_ref_no',$data['do_no']);
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like', '%'.$data['product_name'].'%');
            }
            if(!empty($data['date'])){
                $querys = $querys->whereDate('sale_invoices.created_at',$data['date']);
            }
            if(!empty($data['po_date'])){
                $querys = $querys->whereDate('purchase_orders.created_at',$data['po_date']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('sale_invoices.created_at','ASC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $poInfo){ 
                $actionValues='<a title="Update Status" data-saleinvoiceid="'.$poInfo['sale_invoice_id'].'" class="btn btn-sm green margin-top-10 UpdateSaleiInvoice" href="javascript:;"> Update</a>';
                $userInfo = "";
                if(!empty($poInfo['business_name'])){
                    $userInfo = ucwords($poInfo['business_name']);
                }else{
                    $userInfo = ucwords($poInfo['customer_name']);
                }
                $type = "Customer";
                if(!empty($poInfo['business_name'])){
                    $type = "Dealer";
                }
                $do_checkbox = '<input type="checkbox" data-saleinvoice="'.$poInfo['sale_invoice_id'].'" class="getDoReady" value="'.$poInfo['sale_invoice_id'].'">';
                $records["data"][] = array(  
                    $poInfo['po_ref_no_string'].'<br><small>('.date('d M Y',strtotime($poInfo['created_at'])).')</small>',  
                    $poInfo['do_ref_no'].'<br><small>('.date('d M Y',strtotime($poInfo['do_date'])).')</small>', 
                    $type,
                    $userInfo,
                    $poInfo['product_name'].'<br><small>('.$poInfo['product_code'].')</small>',
                    $poInfo['net_price'],
                    $poInfo['qty'],
                    $poInfo['batch_no'],
                    $do_checkbox
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "DO Ready/ Invoice Required";
        return View::make('admin.orders.do-ready')->with(compact('title'));
    }

    public function updateSaleInvoice(Request $request){
        $data = $request->all();
        $saleInvoice = SaleInvoice::find($data['sale_invoice_id']);
        $saleInvoice->sale_invoice_date = $data['sale_invoice_date'];
        $saleInvoice->dealer_invoice_no = $data['invoice_number'];
        $saleInvoice->save();
        return redirect::to('/admin/do-ready?dealer_info='.$data['dealer_name'].'&product_name='.$data['pro_name'])->with('flash_message_success','Sale invoice has been updated successfully');
    }

    public function updateBulkSaleInvoice(Request $request){
        $data = $request->all();
        $invoice_no_details = SaleInvoice::where('dealer_invoice_no',$data['invoice_number'])->first();
        if(is_object($invoice_no_details)){
            $invoice_no_details = json_decode(json_encode($invoice_no_details),true);
            if(!empty($data['dealer_id']) &&  !empty($invoice_no_details['dealer_id'])){
                if($invoice_no_details['dealer_id'] != $data['dealer_id']){
                    return redirect::to('admin/do-ready')->with('flash_message_error','This Invoice no has already been used.'); 
                }
            }elseif(!empty($data['customer_id']) &&  !empty($lr_no_detials['customer_id'])){
                if($lr_no_detials['customer_id'] != $data['customer_id']){
                    return redirect::to('admin/do-ready')->with('flash_message_error','This Invoice no has already been used.'); 
                }
            }
        }
        $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
        $dealerSaleInvoices = SaleInvoice::wherein('id',$saleInvoiceIds)->whereNotNull('dealer_id')->pluck('dealer_id','id')->toArray();

        $customerSaleInvoices = SaleInvoice::wherein('id',$saleInvoiceIds)->whereNotNull('customer_id')->pluck('customer_id','id')->toArray();
        if(!empty($customerSaleInvoices) && $dealerSaleInvoices){
            return redirect::to('admin/do-ready')->with('flash_message_error','You have selected multiple user (dealer and customer). Please update invoice number of either customer or dealer'); 
        }

        if(!empty($dealerSaleInvoices) && empty($customerSaleInvoices)){
            $saleInvoices = $dealerSaleInvoices;
        }else if(!empty($customerSaleInvoices) && empty($dealerSaleInvoices)){
            $saleInvoices = $customerSaleInvoices;
        }
        $userids = array_unique($saleInvoices);
        if(count($userids) >1){
           return redirect::to('admin/do-ready')->with('flash_message_error','You have selected multiple user. Please update invoice number of one user at once'); 
        }else{
            DB::beginTransaction();
            foreach($saleInvoices as $invoiceid=> $user){
                $saleInvoice = SaleInvoice::find($invoiceid);
                $saleInvoice->sale_invoice_date = $data['sale_invoice_date'];
                $saleInvoice->dealer_invoice_no = $data['invoice_number'];
                $saleInvoice->save();
            }
            DB::commit();
            return redirect::to('admin/do-ready')->with('flash_message_success','Invoice has been updated successfully'); 
        }
    }

    public function generateDoNumbers(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $types = array('dealer_id','customer_id');
            foreach($types as $type){
                $doNumber = 0;
                $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
                $saleInvoices = SaleInvoice::wherein('id',$saleInvoiceIds)->whereNotNull($type)->pluck($type,'id')->toArray();
                if(!empty($saleInvoices)){
                    $ids = array_unique($saleInvoices);
                    $lastDoNumber = SaleInvoice::orderby('do_number','DESC')->where('do_number','>',0)->where('do_financial_year',financialYear())->first();
                    if(is_object($lastDoNumber)){
                        $doNumber = $lastDoNumber->do_number +1;
                    }else{
                        $doNumber = 1;
                    }
                    $doNumbers = array();
                    foreach($ids as $id){
                        $doNumbers[$id]  = $doNumber;
                        ++$doNumber;
                    }
                    foreach($saleInvoices as $invoiceid=> $id){
                        $saleInvoice = SaleInvoice::find($invoiceid);
                        $saleInvoice->do_number = $doNumbers[$id];
                        $saleInvoice->do_ref_no = $doNumbers[$id].'/'.financialYear();
                        $saleInvoice->do_financial_year = financialYear();
                        $saleInvoice->do_date = date('Y-m-d H:i:s');
                        $saleInvoice->save();
                    }
                }
            }
            Session::flash('flash_message_success','DO number has been generated successfully');
            return response()->json([
                'status' =>true,
                'url'    =>  url('admin/finalize-do')
            ]);
        }
    }

    public function BillReady_old(Request $Request){
        Session::put('active','BillReady'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
            $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
            $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
            $querys = SaleInvoice::join('sale_invoice_items','sale_invoice_items.sale_invoice_id','=','sale_invoices.id')->join('products','products.id','=','sale_invoice_items.product_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->leftjoin('dealers','dealers.id','=','sale_invoices.dealer_id')->leftjoin('customers','customers.id','=','sale_invoices.customer_id')->wherein('purchase_orders.id',$poids)->select('sale_invoice_items.qty','sale_invoices.id as sale_invoice_id','purchase_orders.created_at','sale_invoices.sale_invoice_date','sale_invoices.dealer_invoice_no','sale_invoices.created_at as sale_invoice_created_at','purchase_orders.dealer_id','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','products.product_name','products.product_code','sale_invoices.purchase_order_id','customers.name as customer_name')->where('sale_invoices.dealer_invoice_no','!=','')->where('sale_invoices.transport_name','');
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
                    $querys = $querys->where('customers.name','!=','');
                }   
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like', '%' . $data['product_name'] . '%');
            }
            if(!empty($data['invoice_info'])){
                $querys = $querys->where('sale_invoices.dealer_invoice_no',$data['invoice_info']);
            }
            if(!empty($data['invoice_date'])){
                $querys = $querys->whereDate('sale_invoices.sale_invoice_date',$data['invoice_date']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('sale_invoices.sale_invoice_date','ASC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $poInfo){ 
                $actionValues='<a style="display:none;" title="Update Status" data-saleinvoiceid="'.$poInfo['sale_invoice_id'].'" class="btn btn-sm green margin-top-10 updateTransportDetails" href="javascript:;"> Update</a>';
                $type = "Customer";
                if(!empty($poInfo['business_name'])){
                    $type = "Dealer";
                }
                $userInfo = "";
                if(!empty($poInfo['business_name'])){
                    $userInfo = ucwords($poInfo['business_name']);
                }else{
                    $userInfo = ucwords($poInfo['customer_name']);
                }
                $bill_checkbox = '<input type="checkbox" data-saleinvoice="'.$poInfo['sale_invoice_id'].'" class="getBillReady" value="'.$poInfo['sale_invoice_id'].'">';
                $records["data"][] = array(  
                    date('d M Y',strtotime($poInfo['sale_invoice_date'])),    
                    $poInfo['dealer_invoice_no'], 
                    $type,   
                    $userInfo,
                    $poInfo['product_name'].'<br><small>('.$poInfo['product_code'].')</small>',
                    $poInfo['qty'],   
                    $bill_checkbox.$actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Bill Ready, Despatch Required";
        return View::make('admin.orders.bill-ready')->with(compact('title'));
    }

    public function updateTransportDetails(Request $request){
        $data = $request->all();
        $saleInvoice = SaleInvoice::with('item')->find($data['sale_invoice_id']);
        DB::beginTransaction();
        //echo "<pre>"; print_r(json_decode(json_encode($saleInvoice),true)); die;
        $saleInvoice->transport_name = $data['transport_name'];
        $saleInvoice->lr_no = $data['lr_no'];
        $saleInvoice->dispatch_date = $data['dispatch_date'];
        $saleInvoice->save();
        $poinfo = PurchaseOrder::where('id',$saleInvoice->purchase_order_id)->first();
        if(!empty($poinfo->dealer_id)){
            if($poinfo->trader_po == 0){
                $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$saleInvoice->dealer_id,'product_id'=>$saleInvoice->item->product_id])->first();
                $updatedealerProd = DealerProduct::find($dealerProd->id);
                $updatedealerProd->in_transit = $saleInvoice->item->qty;
                $updatedealerProd->pending_orders = $dealerProd->pending_orders - $saleInvoice->item->qty;
                $updatedealerProd->save();
            }
        }else{
            $dealerProd = DB::table('customer_products')->where(['customer_id'=>$saleInvoice->customer_id,'product_id'=>$saleInvoice->item->product_id])->first();
                $updateCustProd = CustomerProduct::find($dealerProd->id);
                $updateCustProd->in_transit = $saleInvoice->item->qty;
                $updateCustProd->pending_orders = $dealerProd->pending_orders - $saleInvoice->item->qty;
                $updateCustProd->save();
        }
        DB::commit();
        return redirect::to('/admin/bill-ready?dealer_info='.$data['dealer_name'].'&product_name='.$data['pro_name'].'&invoice_info='.$data['sale_invoice_no'])->with('flash_message_success','Sale invoice has been updated successfully');
    }

    public function updateBulkTransportDetails(Request $request){
        $data = $request->all();
        $lr_no_detials = SaleInvoice::where('lr_no',$data['lr_no'])->first();
        if(is_object($lr_no_detials)){
            $lr_no_detials = json_decode(json_encode($lr_no_detials),true);
            if(!empty($data['dealer_id']) &&  !empty($lr_no_detials['dealer_id'])){
                if($lr_no_detials['dealer_id'] != $data['dealer_id']){
                    return redirect::to('admin/bill-ready')->with('flash_message_error','This LR no has already been used.'); 
                }
            }elseif(!empty($data['customer_id']) &&  !empty($lr_no_detials['customer_id'])){
                if($lr_no_detials['customer_id'] != $data['customer_id']){
                    return redirect::to('admin/bill-ready')->with('flash_message_error','This LR no has already been used.'); 
                }
            }
        }
        $saleInvoiceIds = explode(',',$data['sale_invoice_ids']);
        $saleInvoices = SaleInvoice::wherein('id',$saleInvoiceIds)->pluck('dealer_invoice_no','id')->toArray();
        $invoiceNos = array_unique($saleInvoices);
        if(count($invoiceNos) >1){
            return redirect::to('admin/bill-ready')->with('flash_message_error','You have selected different invoice numbers. Please update on e invoice number  at once'); 
        }
        foreach($saleInvoices as $sale_invoice_id => $invoice){
            $saleInvoice = SaleInvoice::with('item')->find($sale_invoice_id);
            //echo "<pre>"; print_r(json_decode(json_encode($saleInvoice),true)); die;
            $saleInvoice->transport_name = $data['transport_name'];
            $saleInvoice->lr_no = $data['lr_no'];
            $saleInvoice->dispatch_date = $data['dispatch_date'];
            $saleInvoice->save();
            $poinfo = PurchaseOrder::where('id',$saleInvoice->purchase_order_id)->first();
            if(!empty($poinfo->dealer_id)){
                if($poinfo->trader_po == 0){
                    $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$saleInvoice->dealer_id,'product_id'=>$saleInvoice->item->product_id])->first();
                    $updatedealerProd = DealerProduct::find($dealerProd->id);
                    $updatedealerProd->in_transit = $saleInvoice->item->qty;
                    $updatedealerProd->pending_orders = $dealerProd->pending_orders - $saleInvoice->item->qty;
                    $updatedealerProd->save();
                }
            }else{
                $dealerProd = DB::table('customer_products')->where(['customer_id'=>$saleInvoice->customer_id,'product_id'=>$saleInvoice->item->product_id])->first();
                    $updateCustProd = CustomerProduct::find($dealerProd->id);
                    $updateCustProd->in_transit = $saleInvoice->item->qty;
                    $updateCustProd->pending_orders = $dealerProd->pending_orders - $saleInvoice->item->qty;
                    $updateCustProd->save();
            }
        }
        return redirect::to('/admin/bill-ready')->with('flash_message_success','Sale invoice has been updated successfully');
    }


    public function dispatchedMaterial(Request $Request){
        $data = $Request->all(); 
        Session::put('active','dispatchedMaterial');
        $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
        $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
        $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
        $users = SaleInvoice::wherein('sale_invoices.purchase_order_id',$poids)->leftjoin('dealers','dealers.id','=','sale_invoices.dealer_id')->leftjoin('customers','customers.id','=','sale_invoices.customer_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->join('sale_invoice_items','sale_invoice_items.sale_invoice_id','=','sale_invoices.id')->select('sale_invoices.dealer_id','sale_invoices.customer_id','purchase_orders.action','sale_invoices.do_number','sale_invoices.do_ref_no','sale_invoices.dealer_invoice_no','sale_invoices.sale_invoice_date','sale_invoices.lr_no','sale_invoices.dispatch_date','sale_invoices.transport_name','sale_invoice_items.product_id','sale_invoice_items.batch_no',DB::RAW("CONCAT(COALESCE(dealers.business_name,''),COALESCE(customers.name,'')) AS name"))->where('sale_invoices.lr_no','!=','')->groupby('sale_invoices.dealer_invoice_no')->orderby('sale_invoice_date','DESC');
        if(isset($data['product_id']) && !empty($data['product_id'])){
                $users = $users->where('sale_invoice_items.product_id',$data['product_id']);
            }
            if(isset($data['batch_no'])&& !empty($data['batch_no'])){
                $users = $users->where('sale_invoice_items.batch_no',$data['batch_no']);
            }
            if(isset($data['name']) && !empty($data['name'])){
                $users = $users->where(function($query)use($data){
                    $query->where('customers.name','like', '%' .$data['name']. '%')->where('sale_invoices.dispatch_date','!=','0000-00-00');
                })->orwhere(function($query)use($data){
                    $query->where('dealers.business_name','like', '%' .$data['name']. '%')->where('sale_invoices.dispatch_date','!=','0000-00-00');
                });
            }
        $users = $users->simplePaginate(40);
        $title = "Dispatched Material";
        //echo "<pre>"; print_r(json_decode(json_encode($users),true)); die;
        return view('admin.orders.order_samplings.dispatched_material')->with(compact('title','users','data'));
    }


    public function dispatchedMaterial_OLD(Request $Request){
        Session::put('active','dispatchedMaterial'); 
        if($Request->ajax()){
            $conditions = array();
            $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
            $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
            $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
            $data = $Request->input();
            $querys = SaleInvoice::join('sale_invoice_items','sale_invoice_items.sale_invoice_id','=','sale_invoices.id')->join('products','products.id','=','sale_invoice_items.product_id')->join('purchase_orders','purchase_orders.id','=','sale_invoices.purchase_order_id')->leftjoin('dealers','dealers.id','=','sale_invoices.dealer_id')->leftjoin('customers','customers.id','=','sale_invoices.customer_id')->wherein('purchase_orders.id',$poids)->select('purchase_orders.po_ref_no_string','sale_invoice_items.qty','sale_invoice_items.batch_no','sale_invoice_items.price as dealer_price','sale_invoices.id as sale_invoice_id','purchase_orders.created_at','sale_invoices.sale_invoice_date','sale_invoices.dealer_invoice_no','sale_invoices.created_at as sale_invoice_created_at','purchase_orders.dealer_id','dealers.business_name','dealers.owner_mobile as dealer_mobile','dealers.email as dealer_email','products.product_name','products.product_code','sale_invoices.purchase_order_id','sale_invoices.lr_no','sale_invoices.dispatch_date','customers.name as customer_name')->where('sale_invoices.lr_no','!=','');
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
                    $querys = $querys->where('customers.name','!=','');
                }   
            }
            if(!empty($data['product_name'])){
                $querys = $querys->where('products.product_name','like', '%' . $data['product_name'] . '%');
            }
            if(!empty($data['po_info'])){
                $querys = $querys->where('purchase_orders.po_ref_no_string',$data['po_info']);
            }
            if(!empty($data['invoice_info'])){
                $querys = $querys->where('sale_invoices.dealer_invoice_no',$data['invoice_info']);
            }
            if(!empty($data['lr_info'])){
                $querys = $querys->where('sale_invoices.lr_no',$data['lr_info']);
            }
            if(!empty($data['batch_info'])){
                $querys = $querys->where('sale_invoice_items.batch_no',$data['batch_info']);
            }
            if(!empty($data['invoice_date'])){
                $querys = $querys->whereDate('sale_invoices.sale_invoice_date',$data['invoice_date']);
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('sale_invoices.dispatch_date','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $poInfo){ 
                $actionValues='';
                $userInfo = "";
                if(!empty($poInfo['business_name'])){
                    $userInfo = ucwords($poInfo['business_name']);
                }else{
                    $userInfo = ucwords($poInfo['customer_name']);
                }
                $type = "Customer";
                if(!empty($poInfo['business_name'])){
                    $type = "Dealer";
                }
                $records["data"][] = array(      
                    $poInfo['dealer_invoice_no'].'<br>'.date('d M Y',strtotime($poInfo['sale_invoice_date'])), 
                    $type,   
                    $userInfo,
                    $poInfo['po_ref_no_string'],
                    $poInfo['product_name'].'<br><small>('.$poInfo['product_code'].')</small>',
                    $poInfo['batch_no'],
                    $poInfo['qty'], 
                    $poInfo['dealer_price'], 
                    $poInfo['lr_no'].'<br>'.date('d M Y',strtotime($poInfo['dispatch_date'])),    
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Dispatched Material";
        return View::make('admin.orders.dispatched-material')->with(compact('title'));
    }

    public function fetchProductQtyDiscounts(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $qtyDiscounts = \App\QtyDiscount::get_discounts($data['productId']);
            return response()->json([
                'view' => (String)View::make('admin.orders.partials.product-qty-discounts')->with(compact('qtyDiscounts')),
            ]);
        }
    }
}
