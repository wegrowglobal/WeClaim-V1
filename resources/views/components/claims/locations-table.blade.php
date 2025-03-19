@props(['locations', 'routeColors' => [
    '#4285F4', // Google Blue
    '#DB4437', // Google Red
    '#F4B400', // Google Yellow
    '#0F9D58', // Google Green
    '#AB47BC', // Purple
    '#00ACC1', // Cyan
    '#FF7043', // Deep Orange
    '#9E9E9E', // Grey
]])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-100">
    <div class="px-3 py-3 sm:px-6 sm:py-5">
        <div class="flex items-center justify-between mb-3 sm:mb-6">
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Trip Details</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Location information and distances</p>
            </div>
        </div>

        <div class="space-y-3 sm:space-y-4">
            @foreach ($locations as $index => $location)
                @if ($location->from_location && $location->to_location)
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <!-- Location Header -->
                        <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div class="hidden sm:flex h-8 w-8 items-center justify-center rounded-full transition-all" style="background-color: {{ $routeColors[$index % count($routeColors)] }}">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">Route Segment {{ $index + 1 }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $location->from_location }} → {{ $location->to_location }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-end sm:ml-4">
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">RM {{ number_format($location->distance * 0.6, 2) }}</p>
                                        <p class="text-xs text-gray-500">{{ $location->distance }} km</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="p-2 sm:p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
                                <!-- From Location -->
                                <div class="overflow-hidden rounded-lg border border-gray-100 bg-gray-50/50 p-2 sm:p-3">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full" style="background-color: {{ $routeColors[$index % count($routeColors)] }}"></span>
                                        <span class="text-xs font-medium text-gray-700">From</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-900 truncate" title="{{ $location->from_location }}">{{ $location->from_location }}</p>
                                </div>

                                <!-- To Location -->
                                <div class="overflow-hidden rounded-lg border border-gray-100 bg-gray-50/50 p-2 sm:p-3">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full" style="background-color: {{ $routeColors[($index + 1) % count($routeColors)] }}"></span>
                                        <span class="text-xs font-medium text-gray-700">To</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-900 truncate" title="{{ $location->to_location }}">{{ $location->to_location }}</p>
                                </div>

                                <!-- Distance -->
                                <div class="overflow-hidden rounded-lg border border-gray-100 bg-gray-50/50 p-2 sm:p-3">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-gray-400"></span>
                                        <span class="text-xs font-medium text-gray-700">Distance</span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-gray-900">{{ $location->distance }} km</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <div class="h-[250px] sm:h-[400px] w-full rounded-lg border border-gray-100 shadow-sm" id="map"></div>
        </div>
    </div>
</div>
