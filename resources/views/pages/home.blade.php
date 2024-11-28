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
            <div class="card animate-slide-in overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="p-6">
                    <h3 class="mb-4 text-2xl font-semibold text-gray-800">Recent Updates</h3>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <h4 class="text-base font-medium text-gray-900">New Claims Dashboard</h4>
                                <p class="mt-1 text-sm text-gray-600">Improved claims management interface with better filtering
                                    and sorting options.</p>
                                <span
                                    class="mt-2 inline-block rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-600">2
                                    days ago</span>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <h4 class="text-base font-medium text-gray-900">Enhanced Approval Process</h4>
                                <p class="mt-1 text-sm text-gray-600">Streamlined approval workflow with email notifications and
                                    status tracking.</p>
                                <span
                                    class="mt-2 inline-block rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-600">1
                                    week ago</span>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-purple-100">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </span>
                            </div>
                            <div>
                                <h4 class="text-base font-medium text-gray-900">Mobile Responsiveness</h4>
                                <p class="mt-1 text-sm text-gray-600">Added full mobile support for managing claims on the go.
                                </p>
                                <span
                                    class="mt-2 inline-block rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-600">2
                                    weeks ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3">
                    <a class="text-sm font-medium text-indigo-600 transition-colors duration-150 ease-in-out hover:text-indigo-500"
                        href="#">View all updates &rarr;</a>
                </div>
            </div>
        @endauth
    </div>
@endsection
