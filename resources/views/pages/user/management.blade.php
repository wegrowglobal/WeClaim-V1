@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="card animate-slide-in mb-8 p-4 sm:p-8">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="heading-1 text-2xl sm:text-3xl">User Management</h2>
                    <p class="mt-1 text-sm text-gray-600 sm:text-base">Manage system users and their access</p>
                </div>

                <!-- View Toggle -->
                <div class="flex items-center gap-2 rounded-lg bg-gray-100 p-1.5">
                    <button class="view-toggle-btn flex items-center gap-2 rounded-md bg-white px-3 py-1.5 shadow"
                        id="registeredUsersBtn" onclick="switchView('registered')">
                        <span class="text-sm">Registered Users</span>
                    </button>
                    <button class="view-toggle-btn flex items-center gap-2 rounded-md px-3 py-1.5" id="pendingRequestsBtn"
                        onclick="switchView('pending')">
                        <span class="text-sm">Pending Requests</span>
                        @if ($pendingRequests->where('status', 'pending')->count() > 0)
                            <span
                                class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-xs font-medium text-red-600">
                                {{ $pendingRequests->where('status', 'pending')->count() }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Table Section -->
        <div class="card animate-slide-in delay-200" id="registeredUsersView">
            <div class="p-6">
                <x-users.admin-users-table :users="$users" :roles="$roles" :departments="$departments" :filters="$filters" />
            </div>
        </div>

        <!-- Pending Requests Section -->
        <div class="card animate-slide-in hidden delay-200" id="pendingRequestsView">
            <div class="p-6">
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
