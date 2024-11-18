@extends('layouts.app')

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="card p-8 mb-8 animate-slide-in">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="heading-1">Claims Dashboard</h2>
                <p class="text-gray-600 mt-1">Manage and track all your expense claims</p>
            </div>
            
            <!-- Quick Action Button -->
            <a href="{{ route('claims.new') }}" 
               class="nav-action-button-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Claim</span>
            </a>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stats-card animate-slide-in delay-100">
            <span class="stats-label">Total Claims</span>
            <span class="stats-value text-indigo-600">{{ $totalClaims ?? 0 }}</span>
        </div>
        <div class="stats-card animate-slide-in delay-200">
            <span class="stats-label">Approved</span>
            <span class="stats-value text-emerald-600">{{ $approvedClaims ?? 0 }}</span>
        </div>
        <div class="stats-card animate-slide-in delay-300">
            <span class="stats-label">Pending</span>
            <span class="stats-value text-amber-600">{{ $pendingClaims ?? 0 }}</span>
        </div>
        <div class="stats-card animate-slide-in delay-400">
            <span class="stats-label">Rejected</span>
            <span class="stats-value text-red-600">{{ $rejectedClaims ?? 0 }}</span>
        </div>
    </div>

    <!-- Claims Table Section -->
    <div class="card animate-slide-in delay-200">
        <!-- Claims Table -->
        <div class="p-6">
            <x-claims.claims-table 
                :claims="$claims" 
                :claimService="$claimService" 
                actions="dashboard" 
                class="animate-slide-in delay-300"
            />
        </div>
    </div>
</div>

@vite(['resources/js/filter.js'])
@endsection