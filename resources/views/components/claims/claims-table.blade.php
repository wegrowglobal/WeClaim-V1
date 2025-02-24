@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions'])

<div class="bg-white rounded-lg shadow overflow-hidden mb-6 animate-slide-in">
    @if ($claims->isEmpty())
        <div class="flex flex-col items-center justify-center py-12">
            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No Claims Found</h3>
            <p class="mt-1 text-sm text-gray-500">There are no claims to display at the moment.</p>
        </div>
    @else
        <!-- Search Input -->
        <div class="border-b border-gray-200 p-4 sm:p-6">
            <div class="relative focus-within:shadow-sm">
                <input
                    class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    id="searchInput" type="text" placeholder="Search claims...">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="relative">
            <div class="">
                <div class="min-w-full overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="*:text-xs *:font-medium *:text-gray-600">
                                <th class="w-[10%] px-3 py-3 text-left" scope="col">
                                    <div class="flex items-center gap-1">
                                        ID
                                    </div>
                                </th>
                                <th class="w-[15%] px-3 py-3 text-left" scope="col">
                                    <div class="flex items-center gap-1">
                                        Date
                                    </div>
                                </th>
                                <th class="w-[20%] px-3 py-3 text-left" scope="col">
                                    <div class="flex items-center gap-1">
                                        User
                                    </div>
                                </th>
                                <th class="hidden w-[25%] px-3 py-3 text-left sm:table-cell" scope="col">
                                    <div class="flex items-center gap-1">
                                        Description
                                    </div>
                                </th>
                                <th class="w-[15%] px-3 py-3 text-left" scope="col">
                                    <div class="flex items-center gap-1">
                                        Period
                                    </div>
                                </th>
                                <th class="w-[15%] px-3 py-3 text-left" scope="col">
                                    <div class="flex items-center gap-1">
                                        Status
                                    </div>
                                </th>
                                <th class="w-[5%] px-3 py-3 text-center" scope="col">
                                    <span class="sr-only">View</span>
                                </th>
                                <th class="w-[10%] px-3 py-3 text-right" scope="col">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($claims as $claim)
                                <tr class="*:text-xs *:text-gray-600 hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-3 py-4">
                                        {{ $claim->id }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <span class="hidden sm:inline">{{ $claim->submitted_at->format('d/m/y') }}</span>
                                        <span class="sm:hidden">{{ $claim->submitted_at->format('d/m') }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <div class="flex items-center gap-2">
                                            <x-profile.profile-picture :user="$claim->user" size="sm" class="hidden sm:block" />
                                            <span class="truncate max-w-[100px] sm:max-w-none">{{ $claim->user->first_name }}</span>
                                        </div>
                                    </td>
                                    <td class="hidden whitespace-nowrap px-3 py-4 sm:table-cell">
                                        <div class="truncate max-w-[150px] sm:max-w-[250px]">{{ $claim->title }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <span class="hidden sm:inline">{{ $claim->date_from->format('d/m/y') }} - {{ $claim->date_to->format('d/m/y') }}</span>
                                        <span class="sm:hidden">{{ $claim->date_from->format('d/m') }} - {{ $claim->date_to->format('d/m') }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <x-claims.status-badge :status="$claim->status" class="text-xs" />
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-center">
                                        <a href="{{ route('claims.view', $claim->id) }}" 
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white p-2 text-gray-500 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span class="sr-only">View</span>
                                        </a>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        @if ($actions === 'approval')
                                            <div class="flex items-center justify-end space-x-2">
                                                @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                                    <a class="inline-flex items-center justify-center rounded-lg bg-indigo-50 p-2 text-indigo-600 hover:bg-indigo-100"
                                                        href="{{ route('claims.review', $claim->id) }}">
                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                        </svg>
                                                        <span class="sr-only">Review</span>
                                                    </a>
                                                @endif
                                                @if (in_array($claim->status, [Claim::STATUS_APPROVED_FINANCE, Claim::STATUS_DONE]))
                                                    <button type="button"
                                                            data-export-claim="{{ $claim->id }}"
                                                            class="inline-flex items-center justify-center rounded-lg bg-green-50 p-2 text-green-600 hover:bg-green-100">
                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                        <span class="sr-only">Export</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @elseif ($actions === 'dashboard')
                                            @if ($claim->status === Claim::STATUS_REJECTED)
                                                <a href="{{ route('claims.resubmit', ['claim' => $claim]) }}"
                                                    class="inline-flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-600 hover:bg-red-100">
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    <span class="sr-only">Resubmit</span>
                                                </a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (method_exists($claims, 'hasPages') && $claims->hasPages())
                    <div class="border-t border-gray-200 mt-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 sm:p-6">
                            <div class="mb-4 sm:mb-0 text-sm text-gray-600">
                                Showing <span class="font-medium text-gray-900">{{ $claims->firstItem() }}</span>
                                to <span class="font-medium text-gray-900">{{ $claims->lastItem() }}</span>
                                of <span class="font-medium text-gray-900">{{ $claims->total() }}</span> results
                            </div>
                            <div class="flex items-center justify-center sm:justify-end space-x-2">
                                @if (!$claims->onFirstPage())
                                    <a href="{{ $claims->previousPageUrl() }}" 
                                       class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg class="mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        Previous
                                    </a>
                                @endif

                                <div class="hidden sm:flex items-center space-x-2">
                                    @foreach ($claims->getUrlRange(max($claims->currentPage() - 2, 1), min($claims->currentPage() + 2, $claims->lastPage())) as $page => $url)
                                        <a href="{{ $url }}" 
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg {{ $page == $claims->currentPage() ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }} text-sm font-medium">
                                            {{ $page }}
                                        </a>
                                    @endforeach
                                </div>

                                @if ($claims->hasMorePages())
                                    <a href="{{ $claims->nextPageUrl() }}" 
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
        </div>
    @endif
</div>

@push('scripts')
    @vite(['resources/js/claim-resubmit.js', 'resources/js/claim-export.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize table sorting
            if (typeof initializeTableSorting === 'function') {
                initializeTableSorting();
            }
        });
    </script>
@endpush
