<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\PurchaseOrderItemDiscount;
use App\PurchaseOrder;
use App\PurchaseOrderItemRawMaterial;
use App\Product;
use App\Dealer;
use App\DealerProduct;
use App\UserCustomerShare;
use DB;
class PurchaseOrder extends Model
{
    //
    public function customer(){
    	return $this->belongsTo('App\Customer');
    }

    public function dealer(){
        return $this->belongsTo('App\Dealer');
    }

    public function customer_employee(){
        return $this->belongsTo('App\CustomerEmployee');
    }

    public function linked_employee(){
        return $this->belongsTo('App\User','linked_employee_id','id')->select('id','name','designation','email','mobile');
    }

    public function orderitems(){
    	return $this->hasMany('App\PurchaseOrderItem')->select('id','purchase_order_id','product_id','qty','actual_qty','inherit_type','batch_out_duration','dealer_markup','market_price','packing_size_id','net_price','product_price','dealer_price','spsod','comments','dealer_qty_discount','dealer_special_discount','is_urgent','item_action','customer_discounts','on_hold_until','additional_charges')->with(['product'=> function($query){
            $query->with('qty_discounts');
        },'packingsize']);
    }

    public function sale_invoices(){
        return $this->hasMany('App\SaleInvoice')->with(['invoice_items'=>function($query){
            $query->with('purchase_order_item');
        }]);
    }

    public function discounts(){
    	return $this->hasMany('App\PurchaseOrderItemDiscount','purchase_order_id');
    }

    public function saleinvoices(){
        return $this->hasMany('App\SaleInvoice','purchase_order_id')->with('invoice_items');
    }

    public function adjust_cancel_items(){
        return $this->hasMany('App\PurchaseOrderAdjustment')->select('id','type','purchase_order_id','purchase_order_item_id','qty','reason')->with('orderitem');
    }

    public function adjust_items(){
        return $this->hasMany('App\PurchaseOrderAdjustment')->where('type','adjustment')->select('id','type','purchase_order_id','purchase_order_item_id','qty','reason')->with('orderitem');
    }

    public function cancel_items(){
        return $this->hasMany('App\PurchaseOrderAdjustment')->where('type','cancel')->select('id','type','purchase_order_id','purchase_order_item_id','qty','reason')->with('orderitem');
    }

