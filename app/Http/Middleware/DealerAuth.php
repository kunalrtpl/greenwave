<?php

namespace App\Http\Middleware;

use Closure;

class DealerAuth
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
        if($request->header('Authorization')){
            $token = $request->header('Authorization');
            $resp = \App\AuthToken::verifyUser($token);
            if($resp['status'] && isset($resp['dealer'])) {
                return $next($request);
            }else{
                $message = "You have no right to access this page. Kindly contact system administrator";
                $redirect_to = "login_screen";
                return response()->json(apiErrorResponse($message,422,$redirect_to),422);
            }
        }
        return response()->json([
            'status' =>  false,
            'message' => 'Authorization token is expired or missing',
        ]);
    }
}
