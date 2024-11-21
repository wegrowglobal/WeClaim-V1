<div class="p-6 space-y-6">
    <div>
        <h2 class="text-base font-medium text-gray-900">Trip Details</h2>
        <p class="text-sm text-gray-500 mt-1">Plan your route and calculate travel distance</p>
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
    <input type="hidden" 
           id="draftData" 
           value="{{ is_array($draftData) ? json_encode($draftData) : '{}' }}">
    <input type="hidden" 
           id="locations" 
           name="locations" 
           value="{{ json_encode($filteredLocations) }}">
    <input type="hidden" 
           id="total-distance-input" 
           name="total_distance" 
           value="{{ $totalDistance }}">
    <input type="hidden" 
           id="total-duration-input" 
           name="total_duration" 
           value="{{ $totalDuration }}">
    <input type="hidden" 
           id="total-cost-input" 
           name="total_cost" 
           value="{{ $totalCost }}">
    <input type="hidden" 
           id="segments-data" 
           name="segments_data" 
           value="{{ old('segments_data', $draftData['segments_data'] ?? '[]') }}">

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
            <div id="map" 
                 class="h-[400px] w-full rounded-lg shadow-sm border border-gray-100">
            </div>
        </div>
        
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
                    <span id="total-distance" class="text-xl font-semibold text-gray-900">{{ sprintf('%.2f', (float)$totalDistance) }}</span>
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
                    <p class="text-sm font-medium text-gray-600">Total Duration</p>
                </div>
                <div class="flex items-baseline">
                    <span id="total-duration" class="text-xl font-semibold text-gray-900">{{ $totalDuration }}</span>
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
                    <span id="total-cost" data-cost-display class="text-xl font-semibold text-gray-900 ml-1">{{ sprintf('%.2f', (float)$totalCost) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Add a hidden input to store the rate -->
    <input type="hidden" id="rate-per-km" value="0.60">

    <!-- Add this after the map container -->
    <div id="location-pairs-info" class="mt-6 space-y-6" style="display: none;">
        <div>
            <h2 class="text-base font-medium text-gray-900">Segment Info</h2>
            <p class="text-sm text-gray-500 mt-1">View details for each segment of your journey</p>
        </div>
        <div id="segment-details" class="space-y-4">
            <!-- Segments will be added here dynamically -->
        </div>
    </div>
</div>

</div>

<!-- Action Buttons -->
<div class="px-6 flex justify-between">
    <button type="button" 
            onclick="window.claimForm.previousStep(2)"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
        </svg>
        Previous
    </button>

    <button type="button" 
            id="next-step-button"
            onclick="window.claimForm.nextStep(2)"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
        Next Step
        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
    </button>
</div> 