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
use App\RawMaterial;
use App\Product;
use App\PackingType;
use App\ProductRawMaterial;
use App\RawMaterialChecklist;
use App\PackingSize;
use App\ProductChecklist;
use App\ProductStage;
use App\ProductPricing;
use Validator;
use App\SaleInvoice;
use App\PurchaseOrderItem;
use App\PurchaseOrder;
class PlanController extends Controller
{
    //
    public function pendingorder(Request $Request){
        Session::put('active','pendingorder'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $getPOids = PurchaseOrder::where('action','dealer')->whereNotin('po_status',['rejected','pending'])->whereNull('customer_id')->pluck('id')->toArray();
    		$items = PurchaseOrderItem::wherein('purchase_order_id',$getPOids)->pluck('product_id')->toArray();
            $querys = Product::wherein('id',$items);
            if(!empty($data['product_name'])){
                $querys = $querys->where('product_name','like','%'.$data['product_name'].'%');
            }
            if(!empty($data['product_code'])){
                $querys = $querys->where('product_code','like','%'.$data['product_code'].'%');
            }
            if(!empty($data['hsn_code'])){
                $querys = $querys->where('hsn_code','like','%'.$data['hsn_code'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('products.product_name','ASC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $product){ 
                $orderQty = PurchaseOrderItem::where('product_id',$product['id'])->wherein('purchase_order_id',$getPOids)->sum('actual_qty');
                /*if($orderQty >$product['current_stock']){
                	$produceQty = $orderQty - $product['current_stock'];
                }else{
                	$produceQty = "";
                }*/
                $saleInvoices =  SaleInvoice::join('sale_invoice_items','sale_invoice_items.sale_invoice_id','=','sale_invoices.id')->select('sale_invoices.*')->with('invoice_items')->wherein('sale_invoices.purchase_order_id',$getPOids)->where('sale_invoice_items.product_id',$product['id'])->get()->toArray();
                $sale_qty = 0;
                foreach($saleInvoices as $sale_pro){
                    $sale_qty += array_sum(array_column($sale_pro['invoice_items'],'qty'));
                }
                $pending_qty = $orderQty - $sale_qty;
                if($pending_qty >0){
                    $actionValues='';
                    $num = ++$i;
                    $records["data"][] = array(      
                        $num,
                        $product['product_name'],
                        $pending_qty, 
                        /*$product['current_stock'], 
                        '',
                        $produceQty,*/
                        $actionValues
                    );
                }
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Planning Sheet";
        return View::make('admin.planning.planning-sheet')->with(compact('title'));
    }
}
