@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions'])

<div class="space-y-4">
    @if ($claims->isEmpty())
        <div class="overflow-hidden bg-white">
            <div class="py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-lg font-semibold text-gray-900">No claims found</h3>
                <p class="mt-1 text-sm text-gray-500">There are no claims to display at the moment.</p>
            </div>
        </div>
    @else
        <!-- Filters Section -->
        <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-black/5">
            <div class="space-y-4">
                <!-- Search Input -->
                <div class="relative">
                    <input
                        class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-wgg-border focus:outline-none"
                        id="cardSearchInput" type="text" placeholder="Search claims...">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Status Filters -->
                <div class="flex flex-wrap gap-2">
                    <button class="status-filter-btn active border border-gray-200 bg-white px-3 py-1.5 text-gray-700"
                        data-status="all" type="button" onclick="toggleStatusFilter(this, 'all')">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </button>

                    @foreach ([Claim::STATUS_SUBMITTED, Claim::STATUS_APPROVED_ADMIN, Claim::STATUS_APPROVED_DATUK, Claim::STATUS_APPROVED_HR, Claim::STATUS_APPROVED_FINANCE, Claim::STATUS_REJECTED, Claim::STATUS_DONE] as $status)
                        <button class="status-filter-btn border border-gray-200 text-gray-300 hover:text-gray-500"
                            data-status="{{ $status }}" type="button"
                            onclick="toggleStatusFilter(this, '{{ $status }}')">
                            <x-claims.status-badge class="inline-flex items-center" :status="$status" />
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Claims Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" id="claimsGrid">
            @foreach ($claims as $claim)
                <div class="rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-shadow hover:shadow-md">
                    <div class="space-y-4 p-4">
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <x-profile.profile-picture :user="$claim->user" size="sm" />
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $claim->user->first_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $claim->submitted_at->format('d M Y') }}</div>
                                </div>
                            </div>
                            <x-claims.status-badge :status="$claim->status" />
                        </div>

                        <!-- Content -->
                        <div class="space-y-2">
                            <h3 class="truncate text-sm font-medium text-gray-600">{{ $claim->title }}</h3>
                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ $claim->date_from->format('d M Y') }} -
                                    {{ $claim->date_to->format('d M Y') }}</span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                            <div class="text-xs text-gray-500">ID: {{ $claim->id }}</div>
                            @if ($actions === 'approval')
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
                                                    <button class="text-xs font-medium text-indigo-600 hover:text-indigo-900"
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
                                    <form class="inline" action="{{ route('claims.export', $claim->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('POST')
                                        <button
                                            class="inline-flex items-center justify-center rounded-full bg-green-100 p-1.5 text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                            type="submit" title="Export PDF">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            @elseif ($actions === 'dashboard')
                                <a class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-900"
                                    href="{{ route('claims.view', $claim->id) }}">
                                    View Details
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
