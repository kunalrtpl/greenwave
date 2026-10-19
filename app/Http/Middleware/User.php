<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Closure;
use Session;
use Auth;
use App\UserRole;
use App\Module;
use DB;
use Redirect;
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

                /*
                 | Several modules can point at the same route with different
                 | query strings — the sampling request screens are one screen
                 | listed four times as ?status=pending|approved|closed|declined.
                 | So match on the path and let access to any of them through.
                 */
                $modules = Module::where(function ($query) use ($currentUrl) {
                    foreach (['view_route', 'edit_route', 'delete_route'] as $column) {
                        $query->orWhere($column, $currentUrl)
                              ->orWhere($column, 'like', $currentUrl . '?%');
                    }
                })->get();

                if($modules->count()){
                    $allowed = false;

                    foreach($modules as $module){
                        $userRoleDetail = UserRole::where(['user_id'=>Auth::user()->id,'module_id' => $module->id])->first();

                        // A module the role was never configured for stays open,
                        // which is how this check has always behaved.
                        if(empty($userRoleDetail)){
                            $allowed = true;
                            break;
                        }
                        if($this->routeMatches($currentUrl, $module->view_route) && $userRoleDetail->view_access != 0){
                            $allowed = true;
                            break;
                        }
                        if($this->routeMatches($currentUrl, $module->edit_route) && $userRoleDetail->edit_access != 0){
                            $allowed = true;
                            break;
                        }
                        if($this->routeMatches($currentUrl, $module->delete_route) && $userRoleDetail->delete_access != 0){
                            $allowed = true;
                            break;
                        }
                    }

                    if(!$allowed){
                        return redirect('/admin/dashboard')->with('flash_message_error','You have no right to access this functionality');
                    }
                }
            }

             if (Auth::check()) {
                // Always fetch the fresh user record
                $user = Auth::user()->fresh();

                if ($user->web_access === 'No') {
                    Auth::logout();
                    return redirect::to('/admin')->with('flash_message_error', 'Your web access has been revoked.');
                }
            }
        }
        return $next($request);
    }

    /** True when a module route points at this path, with or without a query string. */
    private function routeMatches($currentUrl, $moduleRoute)
    {
        if (empty($moduleRoute)) {
            return false;
        }
        return strtok($moduleRoute, '?') === $currentUrl;
    }
}
