@extends('layouts.app')

@section('title', 'Resubmit Claim')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8"
     data-requires-trip-details="{{ $latestRejection->requires_trip_details ? 'true' : 'false' }}">


    <div class="space-y-4">

        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in p-6">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 sm:text-3xl">Resubmit Claim</h2>
                    <p class="mt-1 text-sm text-gray-500 sm:text-base">Resubmit your claim with the required updates</p>
                </div>
            </div>
        </div>
        <!-- Rejection Notice & Required Updates -->
        <div class="bg-white rounded-lg animate-slide-in delay-200 space-y-6">
            <!-- Rejection Notice -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600">
                                <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Rejection Reason</p>
                                <p class="text-xs text-gray-500">{{ $latestRejection->remarks }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Required Updates Section -->
                <div class="bg-white px-4 py-3">
                    <div class="space-y-3">
                        @if($latestRejection->requires_basic_info)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100">
                                        <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600">Basic Information Update Required</span>
                                </div>
                            </div>
                        @endif

                        @if($latestRejection->requires_trip_details)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100">
                                        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600">Trip Details Update Required</span>
                                </div>
                            </div>
                        @endif

                        @if($latestRejection->requires_accommodation_details)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-100">
                                        <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600">Accommodation Details Update Required</span>
                                </div>
                            </div>
                        @endif

                        @if($latestRejection->requires_documents)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-100">
                                        <svg class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600">Documents Update Required</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <form id="resubmit-form" 
            action="{{ route('claims.resubmit.process', $claim->id) }}" 
            method="POST" 
            enctype="multipart/form-data" 
            class="space-y-4"
            data-requires-basic-info="{{ $latestRejection->requires_basic_info ? 'true' : 'false' }}"
            data-requires-trip-details="{{ $latestRejection->requires_trip_details ? 'true' : 'false' }}"
            data-requires-accommodation-details="{{ $latestRejection->requires_accommodation_details ? 'true' : 'false' }}"
            data-requires-documents="{{ $latestRejection->requires_documents ? 'true' : 'false' }}">
            @csrf

            <!-- Original Claim Details -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm animate-slide-in delay-300">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Original Claim Details</h3>
                                <p class="text-xs text-gray-500">Reference for sections not requiring revision</p>
                            </div>
                        </div>
                        <button type="button" data-toggle-details class="text-sm text-indigo-600 hover:text-indigo-900">
                            Show Details
                        </button>
                    </div>
                </div>

                <div id="originalDetails" class="hidden divide-y divide-gray-200">
                    <!-- Original claim details content - same as before but with more compact spacing -->
                    @include('components.claims.original-details', ['claim' => $claim])
                </div>
            </div>

            <!-- Sections that need revision -->
            <div class="space-y-4">
                @if($latestRejection->requires_basic_info)
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm animate-slide-in delay-400">
                        <div class="border-b border-gray-100 bg-gray-50 px-4 py-2">
                            <div class="flex items-center space-x-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Update Basic Information</h3>
                                    <p class="text-xs text-gray-500">{{ $latestRejection->basic_info_remarks ?? 'Revise the basic claim details' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <!-- Company Selection -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700" for="claim_company">Company</label>
                                    <select id="claim_company" name="claim_company"
                                        class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500" required>
                                        <option value="">Select Company</option>
                                        <option value="WGG" {{ old('claim_company', $claim->claim_company) == 'WGG' ? 'selected' : '' }}>
                                            Wegrow Global Sdn. Bhd.
                                        </option>
                                        <option value="WGE" {{ old('claim_company', $claim->claim_company) == 'WGE' ? 'selected' : '' }}>
                                            Wegrow Edutainment (M) Sdn. Bhd.
                                        </option>
                                        <option value="WGS" {{ old('claim_company', $claim->claim_company) == 'WGS' ? 'selected' : '' }}>
                                            Wegrow Studios Sdn. Bhd.
                                        </option>
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700" for="date_from">Date From</label>
                                        <input type="date" name="date_from" id="date_from" 
                                            value="{{ old('date_from', \Carbon\Carbon::parse($claim->date_from)->format('Y-m-d')) }}"
                                            class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700" for="date_to">Date To</label>
                                        <input type="date" name="date_to" id="date_to" 
                                            value="{{ old('date_to', \Carbon\Carbon::parse($claim->date_to)->format('Y-m-d')) }}"
                                            class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <!-- Description/Remarks -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700" for="description">Remarks</label>
                                    <textarea id="description" name="description" rows="3"
                                        class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 p-3 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                                        placeholder="Enter any additional notes or remarks">{{ old('description', $claim->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($latestRejection->requires_trip_details)
                    <!-- Trip Details Section -->
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm animate-slide-in delay-500">
                        <div class="border-b border-gray-100 bg-gray-50 px-4 py-2">
                            <div class="flex items-center space-x-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Update Trip Details</h3>
                                    <p class="text-xs text-gray-500">{{ $latestRejection->trip_details_remarks ?? 'Revise your route information' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <!-- Hidden Inputs -->
                            <input type="hidden" id="total-distance-input" name="total_distance" value="{{ $claim->total_distance }}">
                            <input type="hidden" id="petrol-amount-input" name="petrol_amount" value="{{ $claim->petrol_amount }}">
                            <input type="hidden" id="rate-per-km" value="{{ config('claims.rate_per_km', 0.60) }}">
                            <input type="hidden" id="segments-input" name="segments_data" value="">

                            <!-- Summary Cards -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <!-- Total Distance Card -->
                                <div class="overflow-hidden rounded-lg border border-gray-100 bg-gray-50/50 p-4">
                                    <div class="mb-2 flex items-center space-x-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                            <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700">Total Distance</p>
                                    </div>
                                    <div class="flex items-baseline">
                                        <span class="text-xl font-semibold text-gray-900" id="total-distance-display">{{ number_format($claim->total_distance, 2) }}</span>
                                        <span class="ml-1 text-base text-gray-500">km</span>
                                    </div>
                                </div>

                                <!-- Total Petrol Cost Card -->
                                <div class="overflow-hidden rounded-lg border border-gray-100 bg-gray-50/50 p-4">
                                    <div class="mb-2 flex items-center space-x-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                            <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700">Total Petrol Cost</p>
                                    </div>
                                    <div class="flex items-baseline">
                                        <span class="text-base text-gray-900">RM</span>
                                        <span class="ml-1 text-xl font-semibold text-gray-900" id="petrol-amount-display">{{ number_format($claim->petrol_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Inputs Section -->
                            <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 bg-white">
                                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Journey Details</p>
                                                <p class="text-xs text-gray-500">Add your stops and distances will be calculated automatically</p>
                                            </div>
                                        </div>
                                        <button type="button"
                                            id="add-location-btn"
                                            class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-600 transition-all hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Add Stop
                                        </button>
                                    </div>
                                </div>

                                <div class="divide-y divide-gray-100" id="location-inputs">
                                    @foreach($claim->locations as $location)
                                    <div class="location-pair relative p-5">
                                        <div class="flex items-start gap-4">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="flex h-7 w-7 items-center justify-center rounded-full {{ $loop->iteration % 2 === 1 ? 'bg-indigo-100' : 'bg-rose-100' }}">
                                                            <span class="text-sm font-medium {{ $loop->iteration % 2 === 1 ? 'text-indigo-600' : 'text-rose-600' }}">{{ chr(64 + $loop->iteration) }}</span>
                                                        </div>
                                                        <label class="block text-sm font-medium text-gray-700">Location {{ $loop->iteration }}</label>
                                                    </div>
                                                    @if($loop->iteration > 1)
                                                    <button type="button" 
                                                        class="delete-location inline-flex items-center rounded-md text-gray-400 hover:text-rose-500 focus:outline-none transition-colors">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                    @endif
                                                </div>
                                                <div class="mt-1">
                                                    <input type="text" 
                                                        class="location-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500" 
                                                        placeholder="Enter location"
                                                        value="{{ $location->from_location }}"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex items-center space-x-3 text-sm distance-info">
                                            <div class="flex items-center rounded-full bg-gray-100 px-3 py-1">
                                                <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                </svg>
                                                <span class="distance-display font-medium text-gray-600">
                                                    @if(!$loop->last)
                                                        {{ number_format($location->distance, 2) }} km to next stop
                                                    @else
                                                        N/A
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-center rounded-full bg-gray-100 px-3 py-1">
                                                <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="petrol-amount-display font-medium text-gray-600">
                                                    @if(!$loop->last)
                                                        RM {{ number_format($location->distance * config('claims.rate_per_km', 0.60), 2) }}
                                                    @else
                                                        Add next location to calculate petrol cost
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    <!-- Add this after the locations loop -->
                                    <div class="location-pair relative p-5">
                                        <div class="flex items-start gap-4">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100">
                                                            <span class="text-sm font-medium text-emerald-600">
                                                                {{ chr(64 + count($claim->locations) + 1) }}
                                                            </span>
                                                        </div>
                                                        <label class="block text-sm font-medium text-gray-700">
                                                            Final Destination
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mt-1">
                                                    <input type="text" 
                                                        class="location-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500" 
                                                        value="{{ $claim->locations->last()->to_location }}"
                                                        required>
                                                </div>
                                                <!-- Add distance display -->
                                                <div class="mt-4 flex items-center space-x-3 text-sm">
                                                    <div class="flex items-center rounded-full bg-gray-100 px-3 py-1">
                                                        <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span class="distance-display text-sm font-medium text-gray-600">
                                                            N/A
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center rounded-full bg-gray-100 px-3 py-1">
                                                        <svg class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span class="petrol-amount-display text-sm font-medium text-gray-600">
                                                            Add next location to calculate petrol cost
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($latestRejection->requires_accommodation_details)
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm animate-slide-in delay-600">
                        <div class="border-b border-gray-100 bg-gray-50 px-4 py-2">
                            <div class="flex items-center space-x-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Update Accommodation</h3>
                                    <p class="text-xs text-gray-500">{{ $latestRejection->accommodation_remarks ?? 'Revise accommodation details' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <!-- Hidden input for existing accommodations data -->
                            <input type="hidden" id="existing-accommodations" value="{{ $claim->accommodations->toJson() }}">
                            
                            <div id="accommodations-container" class="space-y-4">
                                <!-- Accommodation entries will be added here -->
                            </div>

                            <div class="flex justify-between items-center">
                                <button type="button" onclick="window.claimResubmit.addAccommodation()"
                                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Accommodation
                                </button>
                                
                                <button type="button" onclick="window.claimResubmit.removeAllAccommodations()"
                                    class="inline-flex items-center rounded-md border border-red-600 bg-white px-3 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Remove All
                                </button>
                            </div>
                            <input type="hidden" name="remove_all_accommodations" id="remove_all_accommodations" value="0">
                        </div>
                    </div>
                @endif

                @if($latestRejection->requires_documents)
                    <!-- Update Documents Section -->
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm animate-slide-in delay-700">
                        <div class="border-b border-gray-100 bg-gray-50 px-4 py-2">
                            <div class="flex items-center space-x-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">Update Documents</h3>
                                    <p class="text-xs text-gray-500">{{ $latestRejection->documents_remarks ?? 'Revise required documents' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Toll Amount (RM)</label>
                                    <input type="number" name="toll_amount" step="0.01" value="{{ old('toll_amount', $claim->toll_amount) }}"
                                        class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <!-- File upload fields -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-medium text-gray-700">Toll Receipt</label>
                                        <span class="text-xs text-gray-500">Current file will be kept if no new file is uploaded</span>
                                    </div>
                                    <input type="file" name="toll_receipt" accept=".pdf,.jpg,.jpeg,.png"
                                        class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-sm font-medium text-gray-700">Email Approval</label>
                                        <span class="text-xs text-gray-500">Current file will be kept if no new file is uploaded</span>
                                    </div>
                                    <input type="file" name="email_approval" accept=".pdf,.jpg,.jpeg,.png"
                                        class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Add hidden fields for non-revised sections -->
            @if(!$latestRejection->requires_basic_info)
                <input type="hidden" name="description" value="{{ $claim->description }}">
                <input type="hidden" name="date_from" value="{{ $claim->date_from }}">
                <input type="hidden" name="date_to" value="{{ $claim->date_to }}">
                <input type="hidden" name="claim_company" value="{{ $claim->claim_company }}">
            @endif

            @if(!$latestRejection->requires_trip_details)
                <!-- Hidden inputs for existing trip details -->
                @foreach($claim->locations as $location)
                    <input type="hidden" name="locations[]" value="{{ $location->from_location }}">
                    @if(!$loop->last)
                        <input type="hidden" name="distances[]" value="{{ $location->distance }}">
                    @endif
                @endforeach
                <input type="hidden" name="petrol_amount" value="{{ $claim->petrol_amount }}">
                <input type="hidden" name="total_distance" value="{{ $claim->total_distance }}">
            @endif

            @if(!$latestRejection->requires_documents)
                <input type="hidden" name="toll_amount" value="{{ $claim->toll_amount }}">
            @endif

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4 animate-slide-in delay-800">
                <a href="{{ route('claims.dashboard') }}"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/claim-resubmit.js'])
@endpush 