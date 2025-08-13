<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SaleInvoice;
use App\SaleInvoiceDiscount;
use App\DealerProduct;
use DB;
class SaleInvoice extends Model
{
    //
    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    public function dealer(){
        return $this->belongsTo('App\Dealer');
    }

    public function purchase_order(){
        return $this->belongsTo('App\PurchaseOrder');
    }
    
    public function invoice_items(){
    	return $this->hasMany('App\SaleInvoiceItem','sale_invoice_id')->with('productinfo');
    }

    public function item(){
        return $this->hasOne('App\SaleInvoiceItem','sale_invoice_id')->select('id','sale_invoice_id','purchase_order_item_id','qty','product_id')->with('productinfo');
    }

    public static function createSaleInvoice($data,$resp){
    	$createSO = new SaleInvoice;
    	if($data['action'] == 'dealer_customer'){
            $parentDealerId = \App\Dealer::getParentDealer($resp['dealer']);
            $resp['dealer']['id'] = $parentDealerId;
            $createSO->dealer_id   =  $resp['dealer']['id'];
            $createSO->customer_id =  $data['customer_id'];
        }
        $createSO->sale_invoice_date =  $data['sale_invoice_date'];
        $createSO->purchase_order_id =  $data['purchase_order_id'];
        $createSO->dealer_invoice_no =  $data['dealer_invoice_no'];
        $createSO->remarks     =  $data['remarks'];
        if(isset($data['payment_term_type'])){
            $createSO->payment_term_type =  $data['payment_term_type'];
        }else{
            $data['payment_term_type'] = '';
        }
        if(isset($data['payment_discount'])){
            $createSO->payment_discount_per =  $data['payment_discount'];
        }else{
            $data['payment_discount'] = 0;
            $createSO->payment_discount_per = 0;
        }
        if(isset($data['payment_term'])){
            $createSO->payment_term =  $data['payment_term'];
        }else{
            $data['payment_term'] = '';
        }
        $createSO->gst_per = $data['gst'];
        $createSO->remarks =  $data['remarks'];
        $createSO->save();
        $totalmarketPrice = 0;
        foreach($data['order_items'] as $item){
            //$getinfo = DB::table('purchase_order_items')->where('id',$item['order_item_id'])->first();
            $getinfo = DB::table('purchase_order_items')->where('purchase_order_id',$data['purchase_order_id'])->where('product_id',$item['product_id'])->first();
            $saleOrderitem = new SaleInvoiceItem;
            $saleOrderitem->sale_invoice_id = $createSO->id;
            $saleOrderitem->purchase_order_item_id = $getinfo->id;
            $saleOrderitem->product_id = $item['product_id'];
            $saleOrderitem->qty = $item['qty'];
            $saleOrderitem->price = $getinfo->market_price;
            $saleOrderitem->subtotal = $getinfo->market_price * $item['qty'];
            if(isset($item['spsod'])){
                $saleOrderitem->spsod = $item['spsod'];
            }
            $saleOrderitem->save();
            $totalmarketPrice += $getinfo->market_price * $item['qty'];

            if($data['action'] == 'dealer_customer'){
                $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$resp['dealer']['id'],'product_id'=>$item['product_id']])->first();
                if($dealerProd){
                    $updatedealerProd = DealerProduct::find($dealerProd->id);
                    $updatedealerProd->pending_customer_orders = $dealerProd->pending_customer_orders - $item['qty'];
                    $updatedealerProd->stock_in_hand = $dealerProd->stock_in_hand - $item['qty'];
                    $updatedealerProd->save();
                }
            }
        }
        $corporate_discount_per =0;
        $corporate_discount = 0;
        $totatSaleAmt = $totalmarketPrice;
        $getPaymentDis = (($totatSaleAmt * $data['payment_discount'])/100);
        //Get Corporate Dis
        $gerCorpDis = DB::table('purchase_order_item_discounts')->where('discount_type','Corporate')->where('purchase_order_id',$data['purchase_order_id'])->first();
        if($gerCorpDis){
            $companyShare = (($totalmarketPrice * $gerCorpDis->company_share)/100);
            $dealerShare = (($totalmarketPrice * $gerCorpDis->dealer_share)/100);
            $corporate_discount_per = $gerCorpDis->discount;
            $corporate_discount     = $companyShare + $dealerShare;
            //Create Sale Invoice Discount
            $saleInvoiceDis = new SaleInvoiceDiscount;
            $saleInvoiceDis->sale_invoice_id = $createSO->id;
            $saleInvoiceDis->discount_type = 'Corporate';
            $saleInvoiceDis->dealer_share_per = $gerCorpDis->dealer_share;
            $saleInvoiceDis->company_share_per = $gerCorpDis->company_share;
            $saleInvoiceDis->total_share_per = $gerCorpDis->discount;
            $saleInvoiceDis->company_share = $companyShare;
            $saleInvoiceDis->dealer_share = $dealerShare;
            $saleInvoiceDis->total_share = $corporate_discount;
            $saleInvoiceDis->save();

        }
        if(isset($data['payment_term_type']) && $data['payment_term_type'] =="On Bill"){
            $totatSaleAmt = $totatSaleAmt - $getPaymentDis - $corporate_discount;
        }else{
            $totatSaleAmt = $totatSaleAmt  - $corporate_discount;
        }
        $calGST =  ($totatSaleAmt *$data['gst']) /100;
        $totatSaleAmt = $totatSaleAmt + $calGST;
        $updateSO = SaleInvoice::find($createSO->id);
        $updateSO->price =  $totalmarketPrice;
        $updateSO->payment_discount = $getPaymentDis;
        $updateSO->corporate_discount_per = $corporate_discount_per;
        $updateSO->corporate_discount = $corporate_discount;
        $updateSO->gst = $calGST;
        $updateSO->grand_total = $totatSaleAmt;
        $updateSO->save();
    }

    public static function getDealerSaleInvoices($data,$resp){
        $saleInvoices = SaleInvoice::with(['dealer','invoice_items','purchase_order'=>function($query){
            $query->select('id','customer_purchase_order_no');
        }])->whereDate('sale_invoice_date','>=',$data['start_date'])->whereDate('sale_invoice_date','<=',$data['end_date']);
        
        $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
        $saleInvoices = $saleInvoices->whereNull('customer_id');
        $saleInvoices = $saleInvoices->whereIn('dealer_id',$dealerIds);
        $saleInvoices = $saleInvoices->get();
        return $saleInvoices;
    }


    public static function getCustSaleInvoices($data,$resp){
        $saleInvoices = SaleInvoice::with(['dealer','customer','invoice_items','purchase_order'=>function($query){
            $query->select('id','customer_purchase_order_no');
        }])->whereDate('sale_invoice_date','>=',$data['start_date'])->whereDate('sale_invoice_date','<=',$data['end_date']);
        if(isset($resp['dealer']['id'])){
            $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
            //$saleInvoices = $saleInvoices->where(['dealer_id'=>$resp['dealer']['id']])->whereNotNull('customer_id');
            $saleInvoices = $saleInvoices->whereNotNull('customer_id');
            $saleInvoices = $saleInvoices->whereIn('dealer_id',$dealerIds);
        }
        if(isset($data['customer_id'])){
            $saleInvoices = $saleInvoices->where(['customer_id'=>$data['customer_id']]);
        }

        if(isset($data['customer_ids'])){
            $saleInvoices = $saleInvoices->wherein('customer_id',$data['customer_ids']);
        }
        $saleInvoices = $saleInvoices->get();
        return $saleInvoices;
    }

    public static function getinvoices($user){
        $sale_invoices = SaleInvoice::with('invoice_items')->where('sale_invoices.do_number','')->where('sale_invoices.dealer_invoice_no','');
        if($user['action'] =='dealer'){
            $sale_invoices = $sale_invoices->where('sale_invoices.dealer_id',$user['dealer_id'])->whereNull('customer_id');
        }
        elseif($user['action'] =='customer'){
            $sale_invoices = $sale_invoices->where('sale_invoices.customer_id',$user['customer_id'])->whereNull('dealer_id');
        }
        $sale_invoices = $sale_invoices->get();
        $sale_invoices = json_decode(json_encode($sale_invoices),true);

        /*echo "<pre>"; print_r($sale_invoices); die;*/
        return array('sale_invoices'=>$sale_invoices,'sale_invoice_ids'=>implode(',',array_column($sale_invoices,'id')));
    }

    public static function doinvoices($donumber){
        $sale_invoices = SaleInvoice::with('invoice_items')->where('sale_invoices.do_ref_no',$donumber)->where('sale_invoices.dealer_invoice_no','');
        $sale_invoices = $sale_invoices->get();
        $sale_invoices = json_decode(json_encode($sale_invoices),true);
        //echo "<pre>"; print_r($sale_invoices); die;
        return array('sale_invoices'=>$sale_invoices,'sale_invoice_ids'=>implode(',',array_column($sale_invoices,'id')));
    }

    public static function billinvoices($invoiceno){
        $sale_invoices = SaleInvoice::with('invoice_items')->where('sale_invoices.dealer_invoice_no',$invoiceno)->where('sale_invoices.transport_name','');
        $sale_invoices = $sale_invoices->get();
        $sale_invoices = json_decode(json_encode($sale_invoices),true);
        return array('sale_invoices'=>$sale_invoices,'sale_invoice_ids'=>implode(',',array_column($sale_invoices,'id')));
    }

    public static function dispatchedMaterials($invoiceno,$data){
        $sale_invoices = SaleInvoice::with('invoice_items')->where('sale_invoices.dealer_invoice_no',$invoiceno)->where('sale_invoices.lr_no','!=','')->join('sale_invoice_items','sale_invoice_items.sale_invoice_id','=','sale_invoices.id')->select('sale_invoices.*','sale_invoice_items.product_id','sale_invoice_items.batch_no');
        if(isset($data['product_id'])&& !empty($data['product_id'])){
            $sale_invoices = $sale_invoices->where('sale_invoice_items.product_id',$data['product_id']);
        }
        if(isset($data['batch_no'])&& !empty($data['batch_no'])){
            $sale_invoices = $sale_invoices->where('sale_invoice_items.batch_no',$data['batch_no']);
        }
        $sale_invoices = $sale_invoices->get();
        $sale_invoices = json_decode(json_encode($sale_invoices),true);
        return array('sale_invoices'=>$sale_invoices,'sale_invoice_ids'=>implode(',',array_column($sale_invoices,'id')));
    }
}
