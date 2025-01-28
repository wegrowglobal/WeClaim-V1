<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
    <div class="px-6 py-5" data-step="2">
        @php
            $draftData = $draftData ?? [];
        @endphp
        
        <script>
            console.log('Step 2 - Initial draft data:', @json($draftData));
        </script>

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Trip Details</h3>
                <p class="text-sm text-gray-500 mt-1">Plan your route and calculate travel distance</p>
            </div>
        </div>

        @php
            // Properly handle the draft data
            $draftData = $draftData ?? [];

            // Handle locations data
            $locationsData = $draftData['locations'] ?? '[]';
            if (is_array($locationsData)) {
                $locationsData = json_encode($locationsData);
            }

            // Decode for filtering if it's a JSON string
            $decodedLocations = is_string($locationsData) ? json_decode($locationsData, true) : [];
            $filteredLocations = array_filter($decodedLocations ?? []);

            // Get other values with defaults
            $totalDistance = $draftData['total_distance'] ?? '0';
            $totalDuration = $draftData['total_duration'] ?? '0 min';

            // Get total cost from the sum of segment costs
            $totalCost = isset($draftData['total_cost']) ? $draftData['total_cost'] : '0.00';
        @endphp

        <!-- Hidden Inputs -->
        @php
            // Ensure accommodations data is preserved
            $accommodations = [];
            if (isset($draftData['accommodations'])) {
                $accommodations = is_string($draftData['accommodations']) 
                    ? json_decode($draftData['accommodations'], true) 
                    : $draftData['accommodations'];
            }
        @endphp
        <input id="draftData" type="hidden" value="{{ json_encode([
            'claim_company' => $draftData['claim_company'] ?? '',
            'date_from' => $draftData['date_from'] ?? '',
            'date_to' => $draftData['date_to'] ?? '',
            'remarks' => $draftData['remarks'] ?? '',
            'total_distance' => $totalDistance,
            'total_cost' => $totalCost,
            'segments_data' => $draftData['segments_data'] ?? '[]',
            'locations' => $filteredLocations,
            'accommodations' => $accommodations
        ]) }}">
        <input id="locations" name="locations" type="hidden" value="{{ json_encode($filteredLocations) }}">
        <input id="total-distance-input" name="total_distance" type="hidden" value="{{ $totalDistance }}">
        <input id="total-duration-input" name="total_duration" type="hidden" value="{{ $totalDuration }}">
        <input id="total-cost-input" name="total_cost" type="hidden" value="{{ $totalCost }}">

        <!-- Main Content Section -->
        <div class="space-y-6">
            <!-- Location Controls Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Location Stops</p>
                            <p class="text-xs text-gray-500">Add and manage your journey stops</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Location Inputs -->
                    <div class="" id="location-inputs">
                        <!-- Locations will be added dynamically via JavaScript -->
                    </div>

                    <!-- Location Controls -->
                    <div class="pl-4 pb-4 flex gap-3">
                        <button
                            class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-600 transition-all hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            id="add-location-btn" type="button">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Stop
                        </button>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Route Map</p>
                            <p class="text-xs text-gray-500">View and verify your travel route</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <!-- Map Container -->
                    <div class="relative">
                        <div class="h-[400px] w-full rounded-lg border border-gray-100 shadow-sm" id="map">
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <!-- Total Distance -->
                        <div class="overflow-hidden rounded-lg border border-gray-100 bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                            <div class="mb-2 flex items-center space-x-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                    <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Total Distance</p>
                            </div>
                            <div class="flex items-baseline">
                                <span class="text-xl font-semibold text-gray-900"
                                    id="total-distance">{{ sprintf('%.2f', (float) $totalDistance) }}</span>
                                <span class="ml-1 text-base text-gray-500">km</span>
                            </div>
                        </div>

                        <!-- Total Duration -->
                        <div class="overflow-hidden rounded-lg border border-gray-100 bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                            <div class="mb-2 flex items-center space-x-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                    <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Total Duration</p>
                            </div>
                            <div class="flex items-baseline">
                                <span class="text-xl font-semibold text-gray-900" id="total-duration">{{ $totalDuration }}</span>
                            </div>
                        </div>

                        <!-- Total Cost -->
                        <div class="overflow-hidden rounded-lg border border-gray-100 bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                            <div class="mb-2 flex items-center space-x-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                    <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-700">Total Estimated Cost</p>
                            </div>
                            <div class="flex items-baseline">
                                <span class="text-xl font-semibold text-gray-900">RM</span>
                                <span class="ml-1 text-xl font-semibold text-gray-900" id="total-cost"
                                    data-cost-display>{{ sprintf('%.2f', (float) $totalCost) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add a hidden input to store the rate -->
            <input id="rate-per-km" type="hidden" value="0.60">

            <!-- Segment Info Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm" id="location-pairs-info" style="display: none;">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Segment Info</p>
                            <p class="text-xs text-gray-500">View details for each segment of your journey</p>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="space-y-4" id="segment-details">
                        <!-- Segments will be added here dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-6">
            <button
                class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                type="button" onclick="window.claimForm.previousStep(2)">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Previous
            </button>

            <button
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                id="next-step-button" type="button" onclick="window.claimForm.nextStep(2)">
                Next Step
                <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>
        </div>
    </div>
</div>
