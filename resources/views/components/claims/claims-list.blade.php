@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions'])

<div class="space-y-4">
    @if($claims->isEmpty())
        <div class="bg-white overflow-hidden">
            <div class="py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-lg font-semibold text-gray-900">No claims found</h3>
                <p class="mt-1 text-sm text-gray-500">There are no claims to display at the moment.</p>
            </div>
        </div>
    @else
        <!-- Filters Section -->
        <div class="bg-white p-4 rounded-lg shadow-sm ring-1 ring-black/5">
            <div class="space-y-4">
                <!-- Search Input -->
                <div class="relative">
                    <input type="text" 
                           id="cardSearchInput"
                           class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-wgg-border"
                           placeholder="Search claims...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Status Filters -->
                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            data-status="all"
                            class="status-filter-btn active px-3 py-1.5 border border-gray-200 text-gray-700 bg-white"
                            onclick="toggleStatusFilter(this, 'all')">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </button>

                    @foreach([
                        Claim::STATUS_SUBMITTED,
                        Claim::STATUS_APPROVED_ADMIN,
                        Claim::STATUS_APPROVED_DATUK,
                        Claim::STATUS_APPROVED_HR,
                        Claim::STATUS_APPROVED_FINANCE,
                        Claim::STATUS_REJECTED,
                        Claim::STATUS_DONE
                    ] as $status)
                        <button type="button"
                                data-status="{{ $status }}"
                                class="status-filter-btn border border-gray-200 text-gray-300 hover:text-gray-500"
                                onclick="toggleStatusFilter(this, '{{ $status }}')">
                            <x-claims.status-badge 
                                :status="$status" 
                                class="inline-flex items-center" />
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Claims Grid -->
        <div id="claimsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($claims as $claim)
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 hover:shadow-md transition-shadow">
                    <div class="p-4 space-y-4">
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white text-sm" 
                                     style="background-color: {{ '#' . substr(md5($claim->user->first_name), 0, 6) }}">
                                    {{ strtoupper(substr($claim->user->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $claim->user->first_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $claim->submitted_at->format('d M Y') }}</div>
                                </div>
                            </div>
                            <x-claims.status-badge :status="$claim->status" />
                        </div>

                        <!-- Content -->
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-gray-600 truncate">{{ $claim->title }}</h3>
                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $claim->date_from->format('d M Y') }} - {{ $claim->date_to->format('d M Y') }}</span>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                            <div class="text-xs text-gray-500">ID: {{ $claim->id }}</div>
                            @if ($actions === 'approval')
                                @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                <a href="{{ route('claims.review', $claim->id) }}" 
                                class="text-xs font-medium text-indigo-600 hover:text-indigo-900">
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
                                                <button onclick="approveClaim({{ $claim->id }}, true)"
                                                data-action="mark-as-done"
                                                class="text-xs font-medium text-indigo-600 hover:text-indigo-900">
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
                            @elseif ($actions === 'dashboard')
                                <a href="{{ route('claims.view', $claim->id) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-900">
                                    View Details
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
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