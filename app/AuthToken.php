<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Customer;
class AuthToken extends Model
{
    //
	protected $fillable = [
        'id','type','dealer_id','customer_id','customer_employee_id','auth_token','notification_token','login_device','user_id','app_details','created_at','updated_at'
    ];

    public static function verifyUser($token){
    	$explodeToken = explode('Bearer ',$token);
    	if(isset($explodeToken[1])){
            $decryptToken = explode('##-',decrypt($explodeToken[1]));
            $type = $decryptToken[0];
    		$details = AuthToken::where('auth_token',trim($explodeToken[1]))->where('type',$type)->first();
    		if($details){
                if($type=="dealer"){
                    $dealerinfo = Dealer::with(['contact_persons','linked_products','parent_dealer_info'])->where('id',$details->dealer_id)->where('status',1)->first();
    				$dealerinfo = json_decode(json_encode($dealerinfo),true);
                    if(!empty($dealerinfo)){
    				    $response = array('status'=>true,'message'=>'ok','type'=>$type,'dealer'=>$dealerinfo,'token'=>$explodeToken[1]);
                    }else{
                        $response = array('status'=>false,'message'=>'Authorization Token expired');
                    }
                }elseif($type=="customer"){
                    $customerInfo = Customer::with(['corporate_discount','user_customer_shares'=>function($query){
                    $query->with('user');
                    },'dealer','employees','product_discounts'])->where('id',$details->customer_id)->first();
                    $customerInfo = json_decode(json_encode($customerInfo),true);
                    $response = array('status'=>true,'message'=>'ok','type'=>$type,'customer'=>$customerInfo,'token'=>$explodeToken[1]);
                }elseif($type=="customer-employee"){
                    $customerInfo = CustomerEmployee::where('id',$details->customer_employee_id)->first();
                    $customerInfo = json_decode(json_encode($customerInfo),true);
                    $response = array('status'=>true,'message'=>'ok','type'=>$type,'customer_employee'=>$customerInfo,'token'=>$explodeToken[1]);
                }elseif($type=="user"){
                    $userinfo = User::where('id',$details->user_id)->where('status',1)->where('app_access','Yes')->first();
                    $userinfo = json_decode(json_encode($userinfo),true);
                    if(!empty($userinfo)){
                        $response = array('status'=>true,'message'=>'ok','type'=>$type,'user'=>$userinfo,'token'=>$explodeToken[1]);
                    }else{
                        $response = array('status'=>false,'message'=>'Authorization Token expired');
                    }
                }
    		}else{
    			$response = array('status'=>false,'message'=>'Authorization Token expired');
    		}
    	}else{
    		$response = array('status'=>false,'message'=>'Authorization Token expired');
    	}
    	return $response;
    }
}
