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
use App\DealerProduct;
use App\CustomerProduct;
use Validator;
class DirectCustomerController extends Controller
{
    //
    public function directCustomerOrders(Request $Request){
        Session::put('active','directCustomerOrders'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = PurchaseOrder::with(['adjust_cancel_items','saleinvoices','adjust_items','cancel_items','orderitems'=>function($query){
                $query->with('sale_invoice_items');
            }])->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->wherein('purchase_orders.action',['customer'])->select('purchase_orders.*','customers.name as business_name','customers.mobile','customers.email')->whereNull('purchase_orders.dealer_id');
            if(!empty($data['id'])){
                $querys = $querys->where('purchase_orders.po_ref_no_string',$data['id']);
            }
            if(!empty($data['customer_info'])){
                $keyword = $data['customer_info'];
                $querys = $querys->where(function ($query) use($keyword) {
                    $query->where('customers.name', 'like', '%' . $keyword . '%');
                });
            }
            if(!empty($data['status'])){
                if($data['status'] == "completed"){
                    $querys = $querys->wherein('purchase_orders.po_status',['executed','completed']);
                }else{
                    $querys = $querys->where('purchase_orders.po_status',$data['status']);
                }
            }else{
                /*$data['status'] = "pending";
                $querys = $querys->where('purchase_orders.po_status',$data['status']);*/ 
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
                $actionValues='<a target="_blank" title="View Details" class="btn btn-sm green margin-top-10" href="'.url('admin/direct-customer-purchase-order-detail/'.$customerpo['id']).'"> View
                    </a>';
                $custInfo = "";
                if(!empty($customerpo['business_name'])){
                    $custInfo = ucwords($customerpo['business_name']).'<br>'.$customerpo['email'].'<br>'.$customerpo['mobile'];
                    $custInfo = ucwords($customerpo['business_name']);
                }
                if($customerpo['po_status'] =="pending"){
                   $customerpo['po_status'] = "<b style='color:red;'>Pending Approval</b>"; 
                }elseif($customerpo['po_status'] =="approved"){
                   $customerpo['po_status'] = "<b >Sales Pending</b>"; 
                }elseif($customerpo['po_status'] =="executed"){
                    $customerpo['po_status'] = "<b style='color:green;'>Completed</b>"; 
                }elseif($customerpo['po_status'] =="completed"){
                    $customerpo['po_status'] = "<b style='color:green;'>Completed</b>";
                    if(empty($customerpo['saleinvoices'])){
                        if(!empty($customerpo['adjust_items'])){
                           $customerpo['po_status'] = 'Adjusted'; 
                        }else if(!empty($customerpo['cancel_items'])){
                           $customerpo['po_status'] = 'Cancelled'; 
                        }
                    }else{
                        if(!empty($customerpo['adjust_items'])){
                           $customerpo['po_status'] .= '<br><small> Partially Adjusted</small>'; 
                        }else if(!empty($customerpo['cancel_items'])){
                           $customerpo['po_status'] .= '<br><small>Partially Cancelled</small>'; 
                        }
                    }
                }
                if($customerpo['orderitems']){
                    $products =  '<table class="table table-bordered">
                                    <tr>
                                        <th>Product</th>
                                        <th>OQ</th>
                                        <th>PQ</th>
                                    </tr>';
                    foreach($customerpo['orderitems'] as $orderitem){
                        $item_action = "";
                        if($orderitem['item_action']=="On Hold"){
                            $item_action = '<span class="badge badge-warning">'.$orderitem['item_action'].'</span>';
                        }else if($orderitem['item_action']=="Cancel"){
                            $item_action = '<span class="badge badge-dark">'.$orderitem['item_action'].'</span>';
                        }else if($orderitem['item_action']=="Urgent"){
                            $item_action = '<span class="badge badge-danger">'.$orderitem['item_action'].'</span>';
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
                $records["data"][] = array(      
                    date('d M Y',strtotime($customerpo['created_at'])),
                    $custInfo,
                    $customerpo['po_ref_no_string'].'<br><small>'.$customerpo['customer_purchase_order_no'].'</small>',
                    $products,
                    $customerpo['remarks'],
                    ucwords($customerpo['po_status']),
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Direct Customer Orders";
        return View::make('admin.orders.direct-customer-orders')->with(compact('title'));
    }

    public function directCustomerPurchaseOrderDetail($id){
        $title = "Direct Customer Purchase Order Detail";
        $poDetail = PurchaseOrder::with(['orderitems','sale_invoices','adjust_cancel_items','saleinvoices','adjust_items','cancel_items'])->leftjoin('customers','customers.id','=','purchase_orders.customer_id')->where('purchase_orders.id',$id)->select('purchase_orders.*','customers.name','customers.mobile','customers.email')->first();
        $poDetail = json_decode(json_encode($poDetail),true);
        return view('admin.orders.direct-customer-purchase-order-detail')->with(compact('title','poDetail'));
    }

    public function UpdateDirectCustomerPoQty(Request $request){
        if($request->isMethod('post')){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            DB::beginTransaction();
            $subtotal = 0;
            //echo "<pre>"; print_r($data); die;
            foreach($data['item_ids'] as $ikey=> $itemid){
                $itemDetails = PurchaseOrderItem::find($itemid);
                $itemDetails->actual_qty = $data['actual_qtys'][$ikey];
                $itemDetails->comments = $data['comments'][$ikey];
                $itemDetails->dispatched_qty = 0;
                $itemDetails->save();
                $subtotal +=  $itemDetails->net_price * $data['actual_qtys'][$ikey];

                //Create or update dealer Pending orders
                $dealerProd = DB::table('customer_products')->where(['customer_id'=>$data['customer_id'],'product_id'=>$itemDetails->product_id])->first();
                if($dealerProd){
                    $custProduct =  CustomerProduct::find($dealerProd->id);
                    $pendingOrders = $dealerProd->pending_orders;
                }else{
                    $custProduct = new CustomerProduct;
                    $custProduct->customer_id = $data['customer_id'];
                    $custProduct->product_id = $itemDetails->product_id;
                    $pendingOrders = 0;
                }
                $custProduct->pending_orders = $pendingOrders + $data['actual_qtys'][$ikey];
                $custProduct->save();
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
            return redirect::to('/admin/direct-customer-orders')->with('flash_message_success','Purchase Order Qty has been updates successfully');
        }
    }
}
