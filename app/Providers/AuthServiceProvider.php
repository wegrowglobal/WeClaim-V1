<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User\User;
use App\Policies\SecurityPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // No model-to-policy mappings needed for this policy
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register security policy
        Gate::define('view-failed-logins', [SecurityPolicy::class, 'viewFailedLogins']);
    }
} 