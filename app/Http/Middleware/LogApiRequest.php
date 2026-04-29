<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogApiRequest
{
    public function handle($request, Closure $next)
    {
        $logData = [
            'method'       => $request->method(),
            'url'          => $request->fullUrl(),
            'headers'      => json_encode($request->headers->all()),
            'request_body' => json_encode($request->all()),
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        $response = $next($request);

        $responseContent = $response->getContent();

        // Truncate if response is too large (limit: 64KB)
        $maxSize = 65535;
        if (strlen($responseContent) > $maxSize) {
            $responseContent = substr($responseContent, 0, $maxSize);
        }

        $logData['response_body'] = $responseContent;
        $logData['status_code']   = $response->getStatusCode();

        try {
            DB::table('api_logs')->insert($logData);
        } catch (\Exception $e) {
            Log::error('API Log insert failed: ' . $e->getMessage());
        }

        return $response;
    }
}