<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Claim;

class BreadcrumbsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {
            $breadcrumbs = $this->generateBreadcrumbs();
            $view->with('breadcrumbs', $breadcrumbs);
        });
    }

    protected function generateBreadcrumbs()
    {
        $route = request()->route();
        if (!$route) {
            return $this->getDefaultBreadcrumbs();
        }

        $routeName = $route->getName();
        $parameters = $route->parameters();

        // Start with Dashboard
        $breadcrumbs = [[
            'name' => 'Dashboard',
            'url' => route('home')
        ]];

        // Handle different routes
        switch ($routeName) {
            case 'claims.dashboard':
                $breadcrumbs[] = [
                    'name' => 'Claims',
                    'url' => route('claims.dashboard')
                ];
                break;

            case 'claims.view':
                // Check if coming from admin page using previous URL
                if (url()->previous() === route('claims.admin')) {
                    $breadcrumbs[] = [
                        'name' => 'Claims Management',
                        'url' => route('claims.admin')
                    ];
                } else {
                    $breadcrumbs[] = [
                        'name' => 'Claims',
                        'url' => route('claims.dashboard')
                    ];
                }

                if (isset($parameters['id'])) {
                    $breadcrumbs[] = [
                        'name' => 'Claim #' . $parameters['id'],
                        'url' => '#'
                    ];
                }
                break;

            case 'claims.review':
                $breadcrumbs[] = [
                    'name' => 'Claims Approval',
                    'url' => route('claims.approval')
                ];
                if (isset($parameters['id'])) {
                    $breadcrumbs[] = [
                        'name' => 'Claim #' . $parameters['id'],
                        'url' => '#'
                    ];
                }
                break;

            case 'claims.new':
                $breadcrumbs[] = [
                    'name' => 'New Claim',
                    'url' => '#'
                ];
                break;

            case 'claims.approval':
                $breadcrumbs[] = [
                    'name' => 'Approval',
                    'url' => '#'
                ];
                break;

            case 'profile':
                $breadcrumbs[] = [
                    'name' => 'Profile',
                    'url' => '#'
                ];
                break;

            case 'notifications':
                $breadcrumbs[] = [
                    'name' => 'Notifications',
                    'url' => '#'
                ];
                break;

            case 'claims.admin':
                $breadcrumbs[] = [
                    'name' => 'Claims Management',
                    'url' => route('claims.admin')
                ];
                break;

            case 'users.management':
                $breadcrumbs[] = [
                    'name' => 'User Management',
                    'url' => route('users.management')
                ];
                break;
        }

        return array_values(array_filter($breadcrumbs));
    }

    protected function getDefaultBreadcrumbs()
    {
        return [[
            'name' => 'Dashboard',
            'url' => route('home')
        ]];
    }
}
