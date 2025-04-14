@php
    use App\Models\Claim\Claim;
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
        <!-- Search and Filters -->
        <div class="border-b border-gray-100">
            <div class="p-6">
                <!-- Search Input -->
                <div class="relative mb-4 focus-within:shadow-sm">
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

                <!-- Status Filters -->
                <div class="flex flex-wrap gap-2">
                    <button class="status-filter-btn active inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100"
                        data-status="all" type="button" onclick="toggleStatusFilter(this, 'all')">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        All Claims
                    </button>

                    @foreach ([Claim::STATUS_SUBMITTED, Claim::STATUS_APPROVED_ADMIN, Claim::STATUS_APPROVED_DATUK, Claim::STATUS_APPROVED_HR, Claim::STATUS_APPROVED_FINANCE, Claim::STATUS_REJECTED, Claim::STATUS_DONE] as $status)
                        <button class="status-filter-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium hover:bg-gray-50"
                            data-status="{{ $status }}" type="button"
                            onclick="toggleStatusFilter(this, '{{ $status }}')">
                            <x-claims.status-badge :status="$status" />
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Claims Grid -->
        <div class="grid grid-cols-1 gap-3 p-3 sm:gap-4 sm:p-4 md:grid-cols-2 lg:grid-cols-3" id="claimsGrid">
            @foreach ($claims as $claim)
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white transition-all duration-300 hover:border-gray-300 hover:shadow-sm">
                    <div class="space-y-3 sm:space-y-4 p-3 sm:p-4">
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <x-profile.profile-picture :user="$claim->user" size="sm" />
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $claim->user->first_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $claim->submitted_at->format('d M Y') }}</div>
                                </div>
                            </div>
                            <x-claims.status-badge :status="$claim->status" class="text-xs sm:text-sm" />
                        </div>

                        <!-- Content -->
                        <div>
                            <div class="line-clamp-2 text-xs sm:text-sm text-gray-600">{{ $claim->description }}</div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $claim->date_from->format('d M Y') }} - {{ $claim->date_to->format('d M Y') }}
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                            <div class="text-xs text-gray-500">ID: {{ $claim->id }}</div>
                            @if ($actions === 'approval')
                                @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                    <a class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-100"
                                        href="{{ route('claims.review', $claim->id) }}">
                                        Review
                                    </a>
                                @endif
                                @if (in_array($claim->status, [Claim::STATUS_APPROVED_FINANCE, Claim::STATUS_DONE]))
                                    <form class="inline" action="{{ route('claims.export', $claim->id) }}" method="POST">
                                        @csrf
                                        @method('POST')
                                        <button class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-medium text-green-600 hover:bg-green-100"
                                            type="submit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Export
                                        </button>
                                    </form>
                                @endif
                            @elseif ($actions === 'dashboard')
                                @if ($claim->status === Claim::STATUS_REJECTED)
                                    <a class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100"
                                        href="{{ route('claims.resubmit', $claim->id) }}">
                                        Resubmit
                                    </a>
                                @else
                                    <a class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-100"
                                        href="{{ route('claims.view', $claim->id) }}">
                                        View
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
