@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in mb-8 p-6">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 sm:text-3xl">Bulk Email to Datuk</h2>
                    <p class="mt-1 text-sm text-gray-500 sm:text-base">Send multiple HR-approved claims to Datuk for review</p>
                </div>
            </div>

            <!-- Info Box -->
            <div class="overflow-hidden rounded-lg border border-blue-100 bg-blue-50/50 shadow-sm mt-4">
                <div class="border-b border-blue-100 bg-blue-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Resend Policy</p>
                                <p class="text-xs text-gray-500">Guidelines for resending claims to Datuk</p>
                            </div>
                        </div>
                        <button class="text-gray-400 hover:text-gray-600" type="button" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="bg-white p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm text-gray-900">Waiting Period</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Claims will be monitored for 3 days after being sent to Datuk</p>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-3">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="text-sm text-gray-900">Long-Pending Claims</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Claims pending for more than 3 days will be highlighted in red and available for resending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claims Table Section -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
            @if($claims->isEmpty())
                <div class="flex flex-col items-center justify-center py-12">
                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No Claims Ready</h3>
                    <p class="mt-1 text-sm text-gray-500">There are no HR-approved claims ready to be sent to Datuk.</p>
                </div>
            @else
                <!-- Search Input -->
                <div class="border-b border-gray-100 p-6">
                    <div class="relative focus-within:shadow-sm">
                        <input
                            class="w-full rounded-lg border border-gray-200 py-2.5 pl-10 pr-4 text-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            id="searchInput" type="text" placeholder="Search claims...">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="claimsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-16 px-3 py-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </div>
                                </th>
                                <th class="w-16 px-3 py-2 text-left text-xs font-medium text-gray-500" scope="col">ID</th>
                                <th class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">Date</th>
                                <th class="w-32 px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">Title</th>
                                <th class="w-40 px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">Period</th>
                                <th class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">Amount</th>
                                <th class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">Status</th>
                                <th class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">Sent Date</th>
                                <th class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">Pending</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($claims as $claim)
                                <tr class="text-xs hover:bg-gray-50/50">
                                    <td class="px-3 py-2">
                                        @if($claim->status === App\Models\Claim::STATUS_APPROVED_HR || ($claim->status === App\Models\Claim::STATUS_PENDING_DATUK && $claim->isLongPending()))
                                            <input type="checkbox" name="claims[]" value="{{ $claim->id }}" 
                                                data-amount="{{ $claim->petrol_amount + $claim->toll_amount }}"
                                                class="claim-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ $claim->id }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-gray-600">{{ $claim->submitted_at->format('d/m/y') }}</td>
                                    <td class="whitespace-nowrap px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <x-profile.profile-picture :user="$claim->user" size="sm" />
                                            <span class="text-gray-600">{{ $claim->user->first_name }} {{ $claim->user->second_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="max-w-[250px] truncate text-gray-600">{{ $claim->title }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                        {{ $claim->date_from->format('d/m/y') }} - {{ $claim->date_to->format('d/m/y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                        RM {{ number_format($claim->petrol_amount + $claim->toll_amount, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <x-claims.status-badge :status="$claim->status" />
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                        @if($claim->status === App\Models\Claim::STATUS_PENDING_DATUK)
                                            {{ $claim->updated_at->format('d/m/y H:i') }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if($claim->status === App\Models\Claim::STATUS_PENDING_DATUK)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $claim->isLongPending() ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-700' }}">
                                                <svg class="mr-1.5 h-3.5 w-3.5 {{ $claim->isLongPending() ? 'text-red-400' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $claim->getPendingDuration() }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Floating Action Bar -->
        <div id="floatingActionBar" class="fixed bottom-0 right-0 z-50 w-full transform translate-y-full transition-transform duration-300 ease-in-out">
            <div class="mx-auto w-full px-4 pb-6">
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                    <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <!-- Selected Claims -->
                            <div class="flex items-center space-x-8">
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex items-baseline space-x-1">
                                        <span id="selectedCount" class="text-2xl font-semibold text-indigo-600">0</span>
                                        <span class="text-sm text-gray-500">claims selected</span>
                                    </div>
                                </div>

                                <div class="h-8 w-px bg-gray-200"></div>

                                <!-- Total Amount -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-600">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex items-baseline space-x-1">
                                        <span id="selectedAmount" class="text-2xl font-semibold text-green-600">RM 0.00</span>
                                        <span class="text-sm text-gray-500">total</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Send Button -->
                            <button
                                onclick="sendSelectedClaims()"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Send Selected Claims
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/bulk-email.js'])
    @endpush
@endsection 