@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        @guest
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in space-y-4 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 sm:text-3xl">Welcome to WeClaims</h2>
                        <p class="mt-1 text-sm text-gray-500 sm:text-base">Please log in to access your claims dashboard and submit new claims.</p>
                    </div>
                </div>
                <div class="mt-6 flex gap-4">
                    <a class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700" href="{{ route('login') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Sign In
                    </a>
                    <a class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 transition-all hover:bg-gray-50" href="{{ route('register') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Create Account
                    </a>
                </div>
            </div>
        @endguest

        @auth
            <!-- User Welcome Section -->
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in mb-8 p-4 sm:p-6">
                <div class="flex flex-col items-center gap-3 text-center sm:flex-row sm:gap-6 sm:text-left">
                    <x-profile.profile-picture :user="auth()->user()" size="md" />
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 sm:text-2xl md:text-3xl">Welcome back, {{ auth()->user()->first_name }}</h2>
                        <p class="mt-1 text-sm text-gray-500 sm:text-base">Manage your claims and track their status</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="animate-slide-in delay-100 mb-8 grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">
                <a class="group overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-all hover:shadow-md" href="{{ route('claims.new') }}">
                    <div class="flex items-center gap-3 p-4 sm:gap-4 sm:p-6">
                        <div class="rounded-lg bg-indigo-50 p-2 sm:p-3 text-indigo-600 ring-1 ring-indigo-500/10 transition-all group-hover:bg-indigo-500 group-hover:text-white">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">New Claim</h3>
                            <p class="text-xs sm:text-sm text-gray-500">Submit a new expense claim</p>
                        </div>
                    </div>
                </a>

                <a class="group overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-all hover:shadow-md" href="{{ route('claims.dashboard') }}">
                    <div class="flex items-center gap-3 p-4 sm:gap-4 sm:p-6">
                        <div class="rounded-lg bg-emerald-50 p-2 sm:p-3 text-emerald-600 ring-1 ring-emerald-500/10 transition-all group-hover:bg-emerald-500 group-hover:text-white">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">View Claims</h3>
                            <p class="text-xs sm:text-sm text-gray-500">Manage your existing claims</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @if (auth()->user()->role->name === 'Staff')
                    <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 ring-1 ring-indigo-500/10">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Total</p>
                                <p class="text-lg font-semibold text-indigo-600">{{ $totalClaims }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-300">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 ring-1 ring-emerald-500/10">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Approved</p>
                                <p class="text-lg font-semibold text-emerald-600">{{ $approvedClaims }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-400">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-amber-50 p-2 text-amber-600 ring-1 ring-amber-500/10">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Pending</p>
                                <p class="text-lg font-semibold text-amber-600">{{ $pendingClaims }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-500">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-red-50 p-2 text-red-600 ring-1 ring-red-500/10">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Rejected</p>
                                <p class="text-lg font-semibold text-red-600">{{ $rejectedClaims }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Admin, HR, Finance stats -->
                    <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 ring-1 ring-indigo-500/10">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Total</p>
                                <p class="text-lg font-semibold text-indigo-600">{{ $totalClaims }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-300">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-amber-50 p-2 text-amber-600 ring-1 ring-amber-500/10">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Pending</p>
                                <p class="text-lg font-semibold text-amber-600">{{ $pendingReview }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-400">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 ring-1 ring-emerald-500/10">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Approved</p>
                                <p class="text-lg font-semibold text-emerald-600">{{ $approvedClaims }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-500">
                        <div class="flex items-center gap-2">
                            <div class="rounded-lg bg-purple-50 p-2 text-purple-600 ring-1 ring-purple-500/10">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500">Amount</p>
                                <p class="text-lg font-semibold text-purple-600">RM {{ number_format($totalAmount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endauth
    </div>
@endsection
