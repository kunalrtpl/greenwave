<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SaleInvoiceItem;
class PurchaseOrderItem extends Model
{
    //

    public function product(){
    	return $this->belongsTo('App\Product')->select('id','product_detail_id','product_name','product_code','hsn_code','description','how_to_use','suggested_dosage','technical_literature','gots_certification','zdhc_certification','msds','current_stock','moq','average_dispatch_time');
    }

    public function packingsize(){
    	return $this->belongsto('App\PackingSize','packing_size_id','id')->select('id','type','size');
    }

    public static function details($id){
    	$orderitemDetails = PurchaseOrderItem::where('id',$id)->first();
        $orderitemDetails = json_decode(json_encode($orderitemDetails),true);
        return $orderitemDetails;
    }

    public static function po_item_sale_qty($poitemid){
        $qty = SaleInvoiceItem::where('purchase_order_item_id',$poitemid)->sum('qty');
        return $qty;
    }

    public function sale_invoice_items(){
        return $this->hasMany('App\SaleInvoiceItem','purchase_order_item_id','id');
    }

    public function purchase_order(){
        return $this->belongsto('App\PurchaseOrder','purchase_order_id','id');
    }
}
