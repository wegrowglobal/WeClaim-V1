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

            case 'admin.system-config':
                $breadcrumbs[] = [
                    'name' => 'System Configuration',
                    'url' => route('admin.system-config')
                ];
                break;

            case 'claims.resubmit':
            case 'claims.resubmit.process':
                $breadcrumbs[] = [
                    'name' => 'Claims',
                    'url' => route('claims.dashboard')
                ];
                if (isset($parameters['claim'])) {
                    $claim = $parameters['claim'];
                    $breadcrumbs[] = [
                        'name' => 'Claim #' . $claim->id,
                        'url' => route('claims.view', $claim->id)
                    ];
                    $breadcrumbs[] = [
                        'name' => 'Resubmit',
                        'url' => '#'
                    ];
                }
                break;

            case 'claims.resubmit.show':
                $breadcrumbs[] = [
                    'name' => 'Claims',
                    'url' => route('claims.dashboard')
                ];
                if (isset($parameters['claim'])) {
                    $claim = $parameters['claim'];
                    $breadcrumbs[] = [
                        'name' => 'Claim #' . $claim->id,
                        'url' => route('claims.view', $claim->id)
                    ];
                    $breadcrumbs[] = [
                        'name' => 'Resubmission Form',
                        'url' => '#'
                    ];
                }
                break;

            // For claim review pages
            if (url()->current() === route('claims.review', ['id' => $parameters['id'] ?? ''])) {
                // Check where we're coming from
                if (url()->previous() === route('admin.claims')) {
                    $breadcrumbs[] = [
                        'name' => 'Manage Claims',
                        'url' => route('admin.claims')
                    ];
                } else {
                    $breadcrumbs[] = [
                        'name' => 'Approval',
                        'url' => route('claims.approval')
                    ];
                }
                $breadcrumbs[] = [
                    'name' => 'Review Claim',
                    'url' => '#'
                ];
            }

            // For claim view pages
            if (url()->current() === route('claims.view', ['id' => $parameters['id'] ?? ''])) {
                // Check where we're coming from
                if (str_contains(url()->previous(), 'admin')) {
                    $breadcrumbs[] = [
                        'name' => 'Manage Claims',
                        'url' => route('admin.claims')
                    ];
                } else {
                    $breadcrumbs[] = [
                        'name' => 'Dashboard',
                        'url' => route('claims.dashboard')
                    ];
                }

                if (isset($parameters['id'])) {
                    $breadcrumbs[] = [
                        'name' => 'Claim #' . $parameters['id'],
                        'url' => '#'
                    ];
                }
            }

            // For user management pages
            if (url()->current() === route('admin.users')) {
                $breadcrumbs[] = [
                    'name' => 'Manage Users',
                    'url' => route('admin.users')
                ];
            }

            case 'admin.claims':
                $breadcrumbs[] = [
                    'name' => 'Claims Management',
                    'url' => route('admin.claims')
                ];
                break;

            case 'admin.users':
                $breadcrumbs[] = [
                    'name' => 'User Management',
                    'url' => route('admin.users')
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
