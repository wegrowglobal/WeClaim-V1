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
                        class="view-toggle-btn flex-1 rounded-md bg-white px-4 py-2 text-sm font-medium shadow transition-colors hover:bg-gray-50 sm:flex-initial"
                        id="registeredUsersBtn" onclick="switchView('registered')">
                        Registered Users
                    </button>
                    <button
                        class="view-toggle-btn relative flex flex-1 items-center justify-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium transition-colors hover:bg-gray-200 sm:flex-initial"
                        id="pendingRequestsBtn" onclick="switchView('pending')">
                        Pending Requests
                        @if ($pendingRequests->where('status', 'pending')->count() > 0)
                            <span
                                class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
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
