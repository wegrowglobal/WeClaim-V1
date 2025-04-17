<div class="bg-white" data-step="3">
    @php
        $draftData = $draftData ?? [];

        // Ensure all required data is available with fallbacks
        $claimCompany = $draftData['claim_company'] ?? '';
        $dateFrom = $draftData['date_from'] ?? '';
        $dateTo = $draftData['date_to'] ?? '';
        $remarks = $draftData['remarks'] ?? '';
        $totalDistance = $draftData['total_distance'] ?? 0;
        $totalCost = $draftData['total_cost'] ?? 0;
        $segmentsData = [];

        // Only try to decode segments_data if it exists and is a string
        if (isset($draftData['segments_data']) && is_string($draftData['segments_data']) && $draftData['segments_data'] !== '[]') {
            try {
                $segmentsData = json_decode($draftData['segments_data'], true) ?? [];
            } catch (\Exception $e) {
                $segmentsData = [];
            }
        }

        // Parse accommodations from draft data and remove receipt_path
        $accommodations = [];
        if (isset($draftData['accommodations'])) {
            $accommodations = is_string($draftData['accommodations']) 
                ? json_decode($draftData['accommodations'], true) 
                : $draftData['accommodations'];
            
            // Remove receipt_path from each accommodation
            $accommodations = array_map(function($acc) {
                unset($acc['receipt_path']);
                return $acc;
            }, $accommodations);
        }
    @endphp

    <!-- Hidden inputs for data persistence -->
    <input type="hidden" id="accommodations-data" name="accommodations" value="{{ json_encode($accommodations) }}">
    <input type="hidden" id="draftData" name="draft_data" value="{{ json_encode($draftData) }}">
    <input id="segments-data" name="segments_data" type="hidden" value="{{ old('segments_data', is_array($segmentsData) ? json_encode($segmentsData) : $draftData['segments_data'] ?? '[]') }}">

    <!-- Debug information (optional) -->
    <div class="hidden">
        <pre>{{ print_r($draftData, true) }}</pre>
    </div>

    {{-- Use the new step header component --}}
    <x-forms.claim.step-header 
        title="Final Details & Uploads" 
        subtitle="Add optional accommodation details and upload required documents." 
        currentStep="3" 
        totalSteps="3" />

    <div class="space-y-6">
        <!-- Accommodation Section -->
        <div class="border border-gray-200 rounded-lg shadow-sm bg-white">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <h4 class="text-base font-semibold leading-6 text-gray-900">Accommodation (Optional)</h4>
            </div>
            <div class="p-4 space-y-4">
                <div id="accommodations-container" class="space-y-4">
                    @foreach($accommodations as $index => $accommodation)
                        <div class="border border-gray-200 rounded-lg bg-white accommodation-entry" data-index="{{ $index }}">
                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-2 flex items-center justify-between">
                                <h5 class="text-sm font-medium text-gray-700">Entry #{{ $index + 1 }}</h5>
                                <button type="button" onclick="window.accommodationManager.removeAccommodation({{ $index }})" class="p-1 text-gray-400 hover:text-red-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4 grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-2">
                                <div>
                                    <label for="accommodation_location_{{ $index }}" class="block text-sm font-medium leading-6 text-gray-900">Location</label>
                                    <div class="mt-1 relative">
                                        <input type="text" 
                                            id="accommodation_location_{{ $index }}"
                                            name="accommodations[{{ $index }}][location]"
                                            value="{{ $accommodation['location'] ?? '' }}"
                                            class="location-autocomplete block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 pr-10"
                                            data-accommodation-index="{{ $index }}"
                                            placeholder="Enter location"
                                            required>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.757.433.571.571 0 00.281.14l.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="accommodation_price_{{ $index }}" class="block text-sm font-medium leading-6 text-gray-900">Price (RM)</label>
                                    <div class="mt-1">
                                        <input type="number" step="0.01" min="0"
                                            id="accommodation_price_{{ $index }}"
                                            name="accommodations[{{ $index }}][price]"
                                            value="{{ $accommodation['price'] ?? '' }}"
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6"
                                            required>
                                    </div>
                                </div>
                                <div>
                                    <label for="accommodation_check_in_{{ $index }}" class="block text-sm font-medium leading-6 text-gray-900">Check-in Date</label>
                                    <div class="mt-1">
                                        <input type="date"
                                            id="accommodation_check_in_{{ $index }}"
                                            name="accommodations[{{ $index }}][check_in]"
                                            value="{{ $accommodation['check_in'] ?? '' }}"
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6"
                                            required>
                                    </div>
                                </div>
                                <div>
                                    <label for="accommodation_check_out_{{ $index }}" class="block text-sm font-medium leading-6 text-gray-900">Check-out Date</label>
                                    <div class="mt-1">
                                        <input type="date"
                                            id="accommodation_check_out_{{ $index }}"
                                            name="accommodations[{{ $index }}][check_out]"
                                            value="{{ $accommodation['check_out'] ?? '' }}"
                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6"
                                            required>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium leading-6 text-gray-900 mb-1">Receipt</label>
                                    <div class="document-upload-area mt-1">
                                        <input type="file"
                                            id="accommodation_receipt_{{ $index }}"
                                            name="accommodations[{{ $index }}][receipt]"
                                            class="hidden"
                                            onchange="window.accommodationManager.updateFileName({{ $index }}, this)"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            required>
                                        <label for="accommodation_receipt_{{ $index }}"
                                            class="relative flex cursor-pointer items-center justify-center rounded-md border-2 border-dashed border-gray-300 bg-white p-3 text-center hover:border-gray-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-black focus-within:ring-offset-2">
                                            <svg class="h-8 w-8 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="ml-3 text-sm font-semibold text-gray-900" id="accommodation_receipt_name_{{ $index }}">
                                                {{ $accommodation['receipt_name'] ?? 'Upload receipt' }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button"
                    onclick="window.accommodationManager.addAccommodation()"
                    class="inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Add Accommodation
                </button>
            </div>
        </div>

        <!-- Documents & Toll Section -->
        <div class="border border-gray-200 rounded-lg shadow-sm bg-white" x-data="{ 
                hasToll: @json(old('has_toll', $draftData['has_toll'] ?? false)),
                init() {
                    this.$watch('hasToll', value => {
                        if (!value) {
                            document.getElementById('toll_amount').value = '';
                            document.getElementById('toll_report').value = '';
                            const tollPreview = document.getElementById('toll-preview');
                            const tollFilename = document.getElementById('toll-filename');
                            if (tollPreview && tollFilename) {
                                tollPreview.classList.add('hidden');
                                tollFilename.textContent = 'No file selected';
                            }
                        }
                    });
                }
            }">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                {{-- Header with Title and Toll Checkbox --}}
                <div class="flex items-center justify-between">
                    <h4 class="text-base font-semibold leading-6 text-gray-900">Documents & Toll</h4>
                    {{-- Moved Toll Checkbox Here --}}
                    <div class="flex items-center">
                        <label for="has_toll" class="mr-2 text-sm font-medium text-gray-900">Toll Expenses (Optional)</label>
                        <input type="checkbox" id="has_toll" name="has_toll" value="1" 
                               class="h-4 w-4 rounded border-gray-300 text-black focus:ring-black"
                               x-model="hasToll">
                    </div>
                </div>
            </div>
            <div class="p-4 space-y-6">
                {{-- Grid for Email and Toll fields --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    {{-- Column 1: Email Approval --}}
                    <div :class="{ 'md:col-span-2': !hasToll, 'md:col-span-1': hasToll }">
                         <x-ui.file-upload 
                            id="email_report" 
                            name="email_report" 
                            label="Email Approval" 
                            description="Upload the required email approval (PDF or Image)."
                            required="true" 
                            updatePreviewJs="window.claimDocument.updatePreview('email_report', this)"
                        />
                    </div>

                    {{-- Column 2: Conditional Toll Fields (Reordered: Receipt first) --}}
                    <div x-show="hasToll" x-cloak>
                        <div> {{-- Removed space-y --}}
                            {{-- Toll Receipt Upload --}}
                            <div class="mb-4"> {{-- Added margin-bottom --}}
                                <x-ui.file-upload 
                                    id="toll_report" 
                                    name="toll_report" 
                                    label="Toll Receipt" 
                                    description="Upload the toll receipt (PDF or Image)."
                                    xRequired="hasToll" 
                                    updatePreviewJs="window.claimDocument.updatePreview('toll_report', this)"
                                />
                            </div>
                            {{-- Toll Amount Input --}}
                            <div>
                                <label for="toll_amount" class="block text-sm font-medium leading-6 text-gray-900">Toll Amount</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">RM</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" id="toll_amount" name="toll_amount"
                                        class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6"
                                        x-bind:required="hasToll"
                                        value="{{ old('toll_amount', $draftData['toll_amount'] ?? '') }}"
                                        placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claim Summary Section -->
        <div class="border border-gray-200 rounded-lg shadow-sm bg-white">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <h4 class="text-base font-semibold leading-6 text-gray-900">Claim Summary</h4>
            </div>
            {{-- Use grid layout for summary items --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 p-4 sm:p-6">
                {{-- Company --}}
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M8.25 21h7.5M12 6.75h.008v.008H12V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Company</p>
                        <p class="text-sm text-gray-900" id="summary-company">{{ $claimCompany ?: 'N/A' }}</p>
                    </div>
                </div>
                {{-- Date Range --}}
                <div class="flex items-start gap-3">
                     <svg class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" /></svg>
                     <div>
                        <p class="text-xs font-medium text-gray-500">Date Range</p>
                        <p class="text-sm text-gray-900" id="summary-dates">{{ $dateFrom ?: 'N/A' }} to {{ $dateTo ?: 'N/A' }}</p>
                    </div>
                </div>
                 {{-- Total Distance --}}
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 10-2.636 6.364M16.5 12V8.25" /></svg>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Total Distance</p>
                        <p class="text-sm text-gray-900" id="summary-distance">{{ sprintf('%.2f', $totalDistance) }} km</p>
                    </div>
                </div>
                {{-- Estimated Petrol Cost --}}
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.75A.75.75 0 013 4.5h.75m-.75 0h.008v.008H3v-.008zm0 0h.008v.008H3v-.008zM3 11.25v.75A.75.75 0 012.25 12h.75m-.75 0h.75A.75.75 0 013 12.75v-.75m0 0h.008v.008H3v-.008zm0 0h.008v.008H3v-.008zM3 18v.75A.75.75 0 012.25 18.75h.75m-.75 0h.75A.75.75 0 013 19.5v-.75m0 0h.008v.008H3v-.008zm0 0h.008v.008H3v-.008zm6.75-12v.75A.75.75 0 019 7.5h-.75m0 0v-.75A.75.75 0 019 6.75h.75m-.75 0h.008v.008H9v-.008zm0 0h.008v.008H9v-.008zM9 14.25v.75A.75.75 0 018.25 15h.75m-.75 0h.75a.75.75 0 01.75.75v-.75m0 0h.008v.008H9v-.008zm0 0h.008v.008H9v-.008zM9 21v-.75A.75.75 0 019.75 20.25h-.75m.75 0h-.75A.75.75 0 019 21v-.75m0 0h.008v.008H9v-.008zm0 0h.008v.008H9v-.008zm6.75-12v.75A.75.75 0 0115 7.5h-.75m0 0v-.75A.75.75 0 0115 6.75h.75m-.75 0h.008v.008H15v-.008zm0 0h.008v.008H15v-.008zM15 14.25v.75A.75.75 0 0114.25 15h.75m-.75 0h.75a.75.75 0 01.75.75v-.75m0 0h.008v.008H15v-.008zm0 0h.008v.008H15v-.008zM15 21v-.75A.75.75 0 0115.75 20.25h-.75m.75 0h-.75A.75.75 0 0115 21v-.75m0 0h.008v.008H15v-.008zm0 0h.008v.008H15v-.008zM4.5 11.25v.75A.75.75 0 013.75 12h.75m0 0h.75A.75.75 0 015.25 12.75v-.75m0 0h-.008v.008H4.5v-.008zm0 0h-.008v.008H4.5v-.008z" /></svg>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Estimated Petrol Cost</p>
                        <p class="text-sm text-gray-900" id="summary-cost">RM {{ sprintf('%.2f', $totalCost) }}</p>
                    </div>
                </div>
                {{-- Total Accommodation Cost --}}
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h18M3 7.5h18M3 12h18m-9 9v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Total Accommodation Cost</p>
                        <p class="text-sm text-gray-900" id="summary-accommodation-cost">RM 0.00</p>
                    </div>
                </div>
                {{-- Total Toll Cost --}}
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.32-1.623-.936l-6.375 3.187c-.54.27-.96.756-.96 1.362V21m-6.375-3.188A2.25 2.25 0 014.5 16.5V8.25c0-.414.336-.75.75-.75h4.5a.75.75 0 01.75.75v8.25a2.25 2.25 0 01-2.25 2.25h-5.375c-.621 0-1.125-.504-1.125-1.125z" /></svg>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Total Toll Cost</p>
                        <p class="text-sm text-gray-900" id="summary-toll-cost">RM 0.00</p>
                    </div>
                </div>
                 {{-- Remarks --}}
                 <div class="flex items-start gap-3 sm:col-span-2">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-2.138a1.125 1.125 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Remarks</p>
                        <p class="text-sm text-gray-900 whitespace-pre-wrap" id="summary-remarks">{{ $remarks ?: 'N/A' }}</p>
                    </div>
                </div>
            </div>
            {{-- Total Amount Section --}}
            <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 sm:px-6">
                 <div class="flex items-center justify-between">
                     <p class="text-base font-semibold text-gray-900">Total Claim Amount</p>
                     <p class="text-lg font-bold text-black" id="summary-total-claim">RM 0.00</p>
                 </div>
             </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/claims/claim-document.js', 'resources/js/claims/claim-accommodation.js'])
@endpush
