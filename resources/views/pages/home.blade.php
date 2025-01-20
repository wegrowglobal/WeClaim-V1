@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        @guest
            <div class="card animate-slide-in space-y-4 p-8">
                <div class="flex items-center gap-4">
                    <h2 class="heading-1">Welcome to WeClaims</h2>
                    <span class="status-badge bg-red-50 text-red-700">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm0-8a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V8a1 1 0 0 1 1-1Z" />
                        </svg>
                        Not Logged In
                    </span>
                </div>
                <p class="text-gray-600">Please log in to access your claims dashboard and submit new claims.</p>
                <div class="mt-6 flex gap-4">
                    <a class="btn-primary" href="{{ route('login') }}">
                        Sign In
                    </a>
                    <a class="btn-secondary" href=" ">
                        Create Account
                    </a>
                </div>
            </div>
        @endguest

        @auth
            <!-- User Welcome Section -->
            <div class="card animate-slide-in mb-8 p-4 sm:p-8">
                <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:gap-6 sm:text-left">
                    <x-profile.profile-picture :user="auth()->user()" size="lg" />
                    <div>
                        <h2 class="heading-1 text-xl sm:text-2xl">Welcome back, {{ auth()->user()->first_name }}</h2>
                        <p class="mt-1 text-sm text-gray-600 sm:text-base">Manage your claims and track their status</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="animate-slide-in mb-8 grid grid-cols-1 gap-4 md:grid-cols-2">
                <a class="card flex items-center gap-3 p-4 transition-colors hover:bg-gray-50" href="{{ route('claims.new') }}">
                    <div class="rounded-full bg-indigo-100 p-2 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">New Claim</h3>
                        <p class="text-sm text-gray-600">Submit a new expense claim</p>
                    </div>
                </a>

                <a class="card flex items-center gap-3 p-4 transition-colors hover:bg-gray-50"
                    href="{{ route('claims.dashboard') }}">
                    <div class="rounded-full bg-emerald-100 p-2 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">View Claims</h3>
                        <p class="text-sm text-gray-600">Manage your existing claims</p>
                    </div>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="mb-8 grid grid-cols-2 gap-6 lg:grid-cols-4">
                @if (auth()->user()->role->name === 'Staff')
                    <div class="stats-card animate-slide-in delay-100">
                        <span class="stats-label">Total Claims</span>
                        <span class="stats-value text-indigo-600">{{ $totalClaims }}</span>
                    </div>
                    <div class="stats-card animate-slide-in delay-200">
                        <span class="stats-label">Approved</span>
                        <span class="stats-value text-emerald-600">{{ $approvedClaims }}</span>
                    </div>
                    <div class="stats-card animate-slide-in delay-300">
                        <span class="stats-label">Pending</span>
                        <span class="stats-value text-amber-600">{{ $pendingClaims }}</span>
                    </div>
                    <div class="stats-card animate-slide-in delay-400">
                        <span class="stats-label">Rejected</span>
                        <span class="stats-value text-red-600">{{ $rejectedClaims }}</span>
                    </div>
                @else
                    <!-- Admin, HR, Finance stats -->
                    <div class="stats-card animate-slide-in delay-100">
                        <span class="stats-label">Total Claims</span>
                        <span class="stats-value text-indigo-600">{{ $totalClaims }}</span>
                    </div>
                    <div class="stats-card animate-slide-in delay-200">
                        <span class="stats-label">Pending Review</span>
                        <span class="stats-value text-amber-600">{{ $pendingReview }}</span>
                    </div>
                    <div class="stats-card animate-slide-in delay-300">
                        <span class="stats-label">Approved</span>
                        <span class="stats-value text-emerald-600">{{ $approvedClaims }}</span>
                    </div>
                    <div class="stats-card animate-slide-in delay-400">
                        <span class="stats-label">Total Amount</span>
                        <span class="stats-value text-purple-600">RM {{ number_format($totalAmount, 2) }}</span>
                    </div>
                @endif
            </div>


        @endauth
    </div>
@endsection
