@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
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
        <div class="border-b border-gray-100 p-6">
            <div class="relative focus-within:shadow-sm">
                <input
                    class="w-full rounded-lg border border-gray-200 py-2.5 pl-10 pr-4 text-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    id="searchInput" type="text" placeholder="Search claims...">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="claimsTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-16 px-3 py-2 text-left text-xs font-medium text-gray-500" data-sort="id" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                ID
                                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="submitted" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Date
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-32 px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="user" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                By
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="title" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Title
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-40 px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="dateFrom" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Period
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="status" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Status
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-20 px-4 py-3 text-right text-xs font-medium text-gray-500" scope="col">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($claims as $claim)
                        <tr class="text-xs hover:bg-gray-50/50">
                            <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ $claim->id }}</td>
                            <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ $claim->submitted_at->format('d/m/y') }}</td>
                            <td class="whitespace-nowrap px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <x-profile.profile-picture :user="$claim->user" size="sm" />
                                    <span class="text-gray-600">{{ $claim->user->first_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="max-w-[250px] truncate text-gray-600">{{ $claim->title }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                {{ $claim->date_from->format('d/m/y') }} - {{ $claim->date_to->format('d/m/y') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-claims.status-badge :status="$claim->status" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @if ($actions === 'approval')
                                    <div class="flex items-center justify-end space-x-2">
                                        @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                            <a class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-100"
                                                href="{{ route('claims.review', $claim->id) }}">
                                                Review
                                            </a>
                                        @else
                                            <a class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100"
                                                href="{{ route('claims.view', $claim->id) }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View
                                            </a>
                                        @endif
                                        @if (in_array($claim->status, [Claim::STATUS_APPROVED_FINANCE, Claim::STATUS_DONE]))
                                            <button type="button"
                                                    data-export-claim="{{ $claim->id }}"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-medium text-green-600 hover:bg-green-100">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Export
                                            </button>
                                        @endif
                                    </div>
                                @elseif ($actions === 'dashboard')
                                    @if ($claim->status === Claim::STATUS_REJECTED)
                                        <a href="{{ route('claims.resubmit', ['claim' => $claim]) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100 cursor-pointer">
                                            <span class="flex items-center">
                                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                Resubmit
                                            </span>
                                        </a>
                                    @else
                                        <a class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-100"
                                            href="{{ route('claims.view', $claim->id) }}">
                                            View
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
