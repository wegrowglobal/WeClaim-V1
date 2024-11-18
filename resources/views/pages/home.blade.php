@extends('layouts.app')

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @guest
        <div class="card p-8 space-y-4 animate-slide-in">
            <div class="flex items-center gap-4">
                <h2 class="heading-1">Welcome to WeClaims</h2>
                <span class="status-badge bg-red-50 text-red-700">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm0-8a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V8a1 1 0 0 1 1-1Z"/>
                    </svg>
                    Not Logged In
                </span>
            </div>
            <p class="text-gray-600">Please log in to access your claims dashboard and submit new claims.</p>
            <div class="flex gap-4 mt-6">
                <a href="{{ route('login') }}" class="btn-primary">
                    Sign In
                </a>
                <a href=" " class="btn-secondary">
                    Create Account
                </a>
            </div>
        </div>
    @endguest

    @auth
        <!-- User Welcome Section -->
        <div class="card p-8 mb-8 animate-slide-in">
            <div class="flex items-center gap-6">
                <x-profile.profile-picture :user="auth()->user()" size="lg" />
                <div>
                    <h2 class="heading-1">Welcome back, {{ auth()->user()->first_name }}</h2>
                    <p class="text-gray-600 mt-1">Manage your claims and track their status</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <a href="{{ route('claims.new') }}" 
               class="card p-4 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                <div class="p-2 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">New Claim</h3>
                    <p class="text-sm text-gray-600">Submit a new expense claim</p>
                </div>
            </a>
            
            <a href="{{ route('claims.dashboard') }}" 
               class="card p-4 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                <div class="p-2 rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">View Claims</h3>
                    <p class="text-sm text-gray-600">Manage your existing claims</p>
                </div>
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
        </div>

        <!-- Recent Updates -->
        <div id="changelog" class="card p-8 animate-slide-in delay-300">
            <h3 class="heading-2 mb-6">Recent Updates</h3>
            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-full bg-blue-50">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">New Claims Dashboard</h4>
                        <p class="text-sm text-gray-600 mt-1">Improved claims management interface with better filtering and sorting options.</p>
                        <span class="text-xs text-gray-500 mt-2 block">2 days ago</span>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-full bg-green-50">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Enhanced Approval Process</h4>
                        <p class="text-sm text-gray-600 mt-1">Streamlined approval workflow with email notifications and status tracking.</p>
                        <span class="text-xs text-gray-500 mt-2 block">1 week ago</span>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="p-2 rounded-full bg-purple-50">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Mobile Responsiveness</h4>
                        <p class="text-sm text-gray-600 mt-1">Added full mobile support for managing claims on the go.</p>
                        <span class="text-xs text-gray-500 mt-2 block">2 weeks ago</span>
                    </div>
                </div>
            </div>
        </div>
    @endauth
</div>
@endsection