<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Notification;
use App\Customer;
use App\Dealer;
use App\AuthToken;
use Auth;
use Session;
use Illuminate\Support\Facades\View;
use Validator;
class NotificationController extends Controller
{
    //
	public function saveNotificationToken(Request $request){
		$data = $request->all();
		if(Auth::user()->notification_token != $data['notification_token']){
			$saveToken = User::find(Auth::user()->id);
			$saveToken->notification_token = $data['notification_token'];
			$saveToken->save();
			$messageDetails['title'] = "Greenwave";
			$messageDetails['body'] = "Welcome to Greenwave";
			$messageDetails['click_action'] = url('admin/dashboard');
			$tokens = array($data['notification_token']);
			Notification::sendPushNotification($tokens,$messageDetails);
		} 
	}

	public function sendNotifications(){
		$tokens = User::where('notification_token','!=','')->pluck('notification_token')->toArray();
		$messageDetails['title'] = "Greenwave";
		$messageDetails['body'] = "Welcome to Greenwave";
		$messageDetails['click_action'] = url('admin/dashboard');
		$tokens = $tokens;
		Notification::sendPushNotification($tokens,$messageDetails);
	}

	public function notifications(Request $Request){
        Session::put('active','notifications'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Notification::query();
            if(!empty($data['title'])){
                $querys = $querys->where('title','like','%'.$data['title'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $notification){
                $checked='';
                if($notification['status']==1){
                    $checked='on';
                }else{
                    $checked='off';
                }
                $actionValues='
                    <a title="Edit" class="btn btn-sm green margin-top-10" href="'.url('/admin/add-edit-notification/'.$notification['id']).'"> <i class="fa fa-edit"></i>
                    </a>';
                $num = ++$i;
                $records["data"][] = array(      
                    $notification['id'],
                    $notification['title'],
                    $notification['body'],
                    $notification['type'],
                    '<div  id="'.$notification['id'].'" rel="notifications" class="bootstrap-switch  bootstrap-switch-'.$checked.'  bootstrap-switch-wrapper bootstrap-switch-animate toogle_switch">
                    <div class="bootstrap-switch-container" ><span class="bootstrap-switch-handle-on bootstrap-switch-primary">&nbsp;Active&nbsp;&nbsp;</span><label class="bootstrap-switch-label">&nbsp;</label><span class="bootstrap-switch-handle-off bootstrap-switch-default">&nbsp;Inactive&nbsp;</span></div></div>',   
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Notifications";
        return View::make('admin.notifications.notifications')->with(compact('title'));
    }

    public function addEditNotification(Request $request,$notificationid=NULL){
    	if(!empty($notificationid)){
    		$notifydata = Notification::where('id',$notificationid)->first();
    		$title ="Edit Notification";
    	}else{
    		$title ="Add Notification";
	    	$notifydata =array();
    	}
    	return view('admin.notifications.add-edit-notification')->with(compact('title','notifydata'));
    }

    public function saveNotification(Request $request){
    	try{
            if($request->ajax()){
                $data = $request->all();
                if($data['notificationid']==""){
                    $type ="add";
                }else{ 
                    $type ="update";
                }
                $validator = Validator::make($request->all(), [
                        'title' => 'bail|required',
                        'body' => 'bail|required',
                        'status' => 'bail|required',
                        'type' => 'bail|required',
                    ]
                );
                if($validator->passes()) {
                    $data = $request->all();
                    if($type =="add"){
                        $notification = new Notification; 
                    }else{
                        $notification = Notification::find($data['notificationid']); 
                    }
                    $notification->title = $data['title'];
                    $notification->body = $data['body'];
                    $notification->status = $data['status'];
                    $notification->type = $data['type'];
                    if($request->hasFile('image')){
                        $file = $request->file('image');
                        $img = Image::make($file);
                        $destination = public_path('/images/NotificationImages/');
                        $ext = $file->getClientOriginalExtension();
                        $mainFilename = str_random(5).time().".".$ext;
                        $img->save($destination.$mainFilename);
                        $notification->image = $mainFilename;
                    }
                    $notification->save();
                    $redirectTo = url('/admin/notifications?s');
                    return response()->json(['status'=>true,'message'=>'ok','url'=>$redirectTo]);
                }else{
                    return response()->json(['status'=>false,'errors'=>$validator->messages()]);
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>false,'message'=>$e->getMessage(),'errors'=>array('name'=>$e->getMessage())]);
        }
    }

    public function sendPushnotification(Request $request){
    	Session::put('active','push-notify'); 
    	$title="Send Push Notification";
    	$notifications = Notification::where('status',1)->orderBy('id','DESC')->select('id','title','type')->limit(100)->get();
    	$notifications = json_decode(json_encode($notifications),true);
    	return view('admin.notifications.send-push-notification')->with(compact('title','notifications'));
    }

    public function processNotification(Request $request){
        if($request->ajax()){
            $data = $request->all();
            $notify_detail = Notification::where('id',$data['notification_id'])->first();
            $notify_detail = json_decode(json_encode($notify_detail),true);
            $take = 200;
            if($notify_detail['type'] =="dealer"){
                $tokens = AuthToken::where('type','dealer');
            }elseif($notify_detail['type'] =="customer"){
                $tokens = AuthToken::where('type','customer');
            }
            $tokens = $tokens->where('login_device',$data['sendto'])->skip($data['skip'])->take($take)->where('notification_token','!=','')->pluck('notification_token')->toArray();
            if(count($tokens) >0){
                $messageDetails['body'] = $notify_detail['body'];
                $messageDetails['title'] = $notify_detail['title'];
                $messageDetails['body'] = $notify_detail['body'];
                $messageDetails['info']['redirect_to'] = $notify_detail['type'];
                if($data['sendto'] =="android"){
                    //Send Notification to Android Users
                    Notification::sendAppPushNotification($tokens,$messageDetails);
                }else{
                    //Send Notification to IOS Users
                    Notification::sendAppPushNotification($tokens,$messageDetails);
                }
                return response()->json([
                    'status' =>true,
                    'having_more_data' => 'yes',
                    'skip' => $data['skip'] +  $take,
                    'notification_id' => $data['notification_id'],
                    'sendto' => $data['sendto'],
                ]);
            }else{
                return response()->json([
                    'status' =>true,
                    'having_more_data' => 'no',
                ]);
            }
        }
    }
}
