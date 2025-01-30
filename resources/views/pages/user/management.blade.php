@extends('layouts.app')

@section('content')
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 animate-slide-in">
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="mt-2 text-sm text-gray-500">Manage system users and their access</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8 animate-slide-in delay-100">
            <!-- Total Users -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Total Users</p>
                            <p class="text-lg font-semibold text-indigo-600">{{ $users->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Active Users</p>
                            <p class="text-lg font-semibold text-green-600">{{ $users->where('status', 'active')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-500">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Pending Requests</p>
                            <p class="text-lg font-semibold text-yellow-600">{{ $pendingRequests->where('status', 'pending')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Toggle Card -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm mb-6 animate-slide-in delay-150">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                <div class="flex items-center space-x-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">View Options</p>
                        <p class="text-xs text-gray-500">Switch between different user views</p>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="switchView('registered')"
                        class="user-management-toggle-btn active flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all relative flex-1 sm:flex-initial
                        bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Registered Users</span>
                    </button>

                    <button onclick="switchView('pending')"
                        class="user-management-toggle-btn flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-all relative flex-1 sm:flex-initial
                        border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>Pending Requests</span>
                        @if ($pendingRequests->where('status', 'pending')->count() > 0)
                            <span class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-semibold text-white ring-2 ring-white">
                                {{ $pendingRequests->where('status', 'pending')->count() }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Table Section -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm animate-slide-in delay-200" id="registeredUsersView">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                <div class="flex items-center space-x-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M4 6h16M4 12h8m-8 6h16" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Registered Users</p>
                        <p class="text-xs text-gray-500">Manage existing user accounts</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <x-users.admin-users-table :users="$users" :roles="$roles" :departments="$departments" :filters="$filters" />
            </div>
        </div>

        <!-- Pending Requests Section -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm animate-slide-in delay-200 hidden" id="pendingRequestsView">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                <div class="flex items-center space-x-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-500">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Pending Requests</p>
                        <p class="text-xs text-gray-500">Review and manage account requests</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <x-users.pending-requests-table :requests="$pendingRequests" />
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.roles = @json($roles);
            window.departments = @json($departments);
        </script>
        @vite(['resources/js/filter.js', 'resources/js/users.js'])
    @endpush
@endsection
