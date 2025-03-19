@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
    <div class="px-4 sm:px-6 py-4 sm:py-5">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Cost Summary</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Total claim expenses breakdown</p>
            </div>
        </div>

        <div class="space-y-3 sm:space-y-4">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <!-- Petrol Cost -->
                <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-emerald-600">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Petrol Claim</p>
                                <p class="text-xs text-gray-500">Based on {{ number_format($claim->total_distance, 2) }} KM</p>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900">RM {{ number_format($claim->petrol_amount, 2) }}</p>
                    </div>
                </div>

                <!-- Toll Cost -->
                <div class="border-b border-gray-100 bg-white px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-blue-600">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Toll Expenses</p>
                                <p class="text-xs text-gray-500">Total toll charges</p>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900">RM {{ number_format($claim->toll_amount, 2) }}</p>
                    </div>
                </div>

                <!-- Accommodation Cost -->
                <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-indigo-600">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Accommodation</p>
                                <p class="text-xs text-gray-500">Total lodging expenses</p>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900">RM {{ number_format($claim->accommodations->sum('price'), 2) }}</p>
                    </div>
                </div>

                <!-- Total Cost -->
                <div class="bg-gray-900 px-3 py-3 sm:px-4 sm:py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-white">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white">Total Claim Amount</p>
                                <p class="text-xs text-gray-400">All expenses combined</p>
                            </div>
                        </div>
                        <p class="text-base sm:text-lg font-semibold text-white">
                            RM {{ number_format($claim->petrol_amount + $claim->toll_amount + $claim->accommodations->sum('price'), 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 