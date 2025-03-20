<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  $roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login.form');
        }

        $user = Auth::user();
        
        // If no specific roles are required or user is admin (role_id 5), allow access
        if (empty($roles) || $user->role_id === 5) {
            return $next($request);
        }
        
        // Convert role names or IDs to array for checking
        $roleIds = [];
        foreach ($roles as $role) {
            if (is_numeric($role)) {
                $roleIds[] = (int) $role;
            } else {
                // If role name is provided, we would need to convert it to ID
                // But for simplicity, we'll assume role IDs are passed
                $roleIds[] = (int) $role;
            }
        }
        
        if (in_array($user->role_id, $roleIds)) {
            return $next($request);
        }
        
        // If AJAX request, return 403 error
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized. You do not have the required role.'], 403);
        }
        
        // Redirect with error message
        return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
    }
} 