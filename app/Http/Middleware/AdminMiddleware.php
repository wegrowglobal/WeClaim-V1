<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login.form');
        }

        $user = Auth::user();
        
        // Check if user is admin (role_id 5)
        if ($user->role_id !== 5) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Administrator access required.'], 403);
            }
            
            return redirect()->route('home')->with('error', 'You do not have permission to access this page. Administrator access required.');
        }
        
        return $next($request);
    }
} 