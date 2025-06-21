<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SamplingSaleInvoice extends Model
{
    //
    public function productinfo(){
        return $this->belongsTo('App\Product','product_id')->select('id','product_name','product_code','hsn_code','keywords');
    }

    public function dealer(){
        return $this->belongsTo('App\Dealer','dealer_id');
    }

    public function sampling(){
        return $this->belongsTo('App\Sampling','sampling_id','id')->with('created_by_dealer_info');
    }

    
    public static function getinvoices($user,$required_through){
    	$sale_invoices = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->join('products','products.id','=','sampling_sale_invoices.product_id')->join('sampling_items','sampling_items.id','=','sampling_sale_invoices.sampling_item_id')->where('sampling_sale_invoices.do_number','')->where('sampling_sale_invoices.invoice_no','')->where('samplings.required_through',$required_through)->select('sampling_sale_invoices.id as sale_invoice_id','sampling_sale_invoices.sampling_item_id','products.product_name','products.product_code','sampling_items.actual_pack_size','sampling_sale_invoices.qty');
    	if(isset($user['dealer_id'])){
    		$sale_invoices = $sale_invoices->where('sampling_sale_invoices.dealer_id',$user['dealer_id']);
    	}
    	if(isset($user['user_id'])){
    		$sale_invoices = $sale_invoices->where('sampling_sale_invoices.user_id',$user['user_id']);
    	}
    	$sale_invoices = $sale_invoices->get();
    	$sale_invoices = json_decode(json_encode($sale_invoices),true);
    	return array('sale_invoices'=>$sale_invoices,'sale_invoice_ids'=>implode(',',array_column($sale_invoices,'sale_invoice_id')));
    }

    public static function doinvoices($donumber){
        $sale_invoices = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->join('products','products.id','=','sampling_sale_invoices.product_id')->join('sampling_items','sampling_items.id','=','sampling_sale_invoices.sampling_item_id')->where('sampling_sale_invoices.do_ref_no',$donumber)->where('sampling_sale_invoices.invoice_no','')->select('sampling_sale_invoices.id as sale_invoice_id','products.product_name','products.product_code','sampling_items.actual_pack_size','sampling_sale_invoices.qty');
        $sale_invoices = $sale_invoices->get();
        $sale_invoices = json_decode(json_encode($sale_invoices),true);
        return array('sale_invoices'=>$sale_invoices,'sale_invoice_ids'=>implode(',',array_column($sale_invoices,'sale_invoice_id')));
    }


    public static function billinvoices($invoiceno){
        $sale_invoices = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->join('products','products.id','=','sampling_sale_invoices.product_id')->join('sampling_items','sampling_items.id','=','sampling_sale_invoices.sampling_item_id')->where('sampling_sale_invoices.invoice_no',$invoiceno)->where('sampling_sale_invoices.transport_name','')->select('sampling_sale_invoices.id as sale_invoice_id','products.product_name','products.product_code','sampling_items.actual_pack_size','sampling_sale_invoices.qty');
        $sale_invoices = $sale_invoices->get();
        $sale_invoices = json_decode(json_encode($sale_invoices),true);
        return array('sale_invoices'=>$sale_invoices,'sale_invoice_ids'=>implode(',',array_column($sale_invoices,'sale_invoice_id')));
    }

    public static function dispatchedMaterials($invoiceno,$data){
        $sale_invoices = SamplingSaleInvoice::join('samplings','samplings.id','=','sampling_sale_invoices.sampling_id')->join('products','products.id','=','sampling_sale_invoices.product_id')->join('sampling_items','sampling_items.id','=','sampling_sale_invoices.sampling_item_id')->where('sampling_sale_invoices.invoice_no',$invoiceno)->where('sampling_sale_invoices.lr_no','!=','')->select('sampling_sale_invoices.id as sale_invoice_id','products.product_name','products.product_code','sampling_items.actual_pack_size','sampling_sale_invoices.qty','sampling_sale_invoices.batch_no','sampling_sale_invoices.price');
        if(isset($data['product_id'])&& !empty($data['product_id'])){
            $sale_invoices = $sale_invoices->where('sampling_sale_invoices.product_id',$data['product_id']);
        }
        if(isset($data['batch_no'])&& !empty($data['batch_no'])){
            $sale_invoices = $sale_invoices->where('sampling_sale_invoices.batch_no',$data['batch_no']);
        }
        $sale_invoices = $sale_invoices->get();
        $sale_invoices = json_decode(json_encode($sale_invoices),true);
        return array('sale_invoices'=>$sale_invoices,'sale_invoice_ids'=>implode(',',array_column($sale_invoices,'sale_invoice_id')));
    }
}
