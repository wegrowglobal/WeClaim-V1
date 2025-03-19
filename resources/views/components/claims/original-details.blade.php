@props(['claim'])

<div class="divide-y divide-gray-100">
    <!-- Basic Information -->
    <div class="p-4 space-y-3">
        <div class="flex items-center space-x-2 text-sm">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-gray-900">Claim #{{ $claim->id }}</span>
        </div>

        <div class="flex items-center space-x-2 text-sm">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="text-gray-900">{{ $claim->claim_company }}</span>
        </div>
        <div class="flex items-center space-x-2 text-sm">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-gray-900">{{ \Carbon\Carbon::parse($claim->date_from)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($claim->date_to)->format('d/m/Y') }}</span>
        </div>
        @if($claim->description)
            <div class="flex space-x-2 text-sm">
                <svg class="h-5 w-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
                <span class="text-gray-900">{{ $claim->description }}</span>
            </div>
        @endif
    </div>

    <!-- Trip Details -->
    <div class="p-4 space-y-3">
        <div class="flex flex-col items-start gap-2 w-fit">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <div class="text-sm">
                    <span class="text-gray-500">Distance:</span>
                    <span class="ml-1 text-gray-900">{{ number_format($claim->total_distance, 2) }} KM</span>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm">
                    <span class="text-gray-500">Petrol:</span>
                    <span class="ml-1 text-gray-900">RM {{ number_format($claim->petrol_amount, 2) }}</span>
                </div>
            </div>
        </div>

        @if($claim->locations && count($claim->locations) > 0)
            <div class="mt-2">
                <div class="text-xs font-medium text-gray-500 mb-2">Route</div>
                <div class="space-y-1">
                    @foreach($claim->locations as $location)
                        <div class="flex items-center space-x-2 text-sm">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span class="text-gray-900">{{ $location->from_location }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Accommodation Details -->
    @if($claim->accommodations && count($claim->accommodations) > 0)
        <div class="p-4">
            <div class="text-xs font-medium text-gray-500 mb-2">Accommodations</div>
            <div class="space-y-3">
                @foreach($claim->accommodations as $accommodation)
                    <div class="flex items-start space-x-3 text-sm">
                        <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <div class="flex-1">
                            <div class="text-gray-900">{{ $accommodation->location }}</div>
                            <div class="text-gray-900">RM {{ number_format($accommodation->price, 2) }}</div>
                            <div class="text-gray-500">{{ \Carbon\Carbon::parse($accommodation->check_in)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($accommodation->check_out)->format('d/m/Y') }}</div>
                            @if($accommodation->receipt_path)
                                <a href="{{ Storage::url($accommodation->receipt_path) }}"
                                    target="_blank" class="mt-1 inline-block text-indigo-600 hover:text-indigo-900">View Receipt</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Documents -->
    <div class="p-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div class="text-sm">
                    <span class="text-gray-500">Toll:</span>
                    <span class="ml-1 text-gray-900">RM {{ number_format($claim->toll_amount, 2) }}</span>
                </div>
            </div>
            @if($claim->documents->first())
                <div class="flex items-center justify-end space-x-3">
                    @if($claim->documents->first()->toll_file_name)
                        <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->first()->toll_file_name]) }}"
                            target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900">View Toll Receipt</a>
                    @endif
                    @if($claim->documents->first()->email_file_name)
                        <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->first()->email_file_name]) }}"
                            target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900">View Approval</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div> 