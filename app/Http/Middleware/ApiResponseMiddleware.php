<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request and ensure consistent API response format.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add Accept header for JSON responses
        $request->headers->set('Accept', 'application/json');
        
        // Process the request
        $response = $next($request);
        
        // Only modify JSON responses
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            
            // Ensure the response has a consistent structure
            $formattedData = [
                'success' => $response->getStatusCode() < 400,
                'status_code' => $response->getStatusCode(),
                'message' => $data['message'] ?? ($response->getStatusCode() < 400 ? 'Success' : 'Error'),
                'data' => isset($data['data']) ? $data['data'] : 
                          (array_key_exists('message', $data) ? array_diff_key($data, ['message' => '']) : $data),
            ];
            
            // Add pagination data if available
            if (isset($data['meta']) && isset($data['meta']['pagination'])) {
                $formattedData['pagination'] = $data['meta']['pagination'];
            }
            
            // Add errors if available
            if (isset($data['errors'])) {
                $formattedData['errors'] = $data['errors'];
            }
            
            $response->setData($formattedData);
        }
        
        return $response;
    }
}