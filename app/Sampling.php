<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Sampling;
use App\SamplingItem;
use App\Dealer;
use DB;
class Sampling extends Model
{
    //
    public function dealer(){
        return $this->belongsTo('App\Dealer');
    }

    public function created_by_dealer_info(){
        return $this->belongsTo('App\Dealer','created_by_dealer','id');
    }


    public function user(){
        return $this->belongsTo('App\User');
    }

    public function sampleitems(){
        return $this->hasMany('App\SamplingItem')->with(['product','sale_invoice_items']);
    }

    public function sampling_items(){
        return $this->hasMany('App\SamplingItem')->with(['product']);
    }

    public function sampling_invoice_items(){
        return $this->hasMany('App\SamplingSaleInvoice');
    }

    public function sale_invoices(){
        return $this->hasMany('App\SamplingSaleInvoice')->with('productinfo');
    }

    public static function createSample($data,$resp){
        //echo "<pre>"; print_r($data); die;
        $createsample = new Sampling;
        if($data['action'] == 'dealer'){
            $parentDealerId = \App\Dealer::getParentDealer($resp['dealer']);
        	$createsample->financial_year = financialYear();
            $createsample->dealer_id   =  $parentDealerId;
            $getLastRef = Sampling::where('dealer_id',$parentDealerId)->where('sample_type',$data['sample_type'])->where('financial_year',financialYear());
            if($data['sample_type'] =="free"){
            	$suffix = "FS";
            }else{
            	$suffix = "PS";
            }
            $getLastRef = $getLastRef->orderby('sample_ref_no','DESC')->first();
            $getLastRef = json_decode(json_encode($getLastRef),true);
            if(!empty($getLastRef)){
                $refNo = $getLastRef['sample_ref_no'] +1;
            }else{
                $refNo = 1;
            }
            $createsample->sample_ref_no   =  $refNo;
            $createsample->sample_ref_no_string   =  $suffix."-".$refNo."/".financialYear();
            $createsample->created_by_dealer = $resp['dealer']['id'];
        }else{
            $createsample->financial_year = financialYear();
            $createsample->user_id   =  $resp['user']['id'];
            $getLastRef = Sampling::where('user_id',$resp['user']['id'])->where('sample_type',$data['sample_type'])->where('financial_year',financialYear());
            if($data['sample_type'] =="free"){
                $suffix = "FS";
            }
            $getLastRef = $getLastRef->orderby('sample_ref_no','DESC')->first();
            $getLastRef = json_decode(json_encode($getLastRef),true);
            if(!empty($getLastRef)){
                $refNo = $getLastRef['sample_ref_no'] +1;
            }else{
                $refNo = 1;
            }
            $createsample->sample_ref_no   =  $refNo;
            $createsample->sample_ref_no_string   =  $suffix."-".$refNo."/".financialYear();
        }
        $createsample->action =  $data['action'];
        $createsample->sample_type =  $data['sample_type'];
        $createsample->required_through =  $data['required_through'];
        $createsample->request_type =  $data['request_type'];
        $createsample->remarks     =  $data['remarks'];
        if(isset($data['dealer'])) {
            $createsample->sample_status =  'pending';
        }else{
            $createsample->sample_status =  'pending';
        }
        if(isset($data['customer_id'])&& !empty($data['customer_id'])) {
            $createsample->customer_id =  $data['customer_id'];
        }
        $createsample->sampling_date = $data['sample_date'];
        $createsample->save();
        $totalPrice = 0;
        foreach($data['items'] as $item){
            $productinfo = Product::with('raw_materials')->where('id',$item['product_id'])->first();
            $sampleitem = new SamplingItem;
            $sampleitem->sampling_id = $createsample->id;
            $sampleitem->product_id = $productinfo->id;
            if(isset($item['pack_size']) && !empty($item['pack_size'])){
                $sampleitem->pack_size = $item['pack_size'];
                $sampleitem->actual_pack_size = $item['pack_size'];
            }
            if(isset($item['no_of_packs']) && !empty($item['no_of_packs'])){
                $sampleitem->no_of_packs = $item['no_of_packs'];
            }
            if(isset($item['competitor_product_name']) && !empty($item['competitor_product_name'])){
                $sampleitem->competitor_product_name = $item['competitor_product_name'];
            }
            if(isset($item['customer_potential_product']) && !empty($item['customer_potential_product'])){
                $sampleitem->customer_potential_product = $item['customer_potential_product'];
            }
            $sampleitem->qty = $item['qty'];
            $sampleitem->actual_qty = $item['qty'];
            $sampleitem->additional_cost = $item['additional_cost'];
            $sampleitem->product_detail_id = $productinfo->product_detail_id;
            if($data['sample_type'] =="paid"){
                $totalDiscountPer = 0;
                if(isset($item['discounts']) && !empty($item['discounts'])){
                    $sampleitem->discounts=json_encode($item['discounts']);
                    $totalDiscountPer = array_sum(array_column($item['discounts'], 'value'));
                }
                if($data['action'] == 'dealer' && isset($item['price_id']) && !empty($item['price_id'])){
                    $priceDetails = DB::table('product_pricings')->where('id',$item['price_id'])->first();
                    $sampleitem->price = $priceDetails->dealer_price;
                    $net_price = $sampleitem->price - ($sampleitem->price * $totalDiscountPer/100);
                    $net_price =  $net_price + $item['additional_cost'];
                    $sampleitem->net_price = $net_price;
                }else{
                    
                }
            }
            $rawMaterials = array();
            foreach($productinfo->raw_materials as $rkey=> $rawMaterial){
            	$rawMaterials[$rkey]['raw_material_id']  = $rawMaterial->raw_material_id;
            	$rawMaterials[$rkey]['percentage_included']  = $rawMaterial->percentage_included;
            }
            if(!empty($rawMaterials)){
            	$sampleitem->raw_materials =json_encode($rawMaterials);
            }
            $sampleitem->save();
            if($data['action'] == 'dealer'){
                $totalPrice += $sampleitem->net_price * $item['qty'];
            }
        }

        if($data['sample_type'] =="paid"){
            $totatSaleAmt = $totalPrice;
            $calGST =  ($totatSaleAmt *$data['gst']) /100;
            $totatSaleAmt = $totatSaleAmt + $calGST;
            $updateSample = Sampling::find($createsample->id);
            $updateSample->subtotal =  $totalPrice;
            $updateSample->gst = $calGST;
            $createsample->gst_per     =  $data['gst'];
            $updateSample->grand_total = $totatSaleAmt;
            $updateSample->save();
        }
        return $createsample->id;
    }
}
