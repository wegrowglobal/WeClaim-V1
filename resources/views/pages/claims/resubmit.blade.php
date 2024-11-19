@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center justify-between animate-slide-in delay-100">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Resubmit Claim</h1>
                    <p class="mt-1 text-sm text-gray-500">Update and resubmit your rejected claim</p>
                </div>
                <a href="{{ route('claims.dashboard') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 hover:bg-gray-50">
                    Back to Dashboard
                </a>
            </div>
        </div>

        @if($claim->reviews()->where('status', 'rejected')->exists())
            <div class="bg-white rounded-lg p-6 mb-8 shadow-sm ring-2 ring-red-500/20 animate-slide-in delay-100">
                <div class="flex items-center mb-4 space-x-2">
                    <div>
                        <h3 class="text-base font-medium text-gray-900">Claim Rejected</h3>
                        <p class="text-sm text-gray-500 mt-1">Information about the rejection</p>
                    </div>
                </div>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                        <span class="text-gray-600 font-medium">Rejected By:</span>
                        <span class="font-semibold text-gray-800">
                            {{ $claim->reviews()->where('status', 'rejected')->latest()->first()->reviewer->name }}
                            <span class="text-gray-800 ml-1">{{ $claim->reviews()->where('status', 'rejected')->latest()->first()->reviewer->role->name }}</span>
                        </span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-gray-600 font-medium">Rejection Reason:</span>
                        <p class="mt-2 font-semibold text-gray-800">{{ $claim->reviews()->where('status', 'rejected')->latest()->first()->remarks }}</p>
                    </div>
                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg">
                        <span class="text-gray-600 font-medium">Rejected Date:</span>
                        <span class="font-semibold text-gray-800">{{ $claim->reviews()->where('status', 'rejected')->latest()->first()->created_at->format('M j, Y H:i') }}</span>
                    </div>
                </div>
                <div class="mt-6 flex items-center bg-red-50 p-4 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm font-medium text-red-700">Please review and make necessary changes before resubmitting.</p>
                </div>
            </div>
        @endif

        <form action="{{ route('claims.process-resubmission', $claim->id) }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-6 bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-100 p-6">
                <div>
                    <h2 class="text-base font-medium text-gray-900">Trip Details</h2>
                    <p class="text-sm text-gray-500 mt-1">Review and update your route</p>
                </div>

                <!-- Updated Hidden Inputs -->
                <input type="hidden" id="locations" name="locations">
                <input type="hidden" id="total-distance-input" name="total_distance">
                <input type="hidden" id="petrol-amount-input" name="petrol_amount">
                <input type="hidden" id="segments-data" name="segments_data">

                <!-- Location Inputs -->
                <div id="location-inputs" class="space-y-4">
                    <!-- Locations will be added dynamically via JavaScript -->
                </div>

                <!-- Location Controls -->
                <div class="flex gap-3">
                    <button type="button" 
                            id="add-location-btn"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Stop
                    </button>
                </div>

                <!-- Map Container -->
                <div class="space-y-4 rounded-lg overflow-hidden">
                    <div class="relative">
                        <div id="map" class="h-[400px] w-full rounded-lg shadow-sm border border-gray-100"></div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <!-- Total Distance -->
                        <div class="bg-gray-50/50 rounded-xl p-4 hover:bg-gray-50 transition-all">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="p-2 bg-indigo-50 rounded-lg">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Total Distance</p>
                            </div>
                            <div class="flex items-baseline">
                                <span id="total-distance" class="text-xl font-semibold text-gray-900">{{ number_format($claim->total_distance, 2) }}</span>
                                <span class="ml-1 text-base text-gray-500">km</span>
                            </div>
                        </div>

                        <!-- Total Duration -->
                        <div class="bg-gray-50/50 rounded-xl p-4 hover:bg-gray-50 transition-all">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="p-2 bg-indigo-50 rounded-lg">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Duration</p>
                            </div>
                            <div class="flex items-baseline">
                                <span id="total-duration" class="text-xl font-semibold text-gray-900">{{ $claim->total_duration }}</span>
                            </div>
                        </div>

                        <!-- Total Cost -->
                        <div class="bg-gray-50/50 rounded-xl p-4 hover:bg-gray-50 transition-all">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="p-2 bg-indigo-50 rounded-lg">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Total Estimated Cost</p>
                            </div>
                            <div class="flex items-baseline">
                                <span class="text-xl font-semibold text-gray-900">RM</span>
                                <span id="total-cost" class="text-xl font-semibold text-gray-900 ml-1">{{ number_format($claim->total_cost, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Pairs Info -->
            <div id="location-pairs-info" class="mt-6 space-y-6 bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-100 p-6">
                <div>
                    <h2 class="text-base font-medium text-gray-900">Segment Info</h2>
                    <p class="text-sm text-gray-500 mt-1">View details for each segment of your journey</p>
                </div>
                <div id="segment-details" class="space-y-4">
                    <!-- Segments will be added here dynamically -->
                </div>
            </div>

            
            <div class="grid grid-cols-1 gap-6 w-full bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-100 p-6">
                <div>
                    <h2 class="text-base font-medium text-gray-900">Resubmit Form</h2>
                    <p class="text-sm text-gray-500 mt-1">Review and fill in the form to resubmit your claim</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Company Selection -->
                    <div class="space-y-2">
                        <label for="claim_company" class="block text-sm font-medium text-gray-700">Company</label>
                        <select id="claim_company" name="claim_company" 
                                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px]">
                            <option value="WGG" {{ $claim->claim_company == 'WGG' ? 'selected' : '' }}>Wegrow Global Sdn. Bhd.</option>
                            <option value="WGE" {{ $claim->claim_company == 'WGE' ? 'selected' : '' }}>Wegrow Edutainment (M) Sdn. Bhd.</option>
                            <option value="WGG & WGE" {{ $claim->claim_company == 'WGG & WGE' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="space-y-2">
                        <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                        <input type="date" id="date_from" name="date_from" value="{{ $claim->date_from->format('Y-m-d') }}" 
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px]">
                    </div>

                    <!-- Date To -->
                    <div class="space-y-2">
                        <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                        <input type="date" id="date_to" name="date_to" value="{{ $claim->date_to->format('Y-m-d') }}" 
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px]">
                    </div>
                </div>

                <!-- Amounts -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="space-y-2">
                        <label for="toll_amount" class="block text-sm font-medium text-gray-700">Toll Amount (RM)</label>
                        <input type="number" id="toll_amount" step="0.01" name="toll_amount" value="{{ $claim->toll_amount }}" 
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px]">
                    </div>
                </div>

                <!-- Remarks -->
                <div class="space-y-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3" 
                              class="form-input p-4 block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm">{{ old('description', $claim->description) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end pt-6">
                <button type="button" 
                        onclick="resubmitClaim({{ $claim->id }})"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
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
        ->map(fn($location) => [
            'from_location' => $location->from_location,
            'to_location' => $location->to_location,
            'order' => $location->order,
            'distance' => $location->distance
        ])
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