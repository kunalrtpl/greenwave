<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use App\Sampling;
use App\PurchaseOrder;
use App\SamplingItem;
class Module extends Model
{
    //
    protected $fillable = [
        'id','name', 'parent_id', 'view_route','edit_route','delete_route','icon','session_value','status','sortorder','shown_in_roles','table_name','created_at','updated_at'
    ];
    public static function getModules(){
        if(Auth::user()->type=="admin"){
            $allModules = Module::with('undermodules')->where('status',1)->orderby('sortorder','ASC')->where('status',1)->where('parent_id','ROOT')->get();
            $allModules = json_decode(json_encode($allModules),true);
            return $allModules;
        }else{
            $getEmpModules = DB::table('user_roles')->where(['user_id'=>Auth::user()->id,'view_access'=>'1'])->select('module_id')->get();
            $getEmpModules = array_flatten(json_decode(json_encode($getEmpModules),true));
            $allModules = Module::with(['undermodules'=>function($query) use($getEmpModules){
                $query->whereIn('id',$getEmpModules);
            }])->where('status',1)->where('parent_id','ROOT')->orderby('sortorder','ASC')->get();
            $allModules = json_decode(json_encode($allModules),true);
            return $allModules;
        }
    }

    public function undermodules(){
        return $this->hasMany('App\Module','parent_id')->orderby('sortorder','asc')->where('status',1);
    }


    public static function getPendingCounts($sesionValue){
        $count = 0;
        $getDirectCustomerPoids = PurchaseOrder::whereNUll('dealer_id')->wherein('action',['customer','customer_employee'])->pluck('id')->toArray();
        $getDealerPoids = PurchaseOrder::wherein('action',['dealer'])->pluck('id')->toArray();
        $poids = array_merge($getDirectCustomerPoids,$getDealerPoids);
        if($sesionValue=="customerRegisterRequests"){
            $count = \App\CustomerRegisterRequest::where('status','Pending')->count();
        }if($sesionValue=="dealerOrders"){
            $count = \App\PurchaseOrder::where('po_status','pending')->wherein('purchase_orders.action',['dealer'])->count();
        }elseif($sesionValue=="directCustomerOrders"){
            $count = \App\PurchaseOrder::where('po_status','pending')->wherein('purchase_orders.action',['customer'])->whereNull('purchase_orders.dealer_id')->count();
        }elseif($sesionValue=="POdispatchPlanning"){
            $count = PurchaseOrderItem::join('purchase_orders','purchase_orders.id','=','purchase_order_items.purchase_order_id')->where('purchase_orders.po_status','approved')->whereColumn('purchase_order_items.actual_qty','!=','purchase_order_items.dispatched_qty')->wherein('purchase_order_items.purchase_order_id',$poids)->count();
        }elseif($sesionValue=="freeSampling"){
            $count = Sampling::where('sample_type','free')->where('sample_status','pending')->count();
        }elseif($sesionValue=="freeSampling"){
            $count = Sampling::where('sample_type','free')->where('sample_status','pending')->count();
        }elseif($sesionValue=="paidSampling"){
            $count = Sampling::where('sample_type','paid')->where('sample_status','pending')->count();
        }elseif($sesionValue=="samplingDispatchPlanning"){
            $count = SamplingItem::join('samplings','samplings.id','=','sampling_items.sampling_id')->where('samplings.sample_status','approved')->whereColumn('sampling_items.actual_qty','!=','sampling_items.dispatched_qty')->count();
        }
        return $count;
    }
}
