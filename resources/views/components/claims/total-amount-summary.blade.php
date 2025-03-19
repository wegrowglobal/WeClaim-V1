@props(['claim'])

<div class="animate-slide-in rounded-lg bg-gradient-to-br from-indigo-50 via-white to-white shadow-sm ring-1 ring-black/5 delay-100">
    <div class="px-4 py-5 sm:px-6">
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900">Claim Summary</h3>
            <p class="mt-1 text-sm text-gray-500">Total expenses breakdown</p>
        </div>

        <div class="relative overflow-hidden rounded-lg border-2 border-indigo-100 bg-white p-4">
            <!-- Expense Items -->
            <div class="grid gap-y-3">
                <!-- Petrol -->
                <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                    <div class="flex items-center space-x-3">
                        <div class="rounded-full bg-blue-50 p-2">
                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Petrol Expenses</span>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">RM {{ number_format($claim->petrol_amount, 2) }}</span>
                </div>

                <!-- Toll -->
                <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                    <div class="flex items-center space-x-3">
                        <div class="rounded-full bg-green-50 p-2">
                            <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M16 8v8m-4-5v5M8 8v8m8 0h1a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2h1" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Toll Charges</span>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">RM {{ number_format($claim->toll_amount, 2) }}</span>
                </div>

                <!-- Accommodation if exists -->
                @if($claim->accommodations->isNotEmpty())
                    <div class="flex items-center justify-between pb-3">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-full bg-purple-50 p-2">
                                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Accommodation</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">RM {{ number_format($claim->getTotalAccommodationAmount(), 2) }}</span>
                    </div>
                @endif
            </div>

            <!-- Total -->
            <div class="mt-4 flex items-center justify-between rounded-lg bg-indigo-50 p-4">
                <div class="flex items-center space-x-2">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-base font-medium text-gray-900">Total Claim Amount</span>
                </div>
                <span class="text-lg font-bold text-indigo-600">RM {{ number_format($claim->getTotalClaimAmount(), 2) }}</span>
            </div>
        </div>
    </div>
</div> 