@props(['users', 'roles', 'departments', 'filters'])

<div class="overflow-hidden rounded-lg bg-white">
    <!-- Filters -->
    <div class="border-b border-gray-200 py-4">
        <form class="flex flex-wrap items-center gap-4" method="GET" action="{{ route('users.management') }}">
            <div class="flex flex-1 flex-wrap gap-4">
                <div class="relative min-w-[200px] flex-1 focus-within:shadow-sm">
                    <input
                        class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-wgg-border focus:outline-none"
                        name="search" type="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search users...">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <select
                    class="min-w-[150px] flex-1 rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-wgg-border focus:outline-none"
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
                    class="min-w-[150px] flex-1 rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-wgg-border focus:outline-none"
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

            <div class="flex flex-col gap-2 sm:mt-0">
                <button
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    type="submit">
                    Apply Filters
                </button>
                @if (request()->hasAny(['search', 'role', 'department']))
                    <a class="inline-flex items-center justify-center rounded-lg border border-red-600 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        href="{{ route('users.management') }}">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="usersTable">
            <thead class="bg-gray-50 text-xs">
                <tr>
                    <th class="w-16 px-3 py-2 text-left font-medium text-gray-500" data-sort="id" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            ID
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="w-32 px-3 py-2 text-left font-medium text-gray-500" data-sort="name" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Name
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500" data-sort="email" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Email
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="w-24 px-3 py-2 text-left font-medium text-gray-500" data-sort="role" scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Role
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="w-32 px-3 py-2 text-left font-medium text-gray-500" data-sort="department"
                        scope="col">
                        <div class="flex cursor-pointer items-center gap-1">
                            Department
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th class="w-24 px-3 py-2 text-right font-medium text-gray-500" scope="col">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($users as $user)
                    <tr class="text-xs hover:bg-gray-50/50">
                        <td class="whitespace-nowrap px-3 py-3 text-gray-500">{{ $user->id }}</td>
                        <td class="whitespace-nowrap px-3 py-3 text-gray-500">
                            {{ $user->first_name }} {{ $user->second_name }}
                        </td>
                        <td class="px-3 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="whitespace-nowrap px-3 py-3 text-gray-500">
                            {{ $user->role->name }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-gray-500">
                            {{ $user->department->name }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button class="text-xs font-medium text-blue-600 hover:text-blue-800"
                                    onclick='openEditUserModal(@json($user))'>
                                    Edit
                                </button>
                                <button class="text-xs font-medium text-red-600 hover:text-red-800"
                                    onclick="showDeleteModal({{ $user->id }})">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-8 text-center text-gray-500" colspan="6">
                            No users found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="border-t border-gray-200 py-3">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $users->firstItem() }}</span>
                    to <span class="font-medium">{{ $users->lastItem() }}</span>
                    of <span class="font-medium">{{ $users->total() }}</span> results
                </div>
                <div class="flex justify-between gap-x-2">
                    @if ($users->onFirstPage())
                        <span
                            class="inline-flex cursor-not-allowed items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-400">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                    @else
                        <a class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            href="{{ $users->previousPageUrl() }}">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    @endif

                    @if ($users->hasMorePages())
                        <a class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            href="{{ $users->nextPageUrl() }}">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @else
                        <span
                            class="inline-flex cursor-not-allowed items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-400">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
