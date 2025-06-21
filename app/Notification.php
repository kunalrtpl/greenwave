<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    public static function sendPushNotification($registrationIds,$messageDetails){
		$msg['body'] = $messageDetails['body'];
		$msg['title'] = $messageDetails['title'];
		$msg['icon'] = config('constants.notification_icon');
		$msg['click_action'] =  $messageDetails['click_action'];
		$msg['sound'] = "default";
		$fields = array(
		            'registration_ids'  => $registrationIds,
		            'notification'      => $msg,
		        );
		$headers = array(
		            'Authorization: key=' . 'AAAAUqKJYY0:APA91bEhxt8yEKPIrwFeU02MsNntW2htt79z_pC-qdUgGKdCsRTPYx6TwFe5gh8wANhxOgjZjDsUbOYUjk6PnQXOOgo2DivLp_BYjwowxO664OPyHoeJGvmOELaX9JqHuYfrlIp6de6l	',
		            'Content-Type: application/json'
		        );

		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );
		//echo "<pre>"; print_r($result); die;
		return 'ok';
	}

	public static function sendAppPushNotification($tokens,$messageDetails){
		$registrationIds = $tokens;
		$msg['body'] = $messageDetails['body'];
		$msg['title'] = $messageDetails['title'];
		$msg['sound'] = "default";
		$msg['priority'] = "high";
		$msg['content_available'] = true;
		$msg['mutable_content'] = true;
		if(isset($messageDetails['image']) && !empty($messageDetails['image'])){
			$msg['image'] = $messageDetails['image'];
		}
		$fields = array(
		            'registration_ids'  => $registrationIds,
		            'notification'      => $msg,
		            'data'              => $msg,
		        );
		$headers = array(
		            'Authorization: key=' . 'AAAAj10-hMM:APA91bHYpArUbBWxHD2h1PGYfxO7sxq2OtkSFd9waXSlGbG524HimLJ8l8f-rsTSaKZ87mvAioo0a7Kn7maptFow63jgLBbKJWH1tN13RJ--DHygggOmehfpLIqHGCeZpATBmMsk7lOA',
		            'Content-Type: application/json'
		        );

		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );
		return $result;
	} 
}
