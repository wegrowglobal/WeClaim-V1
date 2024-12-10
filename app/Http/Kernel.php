<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        'role' => \App\Http\Middleware\CheckRole::class,
        'superuser' => \App\Http\Middleware\SuperUserMiddleware::class,
    ];

    protected $middlewareAliases = [
        'superuser' => \App\Http\Middleware\SuperUserMiddleware::class,
    ];
}
