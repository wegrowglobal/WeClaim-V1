@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions'])

<div class="space-y-4">
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
                        class="status-filter-btn active"
                        onclick="toggleStatusFilter(this, 'all')">
                    <span class="text-sm">All Claims</span>
                </button>
                @foreach(['SUBMITTED', 'APPROVED_ADMIN', 'APPROVED_DATUK', 'APPROVED_HR', 'APPROVED_FINANCE', 'REJECTED', 'DONE'] as $status)
                    <button type="button"
                            data-status="{{ $status }}"
                            class="status-filter-btn"
                            onclick="toggleStatusFilter(this, '{{ $status }}')">
                        <x-status-badge :status="$status" />
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
                        <x-status-badge :status="$claim->status" />
                    </div>

                    <!-- Content -->
                    <div class="space-y-2">
                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $claim->title }}</h3>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
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
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                    Review
                                </a>
                            @else
                                <span class="text-xs text-gray-500">Pending</span>
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
</div> 