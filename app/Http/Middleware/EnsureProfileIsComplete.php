<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Handle an incoming request and ensure the user's profile is complete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $requiredFields = ['first_name', 'email', 'phone', 'department_id'];
            
            foreach ($requiredFields as $field) {
                if (empty($user->$field)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Profile incomplete',
                            'message' => 'You need to complete your profile before accessing this feature',
                            'redirect' => route('profile')
                        ], 403);
                    }
                    
                    return redirect()->route('profile')
                        ->with('error', 'Please complete your profile before continuing.');
                }
            }
            
            // Check for banking information if needed
            if (in_array($request->route()->getName(), [
                'claims.new', 
                'claims.store', 
                'claims.resubmit',
                'claims.resubmit.process'
            ])) {
                if (!$user->bankingInformation) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Banking information missing',
                            'message' => 'You need to add your banking information before submitting claims',
                            'redirect' => route('profile')
                        ], 403);
                    }
                    
                    return redirect()->route('profile')
                        ->with('error', 'Please add your banking information before submitting claims.');
                }
            }
        }

        return $next($request);
    }
} 