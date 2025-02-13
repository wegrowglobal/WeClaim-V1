@props(['claim', 'accommodations'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
    <div class="px-4 sm:px-6 py-4 sm:py-5">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Accommodation Details</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Hotel and lodging information</p>
            </div>
        </div>

        <div class="space-y-3 sm:space-y-4">
            @forelse($accommodations as $accommodation)
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <!-- Location Header -->
                    <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-indigo-600">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $accommodation->location }}</p>
                                    <p class="text-xs text-gray-500">{{ $accommodation->location_address }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">RM {{ number_format($accommodation->price, 2) }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($accommodation->check_in)->format('d M Y') }} - {{ \Carbon\Carbon::parse($accommodation->check_out)->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Receipt Section -->
                    @if($accommodation->receipt_path)
                        <div class="bg-white px-3 py-2 sm:px-4 sm:py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Receipt</span>
                                </div>
                                <a href="{{ Storage::url($accommodation->receipt_path) }}" 
                                   target="_blank"
                                   class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500">
                                    <span>View</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-lg border-2 border-dashed border-gray-200 p-4 text-center">
                    <p class="text-sm text-gray-500">No accommodation details available</p>
                </div>
            @endforelse
        </div>
    </div>
</div> 