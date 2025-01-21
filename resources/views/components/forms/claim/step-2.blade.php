<div class="space-y-6 p-0 sm:p-6" data-step="2">
    @php
        $draftData = $draftData ?? [];
    @endphp
    
    <script>
        console.log('Step 2 - Initial draft data:', @json($draftData));
    </script>

    <div>
        <h2 class="text-base font-medium text-gray-900">Trip Details</h2>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Stop
        </button>
    </div>

    <!-- Map Container -->
    <div class="space-y-4 overflow-hidden rounded-lg">
        <div class="relative">
            <div class="h-[400px] w-full rounded-lg border border-gray-100 shadow-sm" id="map">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <!-- Total Distance -->
            <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                <div class="mb-2 flex items-center space-x-3">
                    <div class="rounded-lg bg-indigo-50 p-2">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600">Total Distance</p>
                </div>
                <div class="flex items-baseline">
                    <span class="text-xl font-semibold text-gray-900"
                        id="total-distance">{{ sprintf('%.2f', (float) $totalDistance) }}</span>
                    <span class="ml-1 text-base text-gray-500">km</span>
                </div>
            </div>

            <!-- Total Duration -->
            <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                <div class="mb-2 flex items-center space-x-3">
                    <div class="rounded-lg bg-indigo-50 p-2">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600">Total Duration</p>
                </div>
                <div class="flex items-baseline">
                    <span class="text-xl font-semibold text-gray-900" id="total-duration">{{ $totalDuration }}</span>
                </div>
            </div>

            <!-- Total Cost -->
            <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50">
                <div class="mb-2 flex items-center space-x-3">
                    <div class="rounded-lg bg-indigo-50 p-2">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600">Total Estimated Cost</p>
                </div>
                <div class="flex items-baseline">
                    <span class="text-xl font-semibold text-gray-900">RM</span>
                    <span class="ml-1 text-xl font-semibold text-gray-900" id="total-cost"
                        data-cost-display>{{ sprintf('%.2f', (float) $totalCost) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Add a hidden input to store the rate -->
    <input id="rate-per-km" type="hidden" value="0.60">

    <!-- Add this after the map container -->
    <div class="mt-6 space-y-6" id="location-pairs-info" style="display: none;">
        <div>
            <h2 class="text-base font-medium text-gray-900">Segment Info</h2>
            <p class="mt-1 text-sm text-gray-500">View details for each segment of your journey</p>
        </div>
        <div class="space-y-4" id="segment-details">
            <!-- Segments will be added here dynamically -->
        </div>
    </div>
</div>

</div>

<!-- Action Buttons -->
<div class="flex justify-between px-6">
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
