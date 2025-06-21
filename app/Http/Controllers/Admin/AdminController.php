<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use App\Admin;
use App\Feedback;
use App\FeedbackReply;
use Auth; 
use DB;
use Cookie;
use Session;
use Crypt;
use Illuminate\Support\Facades\Mail;
use PDF;
use App\Module;
use App\AdminRole;
use Hash;
use App\Event;
use App\EventDetail;
use App\Dealership;
use App\Store;
use App\State;
use App\WebSetting;
use App\Cart;
use App\DealershipRequest;
use App\UserRole;
class AdminController extends Controller
{
    //
    public function status(Request $request){
        if(Auth::check()){
        	if($request->ajax()){
                $data = $request->input();
                if(DB::table($data['table'])->where('id', $data['id'])->update(['status' => $data['status'] ]) ){
                    echo "1";die;
                } else {
                    echo "0";die; 
                }
            }
        }
    }

    public function login(Request $request){
    	if(Auth::check()){
    		return redirect('admin/dashboard');
    	}
    	if($request->isMethod('post')){
    		$data = $request->all();
    		$rules = [
                'email' => 'bail|required|email|regex:/^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i|max:255',
                'password' => 'bail|required',
            ];
            $customMessages = [
            	//Add custom Messages here
            ];
            $this->validate($request, $rules, $customMessages);
    		if(Auth::attempt($request->only('email','password'))) {
    			if(Auth::user()->status ==0){
    				Auth::logout();
		    		return redirect()->back()->with('flash_message_error','Account deactivated');
    			}else{
			        return redirect('admin/dashboard')->with('flash_message_success','Logged in successfully');
    			}
		    }else{
		    	return redirect()->back()->with('flash_message_error','Invalid email or password');
		    }
    	}
    	return view('admin.admin_login');
    }

    public function checkAdminEmail(Request $request) {
        $data = $request->all();
        $email = $data['email'];
        $count = DB::table('users')
                       ->where('email', $email)
                       ->count();
        if($count == 1) {
            echo '{"valid":true}';die;
        } else {
            echo '{"valid":false}';die;
        }
    }

    public function dashboard(){
        /*$allDvrs = \App\Dvr::get()->toArray();
        foreach($allDvrs as $dvr){
            if(!empty($dvr['trial_details'])){
                $trial_details = json_decode($dvr['trial_details'], true, JSON_UNESCAPED_SLASHES);
                $trial_details = json_encode($trial_details);
                 \App\Dvr::where('id',$dvr['id'])->update(['trial_details'=>$trial_details]);
            }
        }
        echo "done"; die;*/

        Session::put('active',1);
        if(Auth::user()->type =="admin"){
            $getModules = DB::table('modules')->where('status',1)->where('table_name','!=','')->select('id','name','view_route','table_name','icon')->orderBy('sortorder','ASc')->get();
        }else{
            $getsubadminmodules = DB::table('user_roles')->where(['user_id'=>Auth::user()->id,'view_access'=>'1'])->select('module_id')->get();
            $getsubadminmodules = array_flatten(json_decode(json_encode($getsubadminmodules),true));
            $getModules = DB::table('modules')->where('status',1)->whereIn('id',$getsubadminmodules)->where('table_name','!=','')->select('id','name','view_route','table_name','icon')->orderBy('sortorder','ASc')->get();
        }
        $getModules = json_decode(json_encode($getModules),true);
        foreach ($getModules as $key => $module) {
            if($module['table_name']=="users"){
                $getModules[$key]['table_count'] = DB::table('users')->where('type','!=','admin')->count();
            }else{
                $getModules[$key]['table_count'] = DB::table($module['table_name'])->count();
            }
        }
        $title = "Dashboard";
        return view('admin.admin_dashboard')->with(compact('title','getModules'));
    }

    public function profile(Request $request){
        Session::put('active',3);
        $admindata = DB::table('users')->where('id', Auth::user()->id)->first();
        $admindata=json_decode( json_encode($admindata), true);
        $title = "Profile";
        return view('admin.profile', ['admindata'=>$admindata,'title'=>$title]);
    }

    public function logout(){
        Auth::logout();
        return redirect()->action('Admin\AdminController@login')->with('flash_message_success', 'Logged out successfully.');
       
    }

    public function settings(Request $request){
        Session::put('active',4);
        if($request->isMethod('post')){
            $data = $request->all();
            $this->validate($request, [
                'name'=>'required',
                'email'=>'required|email',
                'mobile'=>'required'
        ]);
        $update_data = DB::table('users')
            ->where('id', Auth::user()->id)
            ->update([
                'name'=>$data['name'],
                'email'=>$data['email'],
                'mobile'=>$data['mobile']]); 
            return redirect()->back()->with('flash_message_success','Profile has been updated successfully');
        } else{
            $admindata = DB::table('users')->where('id', Auth::user()->id)->first();
            $admindata =json_decode( json_encode($admindata), true);
            $title = "Account Settings";
            return view('admin.admin_accountSettings', ['admindata'=>$admindata,'title'=>$title]); 
        }
    }

