@extends('layouts.app')

@section('content')
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 overflow-x-hidden">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage system users and their access</p>
                </div>
                <button onclick="openCreateUserModal()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add User
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex" aria-label="Tabs">
                    <button onclick="switchTab('users')" 
                        class="tab-btn active w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm border-indigo-500 text-indigo-600">
                        Registered Users
                        <span class="ml-2 bg-indigo-100 text-indigo-600 py-0.5 px-2.5 rounded-full text-xs">
                            {{ $users->total() }}
                        </span>
                    </button>
                    <button onclick="switchTab('pending')" 
                        class="tab-btn w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Pending Requests
                        @if($pendingRequests->where('status', 'pending')->count() > 0)
                            <span class="ml-2 bg-red-100 text-red-600 py-0.5 px-2.5 rounded-full text-xs">
                                {{ $pendingRequests->where('status', 'pending')->count() }}
                            </span>
                        @endif
                    </button>
                </nav>
            </div>

            <!-- Content -->
            <div class="relative">
                <div id="usersTab" class="tab-content">
                    <div>
                        <x-users.admin-users-table :users="$users" :roles="$roles" :departments="$departments" :filters="$filters" />
                    </div>
                </div>

                <div id="pendingTab" class="tab-content hidden">
                    <div>
                        <x-users.pending-requests-table :requests="$pendingRequests" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.roles = @json($roles);
            window.departments = @json($departments);
        </script>
        @vite(['resources/js/users.js'])
    @endpush
@endsection
