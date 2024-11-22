@extends('layouts.app')

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="card p-8 mb-8 animate-slide-in">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="heading-1">Claims Approval</h2>
                <p class="text-gray-600 mt-1">Review and manage pending claim requests</p>
            </div>
        </div>
    </div>

    <!-- Claims Table Section -->
    <div class="card animate-slide-in delay-100">
        <!-- Search and Filters -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">

                <!-- View Toggle -->
                <div class="flex items-center gap-2 p-1.5 bg-gray-100 rounded-lg">
                    <button type="button" 
                            onclick="setView('table')"
                            id="tableViewBtn"
                            class="view-toggle-btn flex items-center gap-2 px-3">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M3 18h18M3 6h18"/>
                        </svg>
                        <span class="text-sm">Table</span>
                    </button>
                    <button type="button" 
                            onclick="setView('grid')"
                            id="gridViewBtn"
                            class="view-toggle-btn flex items-center gap-2 px-3">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span class="text-sm">Cards</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Claims View Container -->
        <div class="p-6">
            <div id="tableView" class="animate-slide-in delay-200">
                <x-claims.claims-table 
                    :claims="$claims" 
                    :claimService="$claimService" 
                    actions="approval"
                />
            </div>
            <div id="gridView" class="hidden animate-slide-in delay-200">
                <x-claims.claims-list
                    :claims="$claims" 
                    :claimService="$claimService" 
                    actions="approval"
                />
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