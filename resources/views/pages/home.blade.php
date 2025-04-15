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
                    <a class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700" href="{{ route('login.form') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Sign In
                    </a>
                    <a class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 transition-all hover:bg-gray-50" href="{{ route('register.form') }}">
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
                        <p class="mt-1 text-sm text-gray-500 sm:text-base">
                            @if(auth()->user()->role_id === 1)
                                Manage your claims and track their status.
                            @else
                                Review and manage claims for your team.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if(auth()->user()->role_id === 1)
                <!-- Staff View -->
                <div class="space-y-8">
                    <!-- Quick Actions -->
                    <div class="animate-slide-in delay-100 grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">
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
                                    <h3 class="font-medium text-gray-900">View My Claims</h3>
                                    <p class="text-xs sm:text-sm text-gray-500">Track your submitted claims</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 ring-1 ring-indigo-500/10">
                                    <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Total Claims</p>
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
                                    <p class="text-lg font-semibold text-amber-600">{{ $pendingClaims }}</p>
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
                    </div>

                    <!-- Recent Claims List (Optional) -->
                    {{-- You can add a component here to display recent claims --}}

                </div>

            @elseif(auth()->user()->role_id === 5) {{-- System Admin View --}}
                <div class="space-y-8">
                    <!-- Quick Actions -->
                    <div class="animate-slide-in delay-100 grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">
                        <a class="group overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-all hover:shadow-md" href="{{ route('admin.users.index') }}">
                            <div class="flex items-center gap-3 p-4 sm:gap-4 sm:p-6">
                                <div class="rounded-lg bg-orange-50 p-2 sm:p-3 text-orange-600 ring-1 ring-orange-500/10 transition-all group-hover:bg-orange-500 group-hover:text-white">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">Manage Users</h3>
                                    <p class="text-xs sm:text-sm text-gray-500">User accounts and roles</p>
                                </div>
                            </div>
                        </a>
                         <a class="group overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-all hover:shadow-md" href="{{ route('admin.system.config') }}"> {{-- Adjust route if needed --}}
                            <div class="flex items-center gap-3 p-4 sm:gap-4 sm:p-6">
                                <div class="rounded-lg bg-teal-50 p-2 sm:p-3 text-teal-600 ring-1 ring-teal-500/10 transition-all group-hover:bg-teal-500 group-hover:text-white">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">System Config</h3>
                                    <p class="text-xs sm:text-sm text-gray-500">Configure application settings</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-3">
                        {{-- System Admin Stats Cards --}}
                        <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-orange-50 p-2 text-orange-600 ring-1 ring-orange-500/10">
                                    <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Total Users</p>
                                    <p class="text-lg font-semibold text-orange-600">{{ $totalUsers ?? 'N/A' }}</p> {{-- Controller needs to pass $totalUsers --}}
                                </div>
                            </div>
                        </div>
                         <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-300">
                              <div class="flex items-center gap-2">
                                  <div class="rounded-lg bg-cyan-50 p-2 text-cyan-600 ring-1 ring-cyan-500/10">
                                      <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                  </div>
                                  <div>
                                      <p class="text-xs font-medium text-gray-500">Pending Reg.</p>
                                      <p class="text-lg font-semibold text-cyan-600">{{ $pendingRegistrations ?? 'N/A' }}</p> {{-- Controller needs to pass $pendingRegistrations --}}
                                  </div>
                              </div>
                          </div>
                        <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-400">
                             <div class="flex items-center gap-2">
                                 <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 ring-1 ring-indigo-500/10">
                                     <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                 </div>
                                 <div>
                                     <p class="text-xs font-medium text-gray-500">Total Claims</p>
                                     <p class="text-lg font-semibold text-indigo-600">{{ $totalClaims }}</p>
                                 </div>
                             </div>
                         </div>
                    </div>
                     <!-- Maybe add system logs or other admin-specific sections -->
                </div>

            @else {{-- Other Approvers (Non-Staff, Non-Admin) --}}
                 <div class="space-y-8">
                     <!-- Quick Actions -->
                    <div class="animate-slide-in delay-100 grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-2">
                        <a class="group overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-all hover:shadow-md" href="{{ route('claims.approval') }}">
                            <div class="flex items-center gap-3 p-4 sm:gap-4 sm:p-6">
                                <div class="rounded-lg bg-cyan-50 p-2 sm:p-3 text-cyan-600 ring-1 ring-cyan-500/10 transition-all group-hover:bg-cyan-500 group-hover:text-white">
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">Claim Approval</h3>
                                    <p class="text-xs sm:text-sm text-gray-500">Review and approve/reject claims</p>
                                </div>
                            </div>
                        </a>
                        {{-- Add other relevant actions for this role group if needed --}}
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                         {{-- Approver Stats Cards --}}
                        <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-amber-50 p-2 text-amber-600 ring-1 ring-amber-500/10">
                                    <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Pending Review</p>
                                    <p class="text-lg font-semibold text-amber-600">{{ $pendingReview }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-300">
                             <div class="flex items-center gap-2">
                                 <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 ring-1 ring-indigo-500/10">
                                     <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                 </div>
                                 <div>
                                     <p class="text-xs font-medium text-gray-500">Total Claims</p>
                                     <p class="text-lg font-semibold text-indigo-600">{{ $totalClaims }}</p>
                                 </div>
                             </div>
                         </div>
                        <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-400">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 ring-1 ring-emerald-500/10">
                                    <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Total Approved</p>
                                    <p class="text-lg font-semibold text-emerald-600">{{ $approvedClaims }}</p>
                                </div>
                            </div>
                        </div>
                         <div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm ring-1 ring-black/5 animate-slide-in delay-500">
                            <div class="flex items-center gap-2">
                                <div class="rounded-lg bg-purple-50 p-2 text-purple-600 ring-1 ring-purple-500/10">
                                    <svg class="h-4 w-4 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Total Amount</p>
                                    <p class="text-lg font-semibold text-purple-600">RM {{ number_format($totalAmount, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Queue -->
                    <div class="animate-slide-in delay-600">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Pending Your Review</h3>
                        @if(isset($pendingClaims) && !$pendingClaims->isEmpty())
                            <x-claims.claims-table :claims="$pendingClaims" :claimService="$claimService" actions="approval" />
                        @else
                            <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 p-6 text-center">
                                <p class="text-gray-500">No claims are currently pending your review.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endauth
    </div>
@endsection
