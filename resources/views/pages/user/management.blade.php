@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="card animate-slide-in mb-4 p-4 sm:mb-8 sm:p-8">
            <div class="flex flex-col items-start justify-between gap-4">
                <div class="w-full">
                    <h2 class="heading-1 text-2xl font-bold sm:text-3xl">User Management</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage system users and their access</p>
                </div>

                <!-- View Toggle -->
                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
                    <button
                        class="user-management-toggle-btn active group flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium shadow-sm transition-all hover:bg-gray-50 sm:flex-initial"
                        id="registeredUsersBtn" onclick="switchView('registered')">
                        <svg class="h-4 w-4 text-gray-500 transition-colors group-hover:text-gray-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Registered Users</span>
                    </button>

                    <button
                        class="user-management-toggle-btn group relative flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium shadow-sm transition-all hover:bg-gray-50 sm:flex-initial"
                        id="pendingRequestsBtn" onclick="switchView('pending')">
                        <svg class="h-4 w-4 text-gray-500 transition-colors group-hover:text-gray-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>Pending Requests</span>
                        @if ($pendingRequests->where('status', 'pending')->count() > 0)
                            <span
                                class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-semibold text-white ring-2 ring-white">
                                {{ $pendingRequests->where('status', 'pending')->count() }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Table Section -->
        <div class="card animate-slide-in delay-200" id="registeredUsersView">
            <div class="p-4 sm:p-6">
                <x-users.admin-users-table :users="$users" :roles="$roles" :departments="$departments" :filters="$filters" />
            </div>
        </div>

        <!-- Pending Requests Section -->
        <div class="card animate-slide-in hidden delay-200" id="pendingRequestsView">
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
