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
                    placeholder="Search claims...">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-auto">
                <select
                    wire:model.live="filters.status"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- User Filter -->
            <div class="w-full sm:w-auto">
                <select
                    wire:model.live="filters.user_id"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All Users</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range Filters -->
            <div class="w-full sm:w-auto">
                <input
                    wire:model.live="filters.date_from"
                    type="date"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    placeholder="From Date">
            </div>

            <div class="w-full sm:w-auto">
                <input
                    wire:model.live="filters.date_to"
                    type="date"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    placeholder="To Date">
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

    <!-- Status Filter Buttons -->
    <div class="border-b border-gray-200 px-4 py-3">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Quick Filter by Status:</h3>
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="clearFilters"
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-gray-50 text-gray-600 hover:bg-gray-100 ring-1 ring-gray-500/10 {{ empty($filters['status']) ? 'ring-2 ring-offset-1 ring-gray-500' : '' }}">
                All
            </button>
            @foreach($statuses as $value => $label)
                <button type="button" wire:click="applyFilter('status', '{{ $value }}')"
                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium 
                        @if($value === 'SUBMITTED') bg-amber-50 text-amber-600 hover:bg-amber-100 ring-1 ring-amber-500/10
                        @elseif($value === 'APPROVED_ADMIN') bg-indigo-50 text-indigo-600 hover:bg-indigo-100 ring-1 ring-indigo-500/10
                        @elseif($value === 'APPROVED_DATUK') bg-blue-50 text-blue-600 hover:bg-blue-100 ring-1 ring-blue-500/10
                        @elseif($value === 'APPROVED_HR') bg-purple-50 text-purple-600 hover:bg-purple-100 ring-1 ring-purple-500/10
                        @elseif($value === 'APPROVED_FINANCE') bg-emerald-50 text-emerald-600 hover:bg-emerald-100 ring-1 ring-emerald-500/10
                        @elseif($value === 'APPROVED_MANAGER') bg-teal-50 text-teal-600 hover:bg-teal-100 ring-1 ring-teal-500/10
                        @elseif($value === 'REJECTED') bg-red-50 text-red-600 hover:bg-red-100 ring-1 ring-red-500/10
                        @elseif($value === 'DONE') bg-green-50 text-green-600 hover:bg-green-100 ring-1 ring-green-500/10
                        @elseif($value === 'CANCELLED') bg-gray-50 text-gray-600 hover:bg-gray-100 ring-1 ring-gray-500/10
                        @elseif($value === 'PENDING_DATUK') bg-indigo-50 text-indigo-600 hover:bg-indigo-100 ring-1 ring-indigo-500/10
                        @else bg-gray-50 text-gray-600 hover:bg-gray-100 ring-1 ring-gray-500/10
                        @endif
                        {{ isset($filters['status']) && $filters['status'] === $value ? 'ring-2 ring-offset-1 ring-gray-500' : '' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table-header column="id" label="ID" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('id')" width="10%" />
                    <x-table-header column="submitted_at" label="Date" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('submitted_at')" width="15%" />
                    <x-table-header column="user_id" label="User" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('user_id')" width="20%" />
                    <x-table-header column="title" label="Description" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('title')" responsive breakpoint="sm" width="25%" />
                    <x-table-header column="date_from" label="Period" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('date_from')" width="15%" />
                    <x-table-header column="status" label="Status" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('status')" width="15%" />
                    <x-table-header column="view" label="" :sortable="false" align="center" width="5%" />
                    <x-table-header column="actions" label="" :sortable="false" align="right" width="10%" />
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($claims as $claim)
                    <tr class="hover:bg-gray-50">
                        <x-table-cell>{{ $claim->id }}</x-table-cell>
                        <x-table-cell>
                            <span class="hidden sm:inline">{{ $claim->submitted_at->format('d/m/y') }}</span>
                            <span class="sm:hidden">{{ $claim->submitted_at->format('d/m') }}</span>
                        </x-table-cell>
                        <x-table-cell truncate>
                            <div class="flex items-center gap-2">
                                <span>{{ $claim->user->first_name }} {{ $claim->user->second_name }}</span>
                            </div>
                        </x-table-cell>
                        <x-table-cell responsive breakpoint="sm" truncate maxWidth="250px">
                            {{ $claim->title }}
                        </x-table-cell>
                        <x-table-cell>
                            <span class="hidden sm:inline">{{ $claim->date_from->format('d/m/y') }} - {{ $claim->date_to->format('d/m/y') }}</span>
                            <span class="sm:hidden">{{ $claim->date_from->format('d/m') }} - {{ $claim->date_to->format('d/m') }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                @if($claim->status === 'SUBMITTED') bg-blue-100 text-blue-800
                                @elseif($claim->status === 'APPROVED_ADMIN') bg-indigo-100 text-indigo-800
                                @elseif($claim->status === 'APPROVED_DATUK') bg-purple-100 text-purple-800
                                @elseif($claim->status === 'APPROVED_HR') bg-green-100 text-green-800
                                @elseif($claim->status === 'APPROVED_FINANCE') bg-emerald-100 text-emerald-800
                                @elseif($claim->status === 'REJECTED') bg-red-100 text-red-800
                                @elseif($claim->status === 'DONE') bg-gray-100 text-gray-800
                                @endif">
                                {{ $statuses[$claim->status] ?? $claim->status }}
                            </span>
                        </x-table-cell>
                        <x-table-cell align="center">
                            <a href="{{ route('claims.view', $claim->id) }}" 
                                class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="sr-only">View</span>
                            </a>
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex items-center justify-end gap-2">
                                @if($claim->status === 'REJECTED')
                                    <a href="{{ route('claims.resubmit', ['claim' => $claim]) }}"
                                        class="inline-flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-600 hover:bg-red-100">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span class="sr-only">Resubmit</span>
                                    </a>
                                @endif
                            </div>
                        </x-table-cell>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-8 text-center text-sm text-gray-600">
                            No claims found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($claims->hasPages())
        <div class="border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 sm:px-6">
                <div class="mb-4 sm:mb-0 text-sm text-gray-600">
                    Showing <span class="font-medium text-gray-900">{{ $claims->firstItem() }}</span>
                    to <span class="font-medium text-gray-900">{{ $claims->lastItem() }}</span>
                    of <span class="font-medium text-gray-900">{{ $claims->total() }}</span> results
                </div>
                <div class="pagination-links">
                    {{ $claims->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
