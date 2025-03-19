@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
    <div class="px-4 sm:px-6 py-4 sm:py-5">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Documents & Approvals</h3>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Required documents and expense receipts</p>
            </div>
        </div>

        <div class="space-y-4">
            <!-- Email Approval Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-blue-600">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm font-medium text-gray-900">Email Approval</p>
                                </div>
                                <p class="text-xs text-gray-500">Approval documentation</p>
                            </div>
                        </div>
                        @if($claim->documents()->exists() && $claim->documents->first() && $claim->documents->first()->email_file_name)
                            <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->first()->email_file_name]) }}" 
                               target="_blank"
                               class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500">
                                <span>View Document</span>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        @else
                            <span class="text-sm text-gray-500">No document uploaded</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Toll Section -->
            @if($claim->toll_amount > 0)
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-gray-600">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <p class="text-sm font-medium text-gray-900">Toll Expenses</p>
                                        <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Optional</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Toll charges and receipt</p>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-900">RM {{ number_format($claim->toll_amount, 2) }}</p>
                        </div>
                    </div>

                    @if($claim->documents()->exists() && $claim->documents->first() && $claim->documents->first()->toll_file_name)
                        <div class="bg-white px-3 py-2 sm:px-4 sm:py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Toll Receipt</span>
                                </div>
                                <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->first()->toll_file_name]) }}" 
                                   target="_blank"
                                   class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500">
                                    <span>View Receipt</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>