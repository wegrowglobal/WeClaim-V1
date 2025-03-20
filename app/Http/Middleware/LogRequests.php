<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request and log its details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Record the start time for performance tracking
        $startTime = microtime(true);
        
        // Process the request
        $response = $next($request);
        
        // Calculate execution time
        $executionTime = microtime(true) - $startTime;
        
        // Log the request details
        Log::info('HTTP Request', [
            'uri' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => Auth::id() ?? 'guest',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $response->getStatusCode(),
            'execution_time' => round($executionTime * 1000, 2) . 'ms', // Convert to milliseconds
            'route_name' => $request->route() ? $request->route()->getName() : 'unknown'
        ]);
        
        return $response;
    }
} 