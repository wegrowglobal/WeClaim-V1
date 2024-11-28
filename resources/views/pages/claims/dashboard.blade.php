@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="card animate-slide-in mb-8 p-4 sm:p-8">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="heading-1 text-2xl sm:text-3xl">Claims Dashboard</h2>
                    <p class="mt-1 text-sm text-gray-600 sm:text-base">Manage and track all your expense claims</p>
                </div>

                <!-- Quick Action Button -->
                <a class="nav-action-button-primary mt-2 w-full justify-center sm:mt-0 sm:w-auto"
                    href="{{ route('claims.new') }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New Claim</span>
                </a>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="mb-8 grid grid-cols-2 gap-6 lg:grid-cols-4">
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
                <x-claims.claims-table class="animate-slide-in delay-300" :claims="$claims" :claimService="$claimService"
                    actions="dashboard" />
            </div>
        </div>
    </div>

    @vite(['resources/js/filter.js'])
@endsection
