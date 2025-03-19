@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-100">
    <div class="px-4 sm:px-6 py-4 sm:py-5">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Basic Details</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Claim information and details</p>
            </div>
        </div>

        <div class="space-y-3 sm:space-y-4">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <!-- Submission Info -->
                <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-indigo-600">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $claim->user->first_name }} {{ $claim->user->second_name }}</p>
                                <p class="text-xs text-gray-500">Submitted on {{ $claim->submitted_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Claim Period -->
                <div class="border-b border-gray-100 bg-white px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-emerald-600">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Claim Period</p>
                                <p class="text-xs text-gray-500">{{ $claim->date_from->format('d M Y') }} - {{ $claim->date_to->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Claim Title & Description -->
                <div class="bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-blue-600">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $claim->title }}</p>
                                <p class="text-xs text-gray-500">{{ $claim->description ?: 'No description provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 