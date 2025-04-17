<div data-step="2">
    @php
        $draftData = $draftData ?? [];
        
        // Ensure draft data processing logic is kept
        $locationsData = $draftData['locations'] ?? '[]';
        if (is_array($locationsData)) {
            $locationsData = json_encode($locationsData);
        }
        $decodedLocations = is_string($locationsData) ? json_decode($locationsData, true) : [];
        $filteredLocations = array_filter($decodedLocations ?? []);
        $totalDistance = $draftData['total_distance'] ?? '0';
        $totalDuration = $draftData['total_duration'] ?? '0 min';
        $totalCost = isset($draftData['total_cost']) ? $draftData['total_cost'] : '0.00';
        $accommodations = [];
        if (isset($draftData['accommodations'])) {
            $accommodations = is_string($draftData['accommodations']) 
                ? json_decode($draftData['accommodations'], true) 
                : $draftData['accommodations'];
        }
    @endphp
    
    {{-- Removed script log --}}

    {{-- Use the new step header component --}}
    <x-forms.claim.step-header 
        title="Trip Details" 
        subtitle="Plan your route and calculate travel distance." 
        currentStep="2" 
        totalSteps="3" />

    {{-- Hidden Inputs (Keep as is) --}}
    <input id="draftData" type="hidden" value="{{ json_encode(array_merge($draftData, ['accommodations' => $accommodations, 'locations' => $filteredLocations])) }}">
    <input id="locations" name="locations" type="hidden" value="{{ json_encode($filteredLocations) }}">
    <input id="total-distance-input" name="total_distance" type="hidden" value="{{ $totalDistance }}">
    <input id="total-duration-input" name="total_duration" type="hidden" value="{{ $totalDuration }}">
    <input id="total-cost-input" name="total_cost" type="hidden" value="{{ $totalCost }}">
    <input id="rate-per-km" type="hidden" value="0.60"> {{-- Keep rate --}}
    <input id="segments-data" name="segments_data" type="hidden" value="{{ old('segments_data', $draftData['segments_data'] ?? '[]') }}">

    {{-- Form Sections --}}
    <div class="space-y-10">
        {{-- Location Stops Section --}}
        <div>
            <div class="">
                <h4 class="text-base font-medium leading-6 text-gray-600">Location Stops</h4>
                <p class="mt-1 text-sm text-gray-400">Add your starting and ending locations.</p>
            </div>
            <div class="mt-2 space-y-4">
                {{-- Location Inputs Container --}}
                <div id="location-inputs" class="space-y-4">
                    {{-- 
                        Dynamically Added Location Entry Structure (Target for JS):
                        <div class="location-entry border border-gray-200 rounded-lg bg-white p-4 flex items-center space-x-4" data-index="{index}">
                            <svg class="h-5 w-5 text-gray-400 cursor-move" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                            <div class="flex-shrink-0 h-6 w-6 flex items-center justify-center bg-gray-200 rounded-full text-xs font-semibold text-gray-700">{marker}</div>
                            <div class="flex-grow relative">
                                <label for="location-{index}" class="sr-only">Location {markerLabel}</label>
                                <input type="text" id="location-{index}" name="locations[{index}]" class="location-autocomplete block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 pr-10" placeholder="Enter address or select from map" value="{locationValue}" required>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933...zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                                </div>
                            </div>
                            <button type="button" onclick="window.claimMap.removeLocation({index})" class="flex-shrink-0 p-1 text-gray-400 hover:text-red-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    --}}
                </div>
                {{-- Add Stop Button --}}
                <div>
                    <button id="add-location-btn" type="button"
                            class="mt-4 inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Add Stop
                    </button>
                </div>
            </div>
        </div>

        {{-- Route Map & Stats Section --}}
        <div>
            <div>
                <h4 class="text-base font-medium leading-6 text-gray-600">Route Map & Summary</h4>
                <p class="mt-1 text-sm text-gray-400">View your route and calculate travel distance.</p>
            </div>
            <div class="mt-2 space-y-4">
                {{-- Map Container --}}
                <div class="relative h-[300px] sm:h-[400px] w-full rounded-md border border-gray-300 overflow-hidden bg-gray-100" id="map">
                    {{-- Map placeholder text --}}
                     <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">
                        Map will load here...
                    </div>
                </div>
                {{-- Stats Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Stat Card: Distance --}}
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-center sm:text-left">
                        <p class="text-sm font-medium text-gray-500">Total Distance</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900"><span id="total-distance">{{ sprintf('%.2f', (float) $totalDistance) }}</span> km</p>
                    </div>
                    {{-- Stat Card: Duration --}}
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-center sm:text-left">
                        <p class="text-sm font-medium text-gray-500">Total Duration</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900" id="total-duration">{{ $totalDuration ?: '0 min' }}</p>
                    </div>
                    {{-- Stat Card: Cost --}}
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-center sm:text-left">
                        <p class="text-sm font-medium text-gray-500">Estimated Petrol Cost</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900">RM <span id="total-cost" data-cost-display>{{ sprintf('%.2f', (float) $totalCost) }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Segment Info Section (Hidden by default) --}}
        <div id="location-pairs-info" style="display: none;">
            <div class="">
                <h4 class="text-base font-medium leading-6 text-gray-600">Segment Details</h4>
                <p class="mt-1 text-sm text-gray-400">View your route and calculate travel distance.</p>
            </div>
            <div class="mt-2 space-y-4">
                <div class="text-sm" id="segment-details">
                    {{-- 
                        Dynamically Added Segment Entry Structure (Target for JS):
                        <div class="segment-entry border border-gray-200 rounded-lg bg-white mb-4 overflow-hidden">
                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 h-6 w-6 flex items-center justify-center bg-blue-600 rounded-full text-xs font-semibold text-white">{segmentIndex}</div> 
                                    <h5 class="text-sm font-semibold leading-6 text-gray-900">Route Segment {segmentIndex}</h5>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">RM {segmentCost}</p>
                                    <p class="text-xs text-gray-500">{segmentDuration}</p>
                                </div>
                            </div>
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                                <div class="flex items-start gap-2">
                                    <div class="flex-shrink-0 mt-0.5 h-3 w-3 rounded-full bg-blue-500 ring-1 ring-blue-600/30"></div>
                                    <div>
                                        <p class="font-medium text-gray-600">From</p>
                                        <p class="text-gray-800">{startAddress}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <div class="flex-shrink-0 mt-0.5 h-3 w-3 rounded-full bg-red-500 ring-1 ring-red-600/30"></div>
                                    <div>
                                        <p class="font-medium text-gray-600">To</p>
                                        <p class="text-gray-800">{endAddress}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="flex-shrink-0 h-4 w-4 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" /></svg>
                                    <div>
                                        <p class="font-medium text-gray-600">Distance</p>
                                        <p class="text-gray-800">{segmentDistance}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                     --}}
                    <p class="text-gray-500">Segment distances and durations will appear here once calculated.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    {{-- Ensure claim-map.js and google-maps.js are loaded --}}
    @vite(['resources/js/claims/claim-map.js', 'resources/js/utils/google-maps.js'])
@endpush