    public static function createPO($data,$resp){
        //echo "<pre>"; print_r($data); die;
        $createpo = new PurchaseOrder;
        if(isset($data['user_id'])){
            $createpo->user_id = $data['user_id'];
        }
        if($data['action'] == 'dealer_customer'){
            $parentDealerId = \App\Dealer::getParentDealer($resp['dealer']);
            $resp['dealer']['id'] = $parentDealerId;
            $createpo->dealer_id   =  $resp['dealer']['id'];
            $createpo->customer_id =  $data['customer_id'];
        }elseif($data['action'] == 'dealer'){
            $parentDealerId = \App\Dealer::getParentDealer($resp['dealer']);
            $resp['dealer']['id'] = $parentDealerId;
            $createpo->dealer_id   =  $resp['dealer']['id'];
            $getLastRef = PurchaseOrder::where('action','dealer')->where('dealer_id',$resp['dealer']['id']);
            if(isset($data['trader_po'])){
                $createpo->trader_po = 1;  
                $getLastRef = $getLastRef->where('trader_po',1); 
                $suffix = "TD-";
            }else{
                $getLastRef = $getLastRef->where('trader_po',0);
                $suffix = "";
            }
            $getLastRef = $getLastRef->orderby('po_ref_no','DESC')->first();
            $getLastRef = json_decode(json_encode($getLastRef),true);
            if(!empty($getLastRef)){
                $refNo = $getLastRef['po_ref_no'] +1;
            }else{
                $refNo = 1;
            }
            $dealerinfo = Dealer::where('id',$resp['dealer']['id'])->first();
            $createpo->po_ref_no   =  $refNo;
            $createpo->po_ref_no_string   =  $suffix.$dealerinfo->short_name."-".$refNo."/".financialYear();
        }elseif($data['action'] == 'customer'){
            $createpo->customer_id =  $data['customer_id'];
            if(isset($data['dealer_id'])){
                $createpo->dealer_id =  $data['dealer_id'];
            }else{
                $createpo->dealer_id =  NULL;
            }
            if(empty($createpo->dealer_id)){
                $getLastRef = PurchaseOrder::wherein('action',['customer','customer_employee'])->where('customer_id',$data['customer_id'])->whereNull('dealer_id');  
                $getLastRef = $getLastRef->orderby('po_ref_no','DESC')->first();
                $getLastRef = json_decode(json_encode($getLastRef),true);
                if(!empty($getLastRef)){
                    $refNo = $getLastRef['po_ref_no'] +1;
                }else{
                    $refNo = 1;
                }
                $createpo->po_ref_no   =  $refNo;
                $createpo->po_ref_no_string   =  "CUST-".$refNo."/".financialYear();
            }
        }elseif($data['action'] == 'customer_employee'){
            $createpo->customer_id =  $data['customer_id'];
            $createpo->dealer_id =  $data['dealer_id'];
            $createpo->customer_employee_id =  $data['customer_employee_id'];
            if(empty($createpo->dealer_id)){
                $getLastRef = PurchaseOrder::wherein('action',['customer','customer_employee'])->where('customer_id',$data['customer_id'])->whereNull('dealer_id');  
                $getLastRef = $getLastRef->orderby('po_ref_no','DESC')->first();
                $getLastRef = json_decode(json_encode($getLastRef),true);
                if(!empty($getLastRef)){
                    $refNo = $getLastRef['po_ref_no'] +1;
                }else{
                    $refNo = 1;
                }
                $createpo->po_ref_no   =  $refNo;
                $createpo->po_ref_no_string   =  "CUST-".$refNo."/".financialYear();
            }
        }
        
        if(isset($data['customer_id']) && !empty($data['customer_id'])){
            $empDetails = UserCustomerShare::where('customer_id',$data['customer_id'])->orderby('user_date','DESC')->first();
            if(is_object($empDetails)){
                $createpo->linked_employee_id =  $empDetails->user_id;
            }
        }
        $createpo->action =  $data['action'];
        $createpo->customer_purchase_order_no =  $data['customer_purchase_order_no'];
        $createpo->mode        =  $data['mode'];
        $createpo->remarks     =  $data['remarks'];
        if($data['action'] == 'dealer_customer'){
            $createpo->po_status =  'approved';
        }elseif($data['action'] == 'dealer'){
            $createpo->po_status =  'pending';
        }else{
            $createpo->po_status =  'pending';
        }
        $createpo->gst_per     =  $data['gst'];
        if(isset($data['dealer_purchase_order_no'])){
            $createpo->dealer_purchase_order_no =  $data['dealer_purchase_order_no'];
        }

        if(isset($data['payment_term_type'])){
            $createpo->payment_term_type =  $data['payment_term_type'];
        }
        if(isset($data['payment_discount'])){
            $createpo->payment_discount_per =  $data['payment_discount'];
        }else{
            $data['payment_discount'] = 0;
            $createpo->payment_discount_per = 0;
        }
        if(isset($data['payment_term'])){
            $createpo->payment_term =  $data['payment_term'];
        }else{
            $data['payment_term'] = '';
        }
        if(isset($data['order_placed_by'])){
            $createpo->order_placed_by = $data['order_placed_by'];
        }
        $createpo->po_date = $data['po_date'];

        if(isset($data['is_mini_pack_order'])){
            $createpo->is_mini_pack_order = 1;
        }

        $createpo->save();
        $totalPrice = 0;
        $corporate_discount_per =0;
        $corporate_discount = 0;
        foreach($data['items'] as $item){
            $productinfo = Product::with('raw_materials')->where('id',$item['product_id'])->first();
            $poitem = new PurchaseOrderItem;
            $poitem->purchase_order_id = $createpo->id;
            $poitem->product_id = $productinfo->id;
            $poitem->qty = $item['qty'];
            $poitem->actual_qty = $item['qty'];
            $poitem->product_detail_id = $productinfo->product_detail_id;
            $poitem->packing_size_id = $productinfo->packing_size_id;
            $poitem->inherit_type = $productinfo->inherit_type;
            $poitem->batch_out_duration = $productinfo->batch_out_duration;
            $poitem->rm_cost = $productinfo->rm_cost;
            $poitem->formulation_cost = $productinfo->formulation_cost;
            $poitem->packing_cost = $productinfo->packing_cost;
            $poitem->total_product_cost = $productinfo->total_product_cost;
            $poitem->dp_calculation_cost = $productinfo->dp_calculation_cost;
            $poitem->company_mark_up = $productinfo->company_mark_up;
            /*if(isset($item['customer_discounts']) && !empty($item['customer_discounts'])){
                $poitem->customer_discounts=json_encode($item['customer_discounts']);
            }*/
            if($data['action'] == 'dealer'){
                if(isset($item['price_id']) && !empty($item['price_id'])){
                    $priceDetails = DB::table('product_pricings')->where('id',$item['price_id'])->first();
                    $poitem->dealer_price = $priceDetails->dealer_price;
                }else{
                    $poitem->dealer_price = $productinfo->dealer_price;
                }
                $poitem->product_price = $poitem->dealer_price;

                if (isset($item['mini_pack_size'])) {

                    $poitem->mini_pack_size = $item['mini_pack_size'];
                    $poitem->mini_pack_size_remarks = $item['mini_pack_size'];
                }

                $poitem->dealer_qty_discount = 0;
                if(isset($item['dealer_qty_discount'])){
                    $poitem->dealer_qty_discount = $item['dealer_qty_discount'];
                }
                $poitem->dealer_special_discount = 0;
                if(isset($item['dealer_special_discount'])){
                    $poitem->dealer_special_discount = $item['dealer_special_discount'];
                }
                // Default additional charges
                $poitem->additional_charges = 0;

                if (isset($item['additional_charges']) && $item['additional_charges'] > 0) {

                    $rawAdditional = $item['additional_charges'];

                    // Custom rounding for additional charges
                    $intPart = floor($rawAdditional);
                    $decimal = $rawAdditional - $intPart;

                    $poitem->additional_charges = ($decimal >= 0.30)
                        ? $intPart + 1
                        : $intPart;
                }

                // Total discount
                $total_discount = $poitem->dealer_qty_discount + $poitem->dealer_special_discount;

                // Calculate raw net price
                $rawNetPrice = $poitem->product_price
                    - ($poitem->product_price * $total_discount / 100)
                    + $poitem->additional_charges;

                // Custom rounding for net price
                $intPart = floor($rawNetPrice);
                $decimal = $rawNetPrice - $intPart;

                $poitem->net_price = ($decimal >= 0.30)
                    ? $intPart + 1
                    : $intPart;

            }elseif($data['action'] == 'customer'){
                $directCustomerDiscount = \App\CustomerDiscount::where('product_id', $productinfo->id)
                    ->where('customer_id', $data['customer_id'])
                    ->first();

                if ($directCustomerDiscount && $directCustomerDiscount->discount_type == "net_products") {
                    $poitem->market_price = $item['market_price'];
                    $poitem->net_price = $directCustomerDiscount->net_price;
                    $poitem->product_price = $poitem->market_price;

                } else if ($directCustomerDiscount && $directCustomerDiscount->discount_type == "discounts") {
                    $marketPrice = $item['market_price'];
                    $qty = $item['qty'];

                    $discountDetails = [];
                    $totalDiscountPercent = 0;

                    // Add normal discount
                    if ($directCustomerDiscount->discount) {
                        $discountDetails[] = [
                            'value' => $directCustomerDiscount->discount,
                            'name' => 'discount',
                            'description' => 'Discount',
                        ];
                        $totalDiscountPercent += $directCustomerDiscount->discount;
                    }

                    // Add special discount if quantity matches
                    if ($directCustomerDiscount->special_discount && $qty >= $directCustomerDiscount->min_qty) {
                        $discountDetails[] = [
                            'value' => $directCustomerDiscount->special_discount,
                            'name' => 'special_discount',
                            'description' => 'Special Discount',
                        ];
                        $totalDiscountPercent += $directCustomerDiscount->special_discount;
                    }

                    // Apply total discount at once
                    $netPrice = $marketPrice - ($marketPrice * $totalDiscountPercent / 100);

                    $poitem->market_price = $item['market_price'];
                    $poitem->net_price = round($netPrice, 2);
                    $poitem->product_price = $item['market_price'];
                    $poitem->customer_discounts = json_encode($discountDetails);

                }
            }else{
                $poitem->market_price = $item['market_price'];
                $poitem->net_price = $item['net_price'];
                $poitem->product_price = $poitem->market_price;
            }
            $poitem->dealer_markup = $productinfo->dealer_markup;
            $poitem->freight = $productinfo->freight;
            if(isset($item['spsod'])){
                $poitem->spsod = $item['spsod'];
            }
            $poitem->save();
            if($data['action'] == 'dealer'){
                $totalPrice += $poitem->net_price * $item['qty'];
            }else{
                //$totalPrice += $poitem->market_price * $item['qty'];
                $totalPrice += $poitem->net_price * $item['qty'];
            }
            foreach($productinfo->raw_materials as $rawMaterial){
                $porawmaterial = new PurchaseOrderItemRawMaterial;
                $porawmaterial->purchase_order_item_id = $poitem->id;
                $porawmaterial->product_id = $productinfo->id;
                $porawmaterial->raw_material_id = $rawMaterial->raw_material_id;
                $porawmaterial->percentage_included = $rawMaterial->percentage_included;
                $porawmaterial->save();
            }
            /*if(isset($data['customer_id']) && !empty($data['customer_id'])){
                //Product Base
                $getCustomerProDis = DB::table('customer_discounts')->where('customer_id',$data['customer_id'])->where('product_id',$productinfo->id)->where('discount_type','Product Base')->first();
                $getCustomerProDis = json_decode(json_encode($getCustomerProDis),true);
                if(!empty($getCustomerProDis)){
                    $poitemDis = new PurchaseOrderItemDiscount;
                    $poitemDis->purchase_order_item_id = $poitem->id;
                    $poitemDis->purchase_order_id = $createpo->id;
                    $poitemDis->discount_type = $getCustomerProDis['discount_type'];
                    $poitemDis->discount_type = $getCustomerProDis['discount_type'];
                    $poitemDis->product_id = $getCustomerProDis['product_id'];
                    $poitemDis->committed_sale_qty = $getCustomerProDis['committed_sale_qty'];
                    $poitemDis->dealer_share = $getCustomerProDis['dealer_share'];
                    $poitemDis->company_share = $getCustomerProDis['company_share'];
                    $poitemDis->discount = $getCustomerProDis['discount'];
                    $poitemDis->save();
                }
                //Corporate, Trurnover
                $Customerdiscounts = array('Trurnover','Corporate');
                foreach($Customerdiscounts as $custDis){
                    $getCustomerProDis = DB::table('customer_discounts')->where('customer_id',$data['customer_id'])->where('discount_type',$custDis)->first();
                    $getCustomerProDis = json_decode(json_encode($getCustomerProDis),true);
                    if(!empty($getCustomerProDis)){
                        if($custDis == 'Corporate'){
                            $companyShare = (($totalPrice * $getCustomerProDis['company_share'])/100);
                            $dealerShare = (($totalPrice * $getCustomerProDis['dealer_share'])/100);
                            $corporate_discount_per = $getCustomerProDis['discount'];
                            $corporate_discount     = $companyShare + $dealerShare;
                        }
                        $poitemDis = new PurchaseOrderItemDiscount;
                        $poitemDis->purchase_order_id = $createpo->id;
                        $poitemDis->discount_type = $getCustomerProDis['discount_type'];
                        $poitemDis->dealer_share = $getCustomerProDis['dealer_share'];
                        $poitemDis->company_share = $getCustomerProDis['company_share'];
                        $poitemDis->discount = $getCustomerProDis['discount'];
                        $poitemDis->save();
                    }
                } 
            }*/
            //Manage dealer customer stock
            if($data['action'] == 'dealer_customer'){
                $dealerProd = DB::table('dealer_products')->where(['dealer_id'=>$resp['dealer']['id'],'product_id'=>$productinfo->id])->first();
                    if($dealerProd){
                        $updatedealerProd = DealerProduct::find($dealerProd->id);
                        $updatedealerProd->pending_customer_orders = $dealerProd->pending_customer_orders + $item['qty'];
                    }else{
                        $updatedealerProd = new DealerProduct;
                        $updatedealerProd->dealer_id = $resp['dealer']['id'];
                        $updatedealerProd->product_id = $productinfo->id;
                        $updatedealerProd->pending_customer_orders = $item['qty'];
                    }
                    $updatedealerProd->save();
            }
        }
        $totatSaleAmt = $totalPrice;
        /*$getPaymentDis = (($totatSaleAmt * $data['payment_discount'])/100);
        if(isset($data['payment_term_type']) && $data['payment_term_type'] =="On Bill"){
            $totatSaleAmt = $totatSaleAmt - $getPaymentDis - $corporate_discount;
        }else{
            $totatSaleAmt = $totatSaleAmt  - $corporate_discount;
        }*/
        $calGST =  ($totatSaleAmt *$data['gst']) /100;
        $totatSaleAmt = $totatSaleAmt + $calGST;
        $updatePO = PurchaseOrder::find($createpo->id);
        $updatePO->price =  $totalPrice;
        /*$updatePO->payment_discount = $getPaymentDis;
        $updatePO->corporate_discount_per = $corporate_discount_per;
        $updatePO->corporate_discount = $corporate_discount;*/
        $updatePO->gst = $calGST;
        $updatePO->grand_total = $totatSaleAmt;
        $updatePO->save();
        //self::sendPOEmails($createpo->id, $data);
        return $createpo->id;
    }


