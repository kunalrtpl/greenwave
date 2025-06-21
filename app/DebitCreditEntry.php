<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Dealer;
class DebitCreditEntry extends Model
{
    //
	public function customer(){
    	return $this->belongsTo('App\Customer');
    }
    
    public function dealer(){
        return $this->belongsTo('App\Dealer');
    }

    public static function entries($data,$resp){
        $entries = DebitCreditEntry::with(['customer','dealer'])->whereDate('entry_date','>=',$data['start_date'])->whereDate('entry_date','<=',$data['end_date']);
        if(isset($resp['dealer']['id'])){
            $dealerIds = Dealer::getParentChildDealers($resp['dealer']);
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