    public function changeAdminLogo(Request $request){
    	if($request->isMethod('post')){
    		$image=$_FILES;
	        if($image['image']['error']==0){
	            $imgName = pathinfo($_FILES['image']['name']);
	            $ext = $imgName['extension'];
	            $NewImageName = rand(4,10000);
	            $destination = base_path() . '/public/images/AdminImages/';
	            if(move_uploaded_file($image['image']['tmp_name'],$destination.$NewImageName.".".$ext)){
	                if(file_exists($destination.Auth::user()->image) && !empty(Auth::user()->image)){
	                    unlink($destination.Auth::user()->image);
	                }  
	                $image =DB::table('users')
	                ->where('id', Auth::user()->id)
	                ->update(['image' => $NewImageName.".".$ext]);
	                if(!empty($image)){
	                   return redirect()->action('Admin\AdminController@profile')->with('flash_message_success', 'Image has been uploaded successfully');         
	                } else {
	                   return redirect('admin/settings/#tab_1_2')->with('flash_message_error', 'You have not Select any image'); 
	                }
	            }
	        }
	        else {
	            return redirect('admin/settings/#tab_1_2')->with('flash_message_error', 'You have not Select any image'); 
	        }
    	}
    }

    public function changeAdminPassword(Request $request){
        if($request->isMethod('post')){
            $data = $request->input();
            if(!empty($data)){
                if (Hash::check($data['password'], Auth::user()->password)){
                    if($data['new_password'] == $data['re_password']){
                            DB::table('users')
                            ->where('id', Auth::user()->id)
                            ->update(['password' => bcrypt($data['new_password'])]);  
                        return redirect('admin/settings/')->with('flash_message_success', 'Password has been updated successfully');   
                    }else{
                        return redirect('admin/settings/#tab_1_3')->with('flash_message_error', 'New password and Retype password not match'); 
                    }
                } else {
                    return redirect('admin/settings/#tab_1_3')->with('flash_message_error', 'Your current password is incorrect'); 
                }
            }
        }
    }

    public function qcfs(Request $Request){
        Session::put('active','qcfs'); 
        if(isset($_GET['type'])){
            Session::put('active','qcfs_customer'); 
        }
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = Feedback::with(['customer','dealer','product']);
            if(isset($_GET['type'])){
                $querys = $querys->where('submit_by',$_GET['type']);
            }else{
                $querys = $querys->where('submit_by','dealer');
            }
            if(!empty($data['name'])){
                $querys = $querys->where('name','like','%'.$data['name'].'%');
            }
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                        ->skip($iDisplayStart)->take($iDisplayLength)
                        ->OrderBy('feedback.id','DESC')
                        ->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $feedback){ 
                $actionValues='
                    <a title="Reply" class="btn btn-sm green margin-top-10" href="'.url('/admin/qcfs-reply/'.$feedback['id']).'">Reply</a>';
                $num = ++$i;
                $product_name = "";
                if(isset($feedback['product']['product_name'])){
                    $product_name = "<br>".$feedback['product']['product_name'];
                }
                $records["data"][] = array(      
                    $num,
                    $feedback['feedback_date'],
                    ucwords($feedback['dealer']['business_name']),
                    ucwords($feedback['customer']['name']),
                    $feedback['type'],  
                    (($feedback['is_product_related']==1)?'Yes': 'No').$product_name,  
                    $feedback['batch_no'], 
                    $feedback['remarks'],
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "QCFS";
        return View::make('admin.qcfs.qcfs')->with(compact('title'));
    }

    public function qcfsReply(Request $request,$qcfsid){
        if($request->isMethod('post')){
            $data = $request->all();
            Feedback::where('id',$qcfsid)->update(['admin_reply'=>$data['admin_reply']]);
            //Create logs
            $feedback_reply = new FeedbackReply;
            $feedback_reply->feedback_id = $qcfsid;
            $feedback_reply->reply = $data['admin_reply'];
            $feedback_reply->created_by = \Auth::user()->id;
            $feedback_reply->save();
            return redirect()->back()->with('flash_message_success','Reply has been added successfully');
        }
        $title = "QCFS";
        $qcfs_detail  = Feedback::with('replies')->where('id',$qcfsid)->first(); 
        return view('admin.qcfs.qcfs-reply')->with(compact('title','qcfs_detail'));
    } 
}
