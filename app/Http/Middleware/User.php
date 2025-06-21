<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Closure;
use Session;
use Auth;
use App\UserRole;
use App\Module;
use DB;
class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!Auth::check()) {
            return redirect('/admin')->with('flash_message_error', 'Please Login');
        }else{
            if(Auth::user()->type !="admin"){
                $currentUrl = Route::getFacadeRoot()->current()->uri();
                $moduleDetail = Module::orwhere('view_route',$currentUrl)->orwhere('edit_route',$currentUrl)->orwhere('delete_route',$currentUrl)->first();
                $moduleDetail = json_decode(json_encode($moduleDetail),true);
                if(!empty($moduleDetail)){
                    $userRoleDetail = UserRole::where(['user_id'=>Auth::user()->id,'module_id' => $moduleDetail['id']])->first();
                    $userRoleDetail = json_decode(json_encode($userRoleDetail),true);
                    if(!empty($userRoleDetail) && !empty($moduleDetail)){
                        if($currentUrl == $moduleDetail['view_route'] && $userRoleDetail['view_access'] == 0){
                            return redirect('/admin/dashboard')->with('flash_message_error','You have no right to access this functionality');
                        }
                        if($currentUrl == $moduleDetail['edit_route'] && $userRoleDetail['edit_access'] == 0){
                            return redirect('/admin/dashboard')->with('flash_message_error','You have no right to access this functionality');
                        }
                        if($currentUrl == $moduleDetail['delete_route'] && $userRoleDetail['delete_access'] == 0){
                            return redirect('/admin/dashboard')->with('flash_message_error','You have no right to access this functionality');
                        }
                    }
                }else{
                    $moduleDetail = DB::table('modules')->orwhere('view_route',$currentUrl)->orwhere('edit_route',$currentUrl)->orwhere('delete_route',$currentUrl)->first();
                        $moduleDetail = json_decode(json_encode($moduleDetail),true);
                        if(!empty($moduleDetail)){
                            return redirect('/admin/dashboard')->with('flash_message_error','You have no right to access this functionality');
                        }
                }
            }
        }
        return $next($request);
    }
}
