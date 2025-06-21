<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class LogApiRequest
{
    public function handle($request, Closure $next)
    {
        // Store request data before handling
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => json_encode($request->headers->all()),
            'request_body' => json_encode($request->all()),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Pass request to next middleware/controller
        $response = $next($request);

        // Add response data after handling
        $logData['response_body'] = $response->getContent();
        $logData['status_code'] = $response->getStatusCode();

        // Save log to DB
        DB::table('api_logs')->insert($logData);

        return $response;
    }
}
