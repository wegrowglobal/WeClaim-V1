<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        'role' => \App\Http\Middleware\CheckRole::class,
        'reset.claim' => \App\Http\Middleware\ResetClaimForm::class,
    ];
} 