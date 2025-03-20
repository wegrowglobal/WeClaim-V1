<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }

        $user = Auth::user();
        
        if (!$user->email_verified_at) {
            // For API requests, return a JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your email address is not verified.',
                ], 403);
            }
            
            // Add flash message to session
            return redirect()->route('home')
                ->with('warning', 'Your email address is not verified. Please verify your email address to access this feature.');
        }

        return $next($request);
    }
} 