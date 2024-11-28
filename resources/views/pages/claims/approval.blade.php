@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="card animate-slide-in mb-8 p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="heading-1">Claims Approval</h2>
                    <p class="mt-1 text-gray-600">Review and manage pending claim requests</p>
                </div>
            </div>
        </div>

        <!-- Claims Table Section -->
        <div class="card animate-slide-in delay-100">
            <!-- Search and Filters -->
            <div class="border-b border-gray-100 p-6">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">

                    <!-- View Toggle -->
                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 p-1.5">
                        <button class="view-toggle-btn flex items-center gap-2 px-3" id="tableViewBtn" type="button"
                            onclick="setView('table')">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M3 14h18M3 18h18M3 6h18" />
                            </svg>
                            <span class="text-sm">Table</span>
                        </button>
                        <button class="view-toggle-btn flex items-center gap-2 px-3" id="gridViewBtn" type="button"
                            onclick="setView('grid')">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="text-sm">Cards</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Claims View Container -->
            <div class="p-6">
                <div class="animate-slide-in delay-200" id="tableView">
                    <x-claims.claims-table :claims="$claims" :claimService="$claimService" actions="approval" />
                </div>
                <div class="animate-slide-in hidden delay-200" id="gridView">
                    <x-claims.claims-list :claims="$claims" :claimService="$claimService" actions="approval" />
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        @vite(['resources/js/filter.js', 'resources/js/card-filter.js', 'resources/js/claims-view-toggle.js', 'resources/js/claim-review.js'])
        <script>
            window.userId = {{ Auth::id() }};
        </script>
    @endpush
@endsection
