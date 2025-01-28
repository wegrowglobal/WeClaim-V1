@props(['claim'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
    <div class="px-6 py-5">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Toll & Email Details</h3>
                <p class="text-sm text-gray-500 mt-1">Toll & Email information and related documents</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <!-- Toll Amount -->
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Total Toll Charges</p>
                                <p class="text-xs text-gray-500">Toll expenses for the journey</p>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900">RM {{ number_format($claim->toll_amount, 2) }}</p>
                    </div>
                </div>

                <!-- Documents Section -->
                @if($claim->documents->first()?->toll_file_name || $claim->documents->first()?->email_file_name)
                    <div class="bg-white px-4 py-3">
                        <div class="space-y-3">
                            @if($claim->documents->first()?->toll_file_name)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-600">Toll Receipt</span>
                                    </div>
                                    <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->first()->toll_file_name]) }}" 
                                       target="_blank"
                                       class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500">
                                        <span>View</span>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                            @endif

                            @if($claim->documents->first()?->email_file_name)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-600">Email Approval</span>
                                    </div>
                                    <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->first()->email_file_name]) }}" 
                                       target="_blank"
                                       class="inline-flex items-center space-x-1 text-sm text-indigo-600 hover:text-indigo-500">
                                        <span>View</span>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 px-4 py-3">
                        <div class="flex items-center space-x-2 text-gray-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm">No documents have been uploaded</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>