@props(['locations'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-100">
    <div class="px-4 sm:px-6 py-5">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Trip Details</h3>
                <p class="text-sm text-gray-500 mt-1">Location information and distances</p>
            </div>
        </div>

        <div class="space-y-4">
            @foreach($locations as $index => $location)
                @if($location->from_location && $location->to_location)
                    <div class="segment-detail bg-white rounded-lg shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md border-2 border-indigo-200">
                        <div class="flex flex-col p-4">
                            <div class="space-y-3 w-full mb-4">
                                <div class="flex items-center space-x-3">
                                    <span class="from-location-dot inline-flex items-center justify-center w-2 h-2 rounded-full"></span>
                                    <span class="text-xs sm:text-sm text-gray-700 break-words">{{ $location->from_location }}</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="to-location-dot inline-flex items-center justify-center w-2 h-2 rounded-full"></span>
                                    <span class="text-xs sm:text-sm text-gray-700 break-words">{{ $location->to_location }}</span>
                                </div>
                            </div>
                            <!-- Route information -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 w-full">
                                <div class="flex items-center space-x-2">
                                    <div class="p-2 bg-blue-50 rounded-lg">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Distance</p>
                                        <p class="text-xs font-medium text-gray-900" data-distance>{{ $location->distance }} km</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="p-2 bg-green-50 rounded-lg">
                                        <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Duration</p>
                                        <p class="text-xs font-medium text-gray-900" data-duration>{{ $location->duration }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 col-span-2 sm:col-span-1">
                                    <div class="p-2 bg-purple-50 rounded-lg">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Cost</p>
                                        <p class="text-xs font-medium text-gray-900" data-cost>RM {{ number_format($location->distance * 0.60, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