    /**
     * ------------------------------------------------------------
     * Send Email Notifications After Purchase Order is Created
     * ------------------------------------------------------------
     *
     * @param int   $poId
     * @param array $data
     * @return void
     */
    public static function sendPOEmails($poId, $data)
    {
        try {
            $po = PurchaseOrder::with([
                'dealer',
                'customer',
                'orderitems.product'
            ])->find($poId);

            if (!$po) {
                \Log::error("PO Email Error: Purchase Order not found. ID: " . $poId);
                return;
            }

            /**
             * ---------------------------
             * Send Admin Notification
             * ---------------------------
             */
            $adminEmails = [
                'kunalmahajan710@gmail.com',
                'singhania.kamal@gmail.com'
            ];

            \Mail::to($adminEmails)->send(new \App\Mail\AdminPOCreatedMail($po));

            /**
             * ---------------------------
             * Action-Based Email Rules
             * ---------------------------
             */
            if ($data['action'] == 'dealer_customer') {
                $po->dealer->email = "singhania.kamal@gmail.com";
                // Dealer email
                if ($po->dealer && $po->dealer->email) {
                    \Mail::to($po->dealer->email)
                        ->send(new \App\Mail\DealerCustomerPOCreatedMail($po));
                }

                // Customer email
                if ($po->customer && $po->customer->email) {
                    $po->customer->email = "singhania.kamal@gmail.com";
                    \Mail::to($po->customer->email)
                        ->send(new \App\Mail\CustomerPOCreatedMail($po));
                }

            } elseif ($data['action'] == 'dealer') {

                // Dealer places PO for himself
                if ($po->dealer && $po->dealer->email) {
                    $po->dealer->email = "singhania.kamal@gmail.com";
                    \Mail::to($po->dealer->email)
                        ->send(new \App\Mail\DealerSelfPOCreatedMail($po));
                }

            } elseif ($data['action'] == 'customer') {

                // Customer Places PO for himself
                if ($po->customer && $po->customer->email) {
                    $po->customer->email = "singhania.kamal@gmail.com";
                    \Mail::to($po->customer->email)
                        ->send(new \App\Mail\CustomerPOCreatedMail($po));
                }
            }

        } catch (\Exception $e) {
            \Log::error("PO Email Error: " . $e->getMessage());
        }
    }

}
