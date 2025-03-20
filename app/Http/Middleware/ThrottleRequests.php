<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottleRequests;

class ThrottleRequests extends BaseThrottleRequests
{
    // We extend the Laravel throttle requests middleware
    // and can customize its behavior if needed
    // For now we'll use the default implementation
} 