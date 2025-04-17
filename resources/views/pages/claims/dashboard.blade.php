@extends('layouts.app')

@section('content')
<div class="mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">

    <x-layout.page-header 
        title="My Claims Dashboard" 
        subtitle="Overview and history of your submitted claims">
        <a href="{{ route('claims.new') }}" class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Create New Claim
        </a>
    </x-layout.page-header>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <x-ui.stats-card title="Total Claims" :value="$statistics['totalClaims']" />
        {{-- Note: The controller passes 'pendingReview' and 'approvedClaims' based on finance approval. We might need different stats here or adjust controller logic --}}
        <x-ui.stats-card title="Pending Review" :value="$statistics['pendingReview']" variant="warning" /> 
        <x-ui.stats-card title="Approved (Finance)" :value="$statistics['approvedClaims']" variant="success" />
        <x-ui.stats-card title="Total Claimed (RM)" :value="number_format($statistics['totalAmount'], 2)" />
    </div>

    {{-- Claims List --}}
    <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">All My Claims</h3>
    @if($claims->isNotEmpty())
        <x-claims.claim-list :claims="$claims" :claimService="$claimService" />
    @else
        <div class="text-center py-8 px-4 border border-gray-200 rounded-md bg-white">
             <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">No claims found</h3>
            <p class="mt-1 text-sm text-gray-500">You have not submitted any claims yet.</p>
            <div class="mt-6">
                 <a href="{{ route('claims.new') }}" class="inline-flex items-center rounded-md bg-black px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                    Create Your First Claim
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
