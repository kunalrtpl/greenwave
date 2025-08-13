<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealerPurchaseReturn extends Model
{
    //
    public function items(){
    	return $this->hasMany('App\DealerPurchaseReturnItem','dealer_purchase_return_id','id')->with('productinfo');
    }


    public static function entries($data,$resp){
        $entries = DealerPurchaseReturn::with(['items'])->whereDate('return_date','>=',$data['start_date'])->whereDate('return_date','<=',$data['end_date']);
        if(isset($resp['dealer']['id'])){
            $dealerIds = \App\Dealer::getParentChildDealers($resp['dealer']);
            //$entries = $entries->where(['dealer_id'=>$resp['dealer']['id']]);
            $entries = $entries->whereIn('dealer_id',$dealerIds);
        }
        $entries = $entries->get();
        return $entries;
    }
}
