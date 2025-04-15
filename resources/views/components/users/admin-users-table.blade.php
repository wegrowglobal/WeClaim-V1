@props(['users', 'roles', 'departments', 'filters'])

<div class="overflow-hidden rounded-lg bg-white">
    <!-- Filters -->
    <div class="border-b border-gray-200 p-3 sm:p-4">
        <form
            id="usersSearchForm"
            class="relative mb-4"
            method="GET" 
            action="{{ route('admin.users.index') }}">
            <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-x-4 sm:space-y-0">
                <div class="relative flex-grow focus-within:shadow-sm">
                    <input
                        class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-xs sm:text-sm focus:border-wgg-border focus:outline-none"
                        name="search" type="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search Users...">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <select
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs sm:text-sm focus:border-wgg-border focus:outline-none sm:w-auto"
                    name="role">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}"
                            {{ ($filters['role'] ?? '') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>

                <select
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs sm:text-sm focus:border-wgg-border focus:outline-none sm:w-auto"
                    name="department">
                    <option value="">All Departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}"
                            {{ ($filters['department'] ?? '') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                <button
                    class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs sm:text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto"
                    type="submit">
                    Apply Filters
                </button>
                @if (request()->hasAny(['search', 'role', 'department']))
                    <a class="inline-flex w-full items-center justify-center rounded-lg border border-red-600 bg-white px-3 py-2 text-xs sm:text-sm font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:w-auto"
                        href="{{ route('admin.users.index') }}">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="usersTable">
            <thead class="bg-gray-50">
                <tr class="*:text-xs *:font-medium *:text-gray-600">
                    <th class="px-3 py-2 text-left" data-sort="id" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            ID
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="px-3 py-2 text-left" data-sort="name" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Name
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="hidden px-3 py-2 text-left sm:table-cell" data-sort="email" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Email
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="hidden px-3 py-2 text-left md:table-cell" data-sort="role" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Role
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="hidden px-3 py-2 text-left lg:table-cell" data-sort="department" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Department
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="px-3 py-2 text-right" scope="col">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($users as $user)
                    <tr class="*:text-xs *:text-gray-600 hover:bg-gray-50">
                        <td class="whitespace-nowrap px-2 py-2 sm:px-3 sm:py-3">{{ $user->id }}</td>
                        <td class="whitespace-nowrap px-2 py-2 sm:px-3 sm:py-3">
                            <div class="truncate max-w-[100px] sm:max-w-none">
                                {{ $user->first_name }} {{ $user->second_name }}
                            </div>
                        </td>
                        <td class="hidden px-2 py-2 sm:table-cell sm:px-3 sm:py-3">{{ $user->email }}</td>
                        <td class="hidden whitespace-nowrap px-2 py-2 md:table-cell sm:px-3 sm:py-3">
                            {{ $user->role->name }}
                        </td>
                        <td class="hidden whitespace-nowrap px-2 py-2 lg:table-cell sm:px-3 sm:py-3">
                            {{ $user->department->name }}
                        </td>
                        <td class="whitespace-nowrap px-2 py-2 sm:px-3 sm:py-3 text-right">
                            <div class="flex items-center justify-end gap-1 sm:gap-2">
                                <button onclick='openEditUserModal(@json($user))' 
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-1.5 sm:p-2 text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span class="sr-only">Edit</span>
                                </button>
                                <button onclick="showDeleteModal({{ $user->id }})"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-1.5 sm:p-2 text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span class="sr-only">Delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-2 py-6 sm:px-3 sm:py-8 text-center text-xs sm:text-sm text-gray-600" colspan="6">
                            No users found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:px-6">
                <div class="mb-4 sm:mb-0 text-xs sm:text-sm text-gray-600">
                    Showing <span class="font-medium text-gray-900">{{ $users->firstItem() }}</span>
                    to <span class="font-medium text-gray-900">{{ $users->lastItem() }}</span>
                    of <span class="font-medium text-gray-900">{{ $users->total() }}</span> results
                </div>
                <div class="flex items-center justify-center sm:justify-end space-x-2">
                    @if (!$users->onFirstPage())
                        <a href="{{ $users->previousPageUrl() }}" 
                           class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="mr-1.5 h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </a>
                    @endif

                    <div class="hidden sm:flex items-center space-x-2">
                        @foreach ($users->getUrlRange(max($users->currentPage() - 2, 1), min($users->currentPage() + 2, $users->lastPage())) as $page => $url)
                            <a href="{{ $url }}" 
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg {{ $page == $users->currentPage() ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }} text-sm font-medium">
                                {{ $page }}
                            </a>
                        @endforeach
                    </div>

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" 
                           class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Next
                            <svg class="ml-1.5 h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
