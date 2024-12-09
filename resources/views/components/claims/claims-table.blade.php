@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions', 'rows'])

<div class="overflow-hidden rounded-lg bg-white">
    @if ($claims->isEmpty())
        <div class="py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-900">No claims found</h3>
            <p class="mt-1 text-sm text-gray-500">There are no claims to display at the moment.</p>
        </div>
    @else
        <!-- Search Input -->
        <div class="border-b border-gray-100 pb-4">
            <div class="relative focus-within:shadow-sm">
                <input
                    class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-wgg-border focus:outline-none"
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
                <thead class="bg-gray-50 text-xs">
                    <tr>
                        <th class="w-16 px-3 py-2 text-left font-medium text-gray-500" data-sort="id" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                ID
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-24 px-3 py-2 text-left font-medium text-gray-500" data-sort="submitted"
                            scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Date
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-32 px-3 py-2 text-left font-medium text-gray-500" data-sort="user" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                By
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500" data-sort="title" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Title
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-40 px-3 py-2 text-left font-medium text-gray-500" data-sort="dateFrom"
                            scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Period
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-28 px-3 py-2 text-left font-medium text-gray-500" data-sort="status"
                            scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Status
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-20 px-3 py-2 text-right font-medium text-gray-500" scope="col">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($claims as $claim)
                        <tr class="text-xs hover:bg-gray-50/50">
                            <td class="px-4 py-3 align-middle text-gray-600">{{ $claim->id }}</td>
                            <td class="px-4 py-3 align-middle text-gray-600">{{ $claim->submitted_at->format('d/m/y') }}
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="flex items-center gap-2">
                                    <x-profile.profile-picture :user="$claim->user" size="sm" />
                                    <span class="text-gray-600">{{ $claim->user->first_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="max-w-[250px] truncate text-gray-600">{{ $claim->title }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 align-middle text-gray-600">
                                {{ $claim->date_from->format('d/m/y') }} - {{ $claim->date_to->format('d/m/y') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 align-middle">
                                <x-claims.status-badge :status="$claim->status" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right align-middle">
                                @if ($actions === 'approval')
                                    <div class="flex items-center justify-end space-x-2">
                                        @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                            <a class="text-xs font-medium text-indigo-600 hover:text-indigo-900"
                                                href="{{ route('claims.review', $claim->id) }}">
                                                Review
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-500">
                                                @switch($claim->status)
                                                    @case(Claim::STATUS_DONE)
                                                        Completed
                                                    @break

                                                    @case(Claim::STATUS_CANCELLED)
                                                        Cancelled
                                                    @break

                                                    @case(Claim::STATUS_REJECTED)
                                                        Rejected
                                                    @break

                                                    @case(Claim::STATUS_APPROVED_FINANCE)
                                                        @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                                            <button
                                                                class="text-xs font-medium text-indigo-600 hover:text-indigo-900"
                                                                data-action="mark-as-done"
                                                                onclick="approveClaim({{ $claim->id }}, true)">
                                                                Mark as Done
                                                            </button>
                                                        @else
                                                            Pending Completion
                                                        @endif
                                                    @break

                                                    @default
                                                        Pending
                                                @endswitch
                                            </span>
                                        @endif
                                        @if ($claim->status === Claim::STATUS_DONE)
                                            <form action="{{ route('claims.export', $claim->id) }}" method="POST">
                                                @csrf
                                                @method('POST')
                                                <button
                                                    class="inline-flex items-center justify-center rounded-full bg-green-100 p-1.5 text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                                    type="submit" title="Export">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @elseif ($actions === 'dashboard')
                                    @if ($claim->status === Claim::STATUS_REJECTED)
                                        <a class="text-xs font-medium text-red-600 hover:text-red-900"
                                            href="{{ route('claims.resubmit', $claim->id) }}">
                                            Resubmit
                                        </a>
                                    @else
                                        <a class="text-xs font-medium text-indigo-600 hover:text-indigo-900"
                                            href="{{ route('claims.view', $claim->id) }}">
                                            View Details
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
