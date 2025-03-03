<div class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Search and Filters -->
    <div class="border-b border-gray-200 p-4">
        <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:space-x-4 sm:space-y-0">
            <!-- Search Input -->
            <div class="relative flex-grow focus-within:shadow-sm">
                <input
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    type="search"
                    placeholder="Search users...">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Role Filter -->
            <div class="w-full sm:w-auto">
                <select
                    wire:model.live="filters.role_id"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                    @foreach($roles as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Department Filter -->
            <div class="w-full sm:w-auto">
                <select
                    wire:model.live="filters.department_id"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Departments</option>
                    @foreach($departments as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Per Page Selector -->
            <div class="w-full sm:w-auto">
                <select
                    wire:model.live="perPage"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>

            <!-- Clear Filters Button -->
            @if(!empty($search) || !empty($filters))
                <button
                    wire:click="clearFilters"
                    class="inline-flex items-center justify-center rounded-lg border border-red-600 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table-header column="id" label="ID" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('id')" />
                    <x-table-header column="first_name" label="Name" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('first_name')" />
                    <x-table-header column="email" label="Email" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('email')" responsive breakpoint="sm" />
                    <x-table-header column="role_id" label="Role" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('role_id')" responsive breakpoint="md" />
                    <x-table-header column="department_id" label="Department" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('department_id')" responsive breakpoint="lg" />
                    <x-table-header column="actions" label="Actions" :sortable="false" align="right" />
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <x-table-cell>{{ $user->id }}</x-table-cell>
                        <x-table-cell truncate>
                            {{ $user->first_name }} {{ $user->second_name }}
                        </x-table-cell>
                        <x-table-cell responsive breakpoint="sm">{{ $user->email }}</x-table-cell>
                        <x-table-cell responsive breakpoint="md">{{ $user->role->name }}</x-table-cell>
                        <x-table-cell responsive breakpoint="lg">{{ $user->department->name }}</x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                <button 
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span class="sr-only">Edit</span>
                                </button>
                                <button
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span class="sr-only">Delete</span>
                                </button>
                            </div>
                        </x-table-cell>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-sm text-gray-600">
                            No users found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 sm:px-6">
                <div class="mb-4 sm:mb-0 text-sm text-gray-600">
                    Showing <span class="font-medium text-gray-900">{{ $users->firstItem() }}</span>
                    to <span class="font-medium text-gray-900">{{ $users->lastItem() }}</span>
                    of <span class="font-medium text-gray-900">{{ $users->total() }}</span> results
                </div>
                <div class="pagination-links">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
