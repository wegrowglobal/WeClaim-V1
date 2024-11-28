<div class="space-y-4 p-0 sm:space-y-6 sm:p-6">
    <div>
        <h2 class="text-lg font-medium text-gray-900 sm:text-xl">Trip Details</h2>
        <p class="mt-1 text-sm text-gray-500">Plan your route and calculate travel distance</p>
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
    <input id="draftData" type="hidden" value="{{ is_array($draftData) ? json_encode($draftData) : '{}' }}">
    <input id="locations" name="locations" type="hidden" value="{{ json_encode($filteredLocations) }}">
    <input id="total-distance-input" name="total_distance" type="hidden" value="{{ $totalDistance }}">
    <input id="total-duration-input" name="total_duration" type="hidden" value="{{ $totalDuration }}">
    <input id="total-cost-input" name="total_cost" type="hidden" value="{{ $totalCost }}">
    <input id="segments-data" name="segments_data" type="hidden"
        value="{{ old('segments_data', $draftData['segments_data'] ?? '[]') }}">

    <!-- Location Inputs -->
    <div class="space-y-3 sm:space-y-4" id="location-inputs">
        <!-- Locations will be added dynamically via JavaScript -->
    </div>

    <!-- Location Controls -->
    <div class="flex justify-start sm:justify-start">
        <button
            class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-600 transition-all hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            id="add-location-btn" type="button">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Stop
        </button>
    </div>

    <!-- Map Container -->
    <div class="space-y-4 overflow-hidden rounded-lg">
        <div class="relative">
            <div class="h-[300px] w-full rounded-lg border border-gray-100 shadow-sm sm:h-[400px]" id="map">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-6">
            <!-- Total Distance -->
            <div class="flex items-center justify-between rounded-xl bg-gray-50/50 p-3 transition-all hover:bg-gray-50 sm:p-4">
                <div class="flex items-center space-x-3">
                    <div class="rounded-lg bg-indigo-50 p-1.5 sm:p-2">
                        <svg class="h-4 w-4 text-indigo-600 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="sm:hidden">
                        <p class="text-xs font-medium text-gray-600">Distance</p>
                        <span class="text-sm font-semibold text-gray-900" id="total-distance">{{ sprintf('%.2f', (float) $totalDistance) }} km</span>
                    </div>
                </div>
                <div class="hidden sm:block">
                    <p class="text-sm font-medium text-gray-600">Total Distance</p>
                    <span class="text-lg font-semibold text-gray-900" id="total-distance">{{ sprintf('%.2f', (float) $totalDistance) }} km</span>
                </div>
            </div>

            <!-- Total Duration -->
            <div class="flex items-center justify-between rounded-xl bg-gray-50/50 p-3 transition-all hover:bg-gray-50 sm:p-4">
                <div class="flex items-center space-x-3">
                    <div class="rounded-lg bg-indigo-50 p-1.5 sm:p-2">
                        <svg class="h-4 w-4 text-indigo-600 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="sm:hidden">
                        <p class="text-xs font-medium text-gray-600">Duration</p>
                        <span class="text-sm font-semibold text-gray-900" id="total-duration">{{ $totalDuration }}</span>
                    </div>
                </div>
                <div class="hidden sm:block">
                    <p class="text-sm font-medium text-gray-600">Total Duration</p>
                    <span class="text-lg font-semibold text-gray-900" id="total-duration">{{ $totalDuration }}</span>
                </div>
            </div>

            <!-- Total Cost -->
            <div class="flex items-center justify-between rounded-xl bg-gray-50/50 p-3 transition-all hover:bg-gray-50 sm:p-4">
                <div class="flex items-center space-x-3">
                    <div class="rounded-lg bg-indigo-50 p-1.5 sm:p-2">
                        <svg class="h-4 w-4 text-indigo-600 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="sm:hidden">
                        <p class="text-xs font-medium text-gray-600">Cost</p>
                        <span class="text-sm font-semibold text-gray-900" id="total-cost" data-cost-display>RM {{ sprintf('%.2f', (float) $totalCost) }}</span>
                    </div>
                </div>
                <div class="hidden sm:block">
                    <p class="text-sm font-medium text-gray-600">Total Estimated Cost</p>
                    <span class="text-lg font-semibold text-gray-900" id="total-cost" data-cost-display>RM {{ sprintf('%.2f', (float) $totalCost) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Add a hidden input to store the rate -->
    <input id="rate-per-km" type="hidden" value="0.60">

    <!-- Add this after the map container -->
    <div class="mt-4 space-y-4 sm:mt-6 sm:space-y-6" id="location-pairs-info" style="display: none;">
        <div>
            <h2 class="text-base font-medium text-gray-900 sm:text-lg">Segment Info</h2>
            <p class="mt-1 text-xs text-gray-500 sm:text-sm">View details for each segment of your journey</p>
        </div>
        <div class="space-y-3 sm:space-y-4" id="segment-details">
            <!-- Segments will be added here dynamically -->
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="flex justify-between px-4 py-4 sm:px-6 sm:py-6">
    <button
        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 sm:px-4 sm:text-sm"
        type="button" onclick="window.claimForm.previousStep(2)">
        <svg class="mr-1 h-3 w-3 sm:mr-2 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
        </svg>
        Previous
    </button>

    <button
        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:px-4 sm:text-sm"
        id="next-step-button" type="button" onclick="window.claimForm.nextStep(2)">
        Next Step
        <svg class="ml-1 h-3 w-3 sm:ml-2 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
    </button>
</div>
