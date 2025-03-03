<div>
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
                        placeholder="Search...">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Dynamic Filters -->
                @foreach($filterOptions as $key => $options)
                    <div class="w-full sm:w-auto">
                        <select
                            wire:model.live="filters.{{ $key }}"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">{{ ucfirst(str_replace('_', ' ', $key)) }}</option>
                            @foreach($options as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

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
                        {{ $header }}
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @if($data->isEmpty())
                        <tr>
                            <td colspan="100" class="px-3 py-8 text-center text-sm text-gray-600">
                                No results found
                            </td>
                        </tr>
                    @else
                        {{ $body }}
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($data->hasPages())
            <div class="border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 sm:px-6">
                    <div class="mb-4 sm:mb-0 text-sm text-gray-600">
                        Showing <span class="font-medium text-gray-900">{{ $data->firstItem() }}</span>
                        to <span class="font-medium text-gray-900">{{ $data->lastItem() }}</span>
                        of <span class="font-medium text-gray-900">{{ $data->total() }}</span> results
                    </div>
                    <div class="pagination-links">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
