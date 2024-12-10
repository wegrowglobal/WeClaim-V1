@props(['locations'])

<div class="animate-slide-in rounded-lg bg-white shadow-sm ring-1 ring-black/5 delay-100">
    <div class="px-4 py-5 sm:px-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Trip Details</h3>
                <p class="mt-1 text-sm text-gray-500">Location information and distances</p>
            </div>
        </div>

        <div class="space-y-4">
            @foreach ($locations as $index => $location)
                @if ($location->from_location && $location->to_location)
                    <div
                        class="segment-detail overflow-hidden rounded-lg border-2 border-indigo-200 bg-white shadow-sm transition-all duration-200 hover:shadow-md">
                        <div class="flex flex-col p-4">
                            <div class="mb-4 w-full space-y-3">
                                <div class="flex items-center space-x-3">
                                    <span
                                        class="from-location-dot inline-flex h-2 w-2 items-center justify-center rounded-full"></span>
                                    <span
                                        class="break-words text-xs text-gray-700 sm:text-sm">{{ $location->from_location }}</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span
                                        class="to-location-dot inline-flex h-2 w-2 items-center justify-center rounded-full"></span>
                                    <span
                                        class="break-words text-xs text-gray-700 sm:text-sm">{{ $location->to_location }}</span>
                                </div>
                            </div>
                            <!-- Route information -->
                            <div class="grid w-full grid-cols-2 gap-4 sm:grid-cols-2">
                                <div class="flex items-center space-x-2">
                                    <div class="rounded-lg bg-blue-50 p-2">
                                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Distance</p>
                                        <p class="text-xs font-medium text-gray-900" data-distance>
                                            {{ $location->distance }} km</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="rounded-lg bg-purple-50 p-2">
                                        <svg class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Cost</p>
                                        <p class="text-xs font-medium text-gray-900" data-cost>RM
                                            {{ number_format($location->distance * config('claims.rate_per_km'), 2) }}
                                        </p>
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
