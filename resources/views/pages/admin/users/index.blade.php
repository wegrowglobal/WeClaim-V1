@extends('layouts.app')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="mt-1 text-sm text-gray-500">Manage system users, roles, and registration requests.</p>
        </div>
        <button onclick="openCreateUserModal()" 
            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add User
        </button>
    </div>

    <!-- Filters Section -->
    <div class="mb-6 rounded-lg bg-white p-4 shadow-sm ring-1 ring-black/5">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <div class="relative mt-1">
                        <input type="search" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Name or Email..."
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                             <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Role Filter -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role" name="role"
                        class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ ($filters['role'] ?? '') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Department Filter -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="department" name="department"
                        class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ ($filters['department'] ?? '') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                 <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status"
                        class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Reset
                </a>
                <button type="submit"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="mb-8 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">User</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>
                        <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">Department</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Joined</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <x-profile.profile-picture :user="$user" size="sm" />
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900">{{ $user->first_name }} {{ $user->second_name }}</div>
                                    <div class="text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            <x-users.role-badge :role="$user->role" />
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ $user->department->name ?? 'N/A' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            @if ($user->deleted_at)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Inactive</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>
                            @endif
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 sm:table-cell">
                            {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                         </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Edit Action -->
                                <button onclick='openEditUserModal(@json($user))' title="Edit User"
                                        class="text-indigo-600 hover:text-indigo-900 focus:outline-none">
                                     <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                    </svg>
                                </button>
                                 <!-- Activate/Deactivate Action -->
                                @if ($user->deleted_at)
                                    <button onclick="handleUserActivation({{ $user->id }}, 'activate')" title="Activate User"
                                            class="text-green-600 hover:text-green-900 focus:outline-none">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @else
                                     {{-- Prevent deactivating the last SU --}}
                                    @php $isLastSU = ($user->role_id === 5 && \App\Models\User\User::where('role_id', 5)->count() <= 1); @endphp
                                    <button onclick="handleUserActivation({{ $user->id }}, 'deactivate')" title="Deactivate User"
                                            class="{{ $isLastSU ? 'text-gray-400 cursor-not-allowed' : 'text-red-600 hover:text-red-900 focus:outline-none' }}"
                                            {{ $isLastSU ? 'disabled' : '' }}>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @endif
                                 <!-- Delete Action (only if soft-deleted) -->
                                @if ($user->deleted_at)
                                    <button onclick="showDeleteModal({{ $user->id }})" title="Permanently Delete User"
                                            class="text-red-600 hover:text-red-900 focus:outline-none">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                                {{-- Add View Details Link if needed --}}
                                {{-- <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-900">View</a> --}}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="whitespace-nowrap py-8 pl-4 pr-3 text-center text-sm text-gray-500 sm:pl-6">
                            No users match the current filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                {{ $users->links() }} {{-- Use Tailwind pagination view --}}
            </div>
        @endif
    </div>

    <!-- Pending Registration Requests -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Pending Registration Requests</h2>
        @if($pendingRequests && !$pendingRequests->isEmpty())
            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">Email</th>
                                <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Requested At</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                         <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($pendingRequests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $request->first_name }} {{ $request->second_name }}</td>
                                    <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ $request->email }}</td>
                                    <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 sm:table-cell">{{ $request->created_at->format('M d, Y H:i') }}</td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                         <div class="flex items-center justify-end gap-2">
                                            <button onclick="handleRegistrationRequest({{ $request->id }}, 'approve')" title="Approve Request"
                                                    class="inline-flex items-center justify-center rounded-full bg-green-100 p-1.5 text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                            <button onclick="handleRegistrationRequest({{ $request->id }}, 'reject')" title="Reject Request"
                                                    class="inline-flex items-center justify-center rounded-full bg-red-100 p-1.5 text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                     <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                         </tbody>
                    </table>
                 </div>
            </div>
        @else
             <div class="rounded-lg border-2 border-dashed border-gray-300 p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No pending requests</h3>
                <p class="mt-1 text-sm text-gray-500">There are currently no new user registration requests.</p>
            </div>
        @endif
    </div>

    {{-- Include Modals: Create User, Edit User, Delete Confirmation --}}
    {{-- Assuming you have these modals defined elsewhere or will create them --}}
     @include('partials.modals.user-create-modal')
     @include('partials.modals.user-edit-modal')
     @include('partials.modals.user-delete-modal')
     @include('partials.modals.user-activation-modal') {{-- Modal for Activate/Deactivate confirmation --}}

</div>

@push('scripts')
    <script>
        window.roles = @json($roles);
        window.departments = @json($departments);

        // Placeholder functions for modals/actions - implement these in your JS file
        function openCreateUserModal() { console.log('Open create modal'); /* Implement modal logic */ }
        function openEditUserModal(user) { console.log('Open edit modal for:', user); /* Implement modal logic */ }
        function showDeleteModal(userId) { console.log('Show delete confirmation for user ID:', userId); /* Implement modal logic */ }
        function handleRegistrationRequest(requestId, action) { console.log(`Handle request ${requestId} with action: ${action}`); /* Implement AJAX call */ }
        function handleUserActivation(userId, action) { console.log(`Handle activation for user ${userId} with action: ${action}`); /* Implement AJAX call and confirmation modal */ }

        // You might need specific JS for activate/deactivate confirmation modals
    </script>
    @vite(['resources/js/admin/users.js']) {{-- Ensure you have JS for modal handling and AJAX calls --}}
@endpush
@endsection
