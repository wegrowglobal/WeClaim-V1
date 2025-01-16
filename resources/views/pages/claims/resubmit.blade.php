@extends('layouts.app')

@section('content')
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <div class="animate-slide-in flex items-center justify-between delay-100">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Resubmit Claim</h1>
                        <p class="mt-1 text-sm text-gray-500">Update and resubmit your rejected claim</p>
                    </div>
                    <a class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        href="{{ route('claims.dashboard') }}">
                        Back to Dashboard
                    </a>
                </div>
            </div>

            @if ($claim->reviews()->where('status', 'rejected')->exists())
                <div class="animate-slide-in mb-8 rounded-lg bg-white p-6 shadow-sm ring-2 ring-red-500/20 delay-100">
                    <div class="mb-4 flex items-center space-x-2">
                        <div>
                            <h3 class="text-base font-medium text-gray-900">Claim Rejected</h3>
                            <p class="mt-1 text-sm text-gray-500">Information about the rejection</p>
                        </div>
                    </div>
                    <div class="space-y-4 text-sm">
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                            <span class="font-medium text-gray-600">Rejected By:</span>
                            <span class="font-semibold text-gray-800">
                                {{ $claim->reviews()->where('status', 'rejected')->latest()->first()->reviewer->name }}
                                <span
                                    class="ml-1 text-gray-800">{{ $claim->reviews()->where('status', 'rejected')->latest()->first()->reviewer->role->name }}</span>
                            </span>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-3">
                            <span class="font-medium text-gray-600">Rejection Reason:</span>
                            <p class="mt-2 font-semibold text-gray-800">
                                {{ $claim->reviews()->where('status', 'rejected')->latest()->first()->remarks }}</p>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                            <span class="font-medium text-gray-600">Rejected Date:</span>
                            <span
                                class="font-semibold text-gray-800">{{ $claim->reviews()->where('status', 'rejected')->latest()->first()->created_at->format('M j, Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center rounded-lg bg-red-50 p-4">
                        <svg class="mr-2 h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-medium text-red-700">Please review and make necessary changes before
                            resubmitting.</p>
                    </div>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('claims.process-resubmission', $claim->id) }}" method="POST">
                @csrf

                <div class="animate-slide-in space-y-6 rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5 delay-100">
                    <div>
                        <h2 class="text-base font-medium text-gray-900">Trip Details</h2>
                        <p class="mt-1 text-sm text-gray-500">Review and update your route</p>
                    </div>

                    <!-- Updated Hidden Inputs -->
                    <input id="locations" name="locations" type="hidden">
                    <input id="total-distance-input" name="total_distance" type="hidden">
                    <input id="petrol-amount-input" name="petrol_amount" type="hidden">
                    <input id="segments-data" name="segments_data" type="hidden">

                    <!-- Location Inputs -->
                    <div class="space-y-4" id="location-inputs">
                        <!-- Locations will be added dynamically via JavaScript -->
                    </div>

                    <!-- Location Controls -->
                    <div class="flex gap-3">
                        <button
                            class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-600 transition-all hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            id="add-location-btn" type="button">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Stop
                        </button>
                    </div>

                    <!-- Map Container -->
                    <div class="space-y-4 overflow-hidden rounded-lg">
                        <div class="relative">
                            <div class="h-[400px] w-full rounded-lg border border-gray-100 shadow-sm" id="map"></div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <!-- Total Distance -->
                            <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                                <div class="mb-2 flex items-center space-x-3">
                                    <div class="rounded-lg bg-indigo-50 p-2">
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-600">Total Distance</p>
                                </div>
                                <div class="flex items-baseline">
                                    <span class="text-xl font-semibold text-gray-900"
                                        id="total-distance">{{ number_format($claim->total_distance, 2) }}</span>
                                    <span class="ml-1 text-base text-gray-500">km</span>
                                </div>
                            </div>

                            <!-- Total Duration -->
                            <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                                <div class="mb-2 flex items-center space-x-3">
                                    <div class="rounded-lg bg-indigo-50 p-2">
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-600">Duration</p>
                                </div>
                                <div class="flex items-baseline">
                                    <span class="text-xl font-semibold text-gray-900"
                                        id="total-duration">{{ $claim->total_duration }}</span>
                                </div>
                            </div>

                            <!-- Total Cost -->
                            <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                                <div class="mb-2 flex items-center space-x-3">
                                    <div class="rounded-lg bg-indigo-50 p-2">
                                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-600">Total Estimated Cost</p>
                                </div>
                                <div class="flex items-baseline">
                                    <span class="text-xl font-semibold text-gray-900">RM</span>
                                    <span class="ml-1 text-xl font-semibold text-gray-900"
                                        id="total-cost">{{ number_format($claim->total_cost, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Pairs Info -->
                <div class="animate-slide-in mt-6 space-y-6 rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5 delay-100"
                    id="location-pairs-info">
                    <div>
                        <h2 class="text-base font-medium text-gray-900">Segment Info</h2>
                        <p class="mt-1 text-sm text-gray-500">View details for each segment of your journey</p>
                    </div>
                    <div class="space-y-4" id="segment-details">
                        <!-- Segments will be added here dynamically -->
                    </div>
                </div>

                <div
                    class="animate-slide-in grid w-full grid-cols-1 gap-6 rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5 delay-100">
                    <div>
                        <h2 class="text-base font-medium text-gray-900">Resubmit Form</h2>
                        <p class="mt-1 text-sm text-gray-500">Review and fill in the form to resubmit your claim</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <!-- Company Selection -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="claim_company">Company</label>
                            <select
                                class="form-input block h-[38px] w-full rounded-lg border border-gray-200 bg-gray-50/50 transition-all focus:border-gray-400 focus:bg-white sm:text-sm"
                                id="claim_company" name="claim_company">
                                <option value="WGG" {{ $claim->claim_company == 'WGG' ? 'selected' : '' }}>Wegrow Global
                                    Sdn. Bhd.</option>
                                <option value="WGE" {{ $claim->claim_company == 'WGE' ? 'selected' : '' }}>Wegrow
                                    Edutainment (M) Sdn. Bhd.</option>
                                <option value="WGG & WGE" {{ $claim->claim_company == 'WGG & WGE' ? 'selected' : '' }}>
                                    Both</option>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="date_from">Date From</label>
                            <input
                                class="form-input block h-[38px] w-full rounded-lg border border-gray-200 bg-gray-50/50 transition-all focus:border-gray-400 focus:bg-white sm:text-sm"
                                id="date_from" name="date_from" type="date"
                                value="{{ $claim->date_from->format('Y-m-d') }}">
                        </div>

                        <!-- Date To -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="date_to">Date To</label>
                            <input
                                class="form-input block h-[38px] w-full rounded-lg border border-gray-200 bg-gray-50/50 transition-all focus:border-gray-400 focus:bg-white sm:text-sm"
                                id="date_to" name="date_to" type="date"
                                value="{{ $claim->date_to->format('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Amounts -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700" for="toll_amount">Toll Amount
                                (RM)</label>
                            <input
                                class="form-input block h-[38px] w-full rounded-lg border border-gray-200 bg-gray-50/50 transition-all focus:border-gray-400 focus:bg-white sm:text-sm"
                                id="toll_amount" name="toll_amount" type="number" value="{{ $claim->toll_amount }}"
                                step="0.01">
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700" for="description">Description</label>
                        <textarea
                            class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-all focus:border-gray-400 focus:bg-white sm:text-sm"
                            id="description" name="description" rows="3">{{ old('description', $claim->description) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <button
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        type="button" onclick="resubmitClaim({{ $claim->id }})">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Resubmit Claim
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@php
    $locationData = $claim->locations
        ->sortBy('order')
        ->map(
            fn($location) => [
                'from_location' => $location->from_location,
                'to_location' => $location->to_location,
                'order' => $location->order,
                'distance' => $location->distance,
            ],
        )
        ->values()
        ->toArray();
@endphp

@push('scripts')
    @vite(['resources/js/maps/resubmit-map.js', 'resources/js/claim-resubmit.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.existingLocations = @json($locationData);

            // Set initial values for hidden inputs
            document.getElementById('locations').value = JSON.stringify(window.existingLocations);
            document.getElementById('total-distance-input').value = '{{ $claim->total_distance }}';
            document.getElementById('petrol-amount-input').value = '{{ $claim->petrol_amount }}';

            console.log(document.getElementById('total-distance-input').value);
            console.log(document.getElementById('petrol-amount-input').value);
        });
    </script>
@endpush
