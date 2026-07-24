<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return response()->json('OK', 200, [
                'Access-Control-Allow-Origin'      => '*',
                'Access-Control-Allow-Methods'     => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Accept',
                'Access-Control-Max-Age'           => '86400',
            ]);
        }

        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin',  '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');

        return $response;
    }
}