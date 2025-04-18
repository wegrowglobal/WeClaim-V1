@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">

        {{-- Get authenticated user and role --}}
        @php
            $user = Auth::user();
            $roleName = $user->role->name;
        @endphp

        {{-- === ROLE: Staff === --}}
        @if($roleName === 'Staff')
            <x-layout.page-header 
                title="My Dashboard" 
                subtitle="Overview of your claims.">
                <a href="{{ route('claims.new') }}" class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Create New Claim
                </a>
            </x-layout.page-header>

            {{-- Staff Stats --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <x-ui.stats-card title="Total Claims" :value="$statistics['totalClaims']" />
                <x-ui.stats-card title="Approved" :value="$statistics['approvedClaims']" variant="success" />
                <x-ui.stats-card title="Pending" :value="$statistics['pendingClaims']" variant="warning" />
                <x-ui.stats-card title="Rejected" :value="$statistics['rejectedClaims']" variant="danger" />
            </div>

            {{-- Recent Claims List --}}
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Recent Claims</h3>
            @if($recentClaims->isNotEmpty())
                <x-claims.claim-list :claims="$recentClaims" :claimService="$claimService" />
            @else
                <div class="text-center py-8 px-4 border border-gray-200 rounded-md bg-white">
                     <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No claims yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first claim.</p>
                    <div class="mt-6">
                         <a href="{{ route('claims.new') }}" class="inline-flex items-center rounded-md bg-black px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                            Create New Claim
                        </a>
                    </div>
                </div>
            @endif

        {{-- === ROLE: HR === --}}
        @elseif($roleName === 'HR')
            <x-layout.page-header 
                title="HR Dashboard" 
                subtitle="Overview of claims requiring HR attention.">
                 <a href="{{ route('claims.approval') }}" class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                    View All Approvals
                </a>
            </x-layout.page-header>
            {{-- HR Stats --}}
             <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                 <x-ui.stats-card title="Pending HR Review" :value="$statistics['pendingClaims'] ?? 0" variant="warning" /> {{-- Adjust stat source if needed --}}
                 <x-ui.stats-card title="Total Claims" :value="$statistics['totalClaims'] ?? 0" />
                 {{-- Add more relevant HR stats here --}}
             </div>

            {{-- Pending HR Approval List --}}
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Claims Pending HR Approval</h3>
            @if($pendingHrApproval->isNotEmpty())
                <x-claims.claim-list :claims="$pendingHrApproval" :claimService="$claimService" />
            @else
                 <div class="text-center py-8 px-4 border border-gray-200 rounded-md bg-white">
                     <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                     <h3 class="mt-2 text-sm font-semibold text-gray-900">No claims pending</h3>
                     <p class="mt-1 text-sm text-gray-500">There are currently no claims awaiting your approval.</p>
                 </div>
            @endif

        {{-- === ROLE: Finance === --}}
        @elseif($roleName === 'Finance')
             <x-layout.page-header 
                title="Finance Dashboard" 
                subtitle="Overview of claims requiring Finance attention.">
                 <a href="{{ route('claims.approval') }}" class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                    View All Approvals
                </a>
             </x-layout.page-header>
              {{-- Finance Stats --}}
             <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                 <x-ui.stats-card title="Pending Finance Review" :value="$statistics['pendingClaims'] ?? 0" variant="warning" /> {{-- Adjust stat source --}}
                 <x-ui.stats-card title="Total Claims" :value="$statistics['totalClaims'] ?? 0" />
                 {{-- Add more relevant Finance stats here --}}
             </div>
            {{-- Pending Finance Approval List --}}
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Claims Pending Finance Approval</h3>
             @if($pendingFinanceApproval->isNotEmpty())
                <x-claims.claim-list :claims="$pendingFinanceApproval" :claimService="$claimService" />
             @else
                 <div class="text-center py-8 px-4 border border-gray-200 rounded-md bg-white">
                     <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                     <h3 class="mt-2 text-sm font-semibold text-gray-900">No claims pending</h3>
                     <p class="mt-1 text-sm text-gray-500">There are currently no claims awaiting your approval.</p>
                 </div>
            @endif

        {{-- === ROLE: Datuk === --}}
        @elseif($roleName === 'Datuk')
             <x-layout.page-header 
                title="Datuk Dashboard" 
                subtitle="Overview of claims requiring Datuk attention.">
                 <a href="{{ route('claims.approval') }}" class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                    View All Approvals
                </a>
             </x-layout.page-header>
              {{-- Datuk Stats --}}
             <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                 <x-ui.stats-card title="Pending Datuk Review" :value="$statistics['pendingClaims'] ?? 0" variant="warning" /> {{-- Adjust stat source --}}
                 <x-ui.stats-card title="Total Claims" :value="$statistics['totalClaims'] ?? 0" />
                 {{-- Add more relevant Datuk stats here --}}
             </div>
            {{-- Pending Datuk Approval List --}}
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Claims Pending Datuk Approval</h3>
             @if($pendingDatukApproval->isNotEmpty())
                <x-claims.claim-list :claims="$pendingDatukApproval" :claimService="$claimService" />
            @else
                 <div class="text-center py-8 px-4 border border-gray-200 rounded-md bg-white">
                     <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                     <h3 class="mt-2 text-sm font-semibold text-gray-900">No claims pending</h3>
                     <p class="mt-1 text-sm text-gray-500">There are currently no claims awaiting your approval.</p>
                 </div>
            @endif

        {{-- === ROLE: Admin === --}}
        @elseif($roleName === 'Admin' || $roleName === 'SU')
            <x-layout.page-header 
                title="Admin Dashboard" 
                subtitle="System overview and administration.">
                 {{-- Admin specific actions can go here --}}
                 <div class="flex gap-x-4">
                     <a href="{{ route('claims.new') }}" class="inline-flex items-center justify-center rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                         <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                         Create Claim
                    </a>
                     <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                        User Management
                    </a>
                </div>
            </x-layout.page-header>

            {{-- Admin Stats --}}
             <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <x-ui.stats-card title="Total Claims" :value="$statistics['totalClaims'] ?? 0" />
                <x-ui.stats-card title="All Pending" :value="$statistics['pendingClaims'] ?? 0" variant="warning" />
                <x-ui.stats-card title="Total Users" :value="$userCount ?? 0" />
                 {{-- Add more system stats --}}
             </div>

            {{-- All Pending Claims List --}}
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">All Pending Claims</h3>
             @if($allPendingClaims->isNotEmpty())
                <x-claims.claim-list :claims="$allPendingClaims" :claimService="$claimService" />
            @else
                 <div class="text-center py-8 px-4 border border-gray-200 rounded-md bg-white">
                     <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                     <h3 class="mt-2 text-sm font-semibold text-gray-900">No pending claims</h3>
                     <p class="mt-1 text-sm text-gray-500">There are currently no claims awaiting review across the system.</p>
                 </div>
            @endif

        @endif

    </div>
@endsection

@push('scripts')
{{-- Add any home page specific scripts here if needed --}}
@endpush
