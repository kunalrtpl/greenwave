<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SamplingItem extends Model
{
    //
     public function requested_product(){
    	return $this->belongsTo('App\Product','requested_product_id')->select('id','product_detail_id','product_name','product_code','hsn_code','description','how_to_use','suggested_dosage','technical_literature','gots_certification','zdhc_certification','msds','current_stock','physical_form');
    }

    public function product(){
        return $this->belongsTo('App\Product','product_id')->select('id','product_detail_id','product_name','product_code','hsn_code','description','how_to_use','suggested_dosage','technical_literature','gots_certification','zdhc_certification','msds','current_stock','physical_form');
    }

    public function sampling(){
    	return $this->belongsTo('App\Sampling');
    }

    public function sale_invoice_items(){
        return $this->hasMany('App\SamplingSaleInvoice')->with('productinfo');
    }

    public static function details($id){
    	$orderitemDetails = SamplingItem::with('sampling')->where('id',$id)->first();
        $orderitemDetails = json_decode(json_encode($orderitemDetails),true);
        return $orderitemDetails;
    }
}
