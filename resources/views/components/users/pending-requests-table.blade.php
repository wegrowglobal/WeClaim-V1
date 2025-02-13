@props(['requests'])

<div class="overflow-hidden">
    <div class="min-w-full overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="pendingRequestsTable">
            <thead class="bg-gray-50">
                <tr class="*:text-xs *:font-medium *:text-gray-600">
                    <th class="w-[10%] px-3 py-3 text-left" scope="col">
                        <div class="flex items-center gap-1">
                            ID
                        </div>
                    </th>
                    <th class="w-[25%] px-3 py-3 text-left" scope="col">
                        <div class="flex items-center gap-1">
                            Name
                        </div>
                    </th>
                    <th class="hidden w-[30%] px-3 py-3 text-left sm:table-cell" scope="col">
                        <div class="flex items-center gap-1">
                            Email
                        </div>
                    </th>
                    <th class="hidden w-[20%] px-3 py-3 text-left md:table-cell" scope="col">
                        <div class="flex items-center gap-1">
                            Requested At
                        </div>
                    </th>
                    <th class="w-[15%] px-3 py-3 text-right" scope="col">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($requests->where('status', 'pending') as $request)
                    <tr class="*:text-xs *:text-gray-600 hover:bg-gray-50">
                        <td class="whitespace-nowrap px-3 py-4">
                            {{ $request->id }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4">
                            <div class="truncate max-w-[100px] sm:max-w-none">
                                {{ $request->first_name }} {{ $request->second_name }}
                            </div>
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-4 sm:table-cell">
                            {{ $request->email }}
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-4 md:table-cell">
                            {{ $request->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="handleRegistrationRequest({{ $request->id }}, 'approve')"
                                    class="inline-flex items-center justify-center rounded-lg border border-transparent bg-green-600 p-2 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="sr-only">Approve</span>
                                </button>
                                <button onclick="handleRegistrationRequest({{ $request->id }}, 'reject')"
                                    class="inline-flex items-center justify-center rounded-lg border border-transparent bg-red-600 p-2 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="sr-only">Reject</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-8 text-center text-sm text-gray-600" colspan="5">
                            No pending requests found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($requests, 'hasPages') && $requests->hasPages())
        <div class="border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 sm:px-6">
                <div class="mb-4 sm:mb-0 text-sm text-gray-600">
                    Showing <span class="font-medium text-gray-900">{{ $requests->firstItem() }}</span>
                    to <span class="font-medium text-gray-900">{{ $requests->lastItem() }}</span>
                    of <span class="font-medium text-gray-900">{{ $requests->total() }}</span> results
                </div>
                <div class="flex items-center justify-center sm:justify-end space-x-2">
                    @if (!$requests->onFirstPage())
                        <a href="{{ $requests->previousPageUrl() }}" 
                           class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </a>
                    @endif

                    <div class="hidden sm:flex items-center space-x-2">
                        @foreach ($requests->getUrlRange(max($requests->currentPage() - 2, 1), min($requests->currentPage() + 2, $requests->lastPage())) as $page => $url)
                            <a href="{{ $url }}" 
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg {{ $page == $requests->currentPage() ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }} text-sm font-medium">
                                {{ $page }}
                            </a>
                        @endforeach
                    </div>

                    @if ($requests->hasMorePages())
                        <a href="{{ $requests->nextPageUrl() }}" 
                           class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Next
                            <svg class="ml-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initializeTableSorting === 'function') {
                initializeTableSorting();
            } else {
                console.error('Table sorting functionality not loaded');
            }
        });
    </script>
@endpush
