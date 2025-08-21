<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerPurchaseReturn extends Model
{
    //
	public function items(){
		return $this->hasMany('App\CustomerPurchaseReturnItem','customer_purchase_return_id','id')->with('product');
	}

	public function customer(){
    	return $this->belongsTo('App\Customer');
    }

    public function linked_employee(){
        return $this->belongsTo('App\User','linked_employee_id','id')->select('id','name','designation','email','mobile');
    }

    public function dealer(){
        return $this->belongsTo('App\Dealer');
    }

    public static function cprEntries($data,$resp){
        $entries = CustomerPurchaseReturn::with(['items','customer','dealer','linked_employee'])->whereDate('return_date','>=',$data['start_date'])->whereDate('return_date','<=',$data['end_date']);
        if(isset($resp['dealer']['id'])){
            $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
            //$entries = $entries->where(['dealer_id'=>$resp['dealer']['id']]);
            $entries = $entries->whereIn('dealer_id',$dealerIds);
        }
        if(isset($data['customer_id'])){
            $entries = $entries->where(['customer_id'=>$data['customer_id']]);
        }
        if(isset($data['customer_ids'])){
            $entries = $entries->wherein('customer_id',$data['customer_ids']);
        }
        $entries = $entries->get();
        return $entries;
    }
}
