<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Symfony\Component\HttpFoundation\Response;

class CheckForMaintenanceMode extends Middleware
{
    /**
     * The URIs that should be accessible while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/login',
        '/logout',
        '/coming-soon',
        '/password/*',
        '/admin/*',
        '/register/*',
        '/claims/email-action/*',
        // API endpoints that should remain accessible
        'api/*',
    ];
    
    /**
     * Handle an incoming request during maintenance mode, with special access for admins.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If the app is not in maintenance mode, proceed normally
        if (!App::isDownForMaintenance()) {
            return $next($request);
        }
        
        // Always let admin users through during maintenance mode
        if ($request->user() && $request->user()->role_id === 5) {
            return $next($request);
        }
        
        // Use parent handler for other cases (uses the except array)
        return parent::handle($request, $next);
    }
} 