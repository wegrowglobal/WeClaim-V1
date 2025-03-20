<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TrackUserActivity
{
    /**
     * Handle an incoming request and track user activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Store user's last activity time in cache for 5 minutes
            $expiresAt = Carbon::now()->addMinutes(5);
            Cache::put('user-online-' . $user->id, true, $expiresAt);
            
            // Optionally, update the user's last_active_at field in the database
            // if you want to store this information persistently
            /*
            $user->update([
                'last_active_at' => Carbon::now()
            ]);
            */
        }

        return $next($request);
    }
}