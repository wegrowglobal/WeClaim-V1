<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
    <div class="px-6 py-5" data-step="3">
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

        <script>
            console.log('Step 3 - Initial draft data:', @json($draftData));
            console.log('Step 3 - Parsed accommodations:', @json($accommodations));
        </script>

        <!-- Hidden inputs for data persistence -->
        <input type="hidden" id="accommodations-data" name="accommodations" value="{{ json_encode($accommodations) }}">
        <input type="hidden" id="draftData" name="draft_data" value="{{ json_encode($draftData) }}">

    <!-- Debug information (optional) -->
    <div class="hidden">
        <pre>{{ print_r($draftData, true) }}</pre>
    </div>

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
    <div>
                <h3 class="text-lg font-medium text-gray-900">Final Details</h3>
                <p class="text-xs text-gray-500">If you had any accommodations during your trip, you can add them here with their receipts</p>
            </div>
    </div>

    <div class="space-y-6">

            <!-- Accommodation Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Accommodation Details</p>
                            <p class="text-xs text-gray-500">Add your accommodation expenses</p>
                        </div>
                    </div>
                </div>

                <div class="p-3 sm:p-4">
                    <!-- Accommodations Container -->
                    <div id="accommodations-container" class="space-y-3 sm:space-y-4">
                        @foreach($accommodations as $index => $accommodation)
                            <div class="mb-3 sm:mb-4 accommodation-entry overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm" data-index="{{ $index }}">
                                <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">Accommodation Entry #{{ $index + 1 }}</p>
                                        <button type="button" onclick="removeAccommodation({{ $index }})" class="text-red-600 hover:text-red-700">
                                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                    </div>
                </div>

                                <div class="p-3 sm:p-4">
                                    <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2">
                                        <!-- Location -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700" for="accommodation_location_{{ $index }}">
                                                Location
                                            </label>
                                            <div class="relative">
                                                <input type="text" 
                                                    id="accommodation_location_{{ $index }}"
                                                    name="accommodations[{{ $index }}][location]"
                                                    value="{{ $accommodation['location'] ?? '' }}"
                                                    class="location-autocomplete form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500 pr-10"
                                                    data-accommodation-index="{{ $index }}"
                                                    placeholder="Enter or select location"
                                                    required>
                                                <div class="absolute inset-y-0 right-0 flex items-center">
                                                    <button type="button" class="h-full px-2 text-gray-400 hover:text-gray-600">
                                                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Price -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700" for="accommodation_price_{{ $index }}">
                                                Price (RM)
                                            </label>
                                            <input type="number" 
                                                step="0.01"
                                                id="accommodation_price_{{ $index }}"
                                                name="accommodations[{{ $index }}][price]"
                                                value="{{ $accommodation['price'] ?? '' }}"
                                                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                                                required>
                                        </div>

                                        <!-- Check-in Date -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700" for="accommodation_check_in_{{ $index }}">
                                                Check-in Date
                                            </label>
                                            <input type="date" 
                                                id="accommodation_check_in_{{ $index }}"
                                                name="accommodations[{{ $index }}][check_in]"
                                                value="{{ $accommodation['check_in'] ?? '' }}"
                                                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                                                required>
                                        </div>

                                        <!-- Check-out Date -->
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700" for="accommodation_check_out_{{ $index }}">
                                                Check-out Date
                                            </label>
                                            <input type="date" 
                                                id="accommodation_check_out_{{ $index }}"
                                                name="accommodations[{{ $index }}][check_out]"
                                                value="{{ $accommodation['check_out'] ?? '' }}"
                                                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                                                required>
                                        </div>

                                        <!-- Receipt Upload -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Receipt
                                            </label>
                                            <div class="document-upload-area">
                                                <input type="file" 
                                                    id="accommodation_receipt_{{ $index }}"
                                                    name="accommodations[{{ $index }}][receipt]"
                                                    class="hidden"
                                                    onchange="window.accommodationManager.updateFileName({{ $index }}, this)"
                                                    accept=".pdf,.jpg,.jpeg,.png">
                                                <label for="accommodation_receipt_{{ $index }}"
                                                    class="document-upload-label block cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-3 sm:p-4 transition-colors hover:border-indigo-400"
                                                    ondragover="event.preventDefault(); event.stopPropagation(); this.classList.add('border-indigo-400');"
                                                    ondragleave="event.preventDefault(); event.stopPropagation(); this.classList.remove('border-indigo-400');"
                                                    ondrop="event.preventDefault(); event.stopPropagation(); this.classList.remove('border-indigo-400'); const input = document.getElementById('accommodation_receipt_{{ $index }}'); input.files = event.dataTransfer.files; window.accommodationManager.updateFileName({{ $index }}, input);">
                                                    <div class="space-y-2 text-center">
                                                        <svg class="mx-auto h-7 w-7 sm:h-8 sm:w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                        </svg>
                                                        <div class="flex flex-col items-center text-sm">
                                                            <div>
                                                                <span class="font-medium text-indigo-600">Click to upload</span>
                                                                <span class="text-gray-500"> or drag and drop</span>
                                                            </div>
                                                            <p class="text-xs text-gray-500 mt-1">PDF, JPG, JPEG or PNG</p>
                                                        </div>
                                                    </div>
                                                </label>
                                                <div class="mt-2 text-sm text-gray-500" id="accommodation_receipt_name_{{ $index }}">
                                                    {{ $accommodation['receipt_name'] ?? 'No file selected' }}
                                                </div>
                                            </div>
                    </div>
                </div>
            </div>
                            </div>
                        @endforeach
        </div>

                    <!-- Add Accommodation Button -->
                    <button type="button" 
                        onclick="window.accommodationManager.addAccommodation()"
                        class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-600 transition-all hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                        Add Accommodation (Optional)
                    </button>
                </div>
            </div>

            <!-- Documents & Toll Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm" x-data="{ 
                hasToll: @json(old('has_toll', $draftData['has_toll'] ?? false)),
                init() {
                    this.$watch('hasToll', value => {
                        if (!value) {
                            document.getElementById('toll_amount').value = '';
                            document.getElementById('toll_report').value = '';
                        }
                    });
                }
            }">
                <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Required Documents</p>
                            <p class="text-xs text-gray-500">Upload required approval documents</p>
                        </div>
                    </div>
                </div>

                <div class="p-3 sm:p-4 space-y-4 sm:space-y-6">
                    <!-- Email Approval (Mandatory) -->
                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-blue-600">
                                    <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <p class="text-sm font-medium text-gray-900">Email Approval</p>
                                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">Required</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Upload your email approval (PDF or Image)</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 sm:p-4">
                            <div class="document-upload-area" id="email-upload-area">
                                <input class="hidden" id="email_report" name="email_report" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
                                <label for="email_report"
                                    class="document-upload-label group flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 px-4 py-3 sm:px-6 sm:py-4 text-center transition-all hover:border-indigo-400">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-center">
                                            <svg class="h-7 w-7 sm:h-8 sm:w-8 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <div class="text-sm">
                                            <span class="font-medium text-indigo-600">Click to upload</span>
                                            <span class="text-gray-500"> or drag and drop</span>
                                        </div>
                                        <p class="text-xs text-gray-500">PDF, JPG, JPEG or PNG</p>
                                    </div>
                                </label>
                                <div class="mt-2 hidden" id="email-preview">
                                    <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                                        <div class="flex items-center space-x-2">
                                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-sm text-gray-600 truncate" id="email-filename">No file selected</span>
                                        </div>
                                        <button type="button" onclick="removeFile('email')"
                                            class="ml-4 text-sm font-medium text-red-600 hover:text-red-700">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Toll Expenses (Optional) -->
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
                                        <p class="text-xs text-gray-500">Include if you had any toll expenses</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="has_toll" name="has_toll" value="1" 
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        x-model="hasToll">
                                    <label for="has_toll" class="ml-2 text-sm font-medium text-gray-700">
                                        Include Toll
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 sm:p-4 space-y-4" x-show="hasToll" x-cloak>
                            <!-- Toll Amount -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700" for="toll_amount">
                                    Toll Amount
                                </label>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">RM</span>
                                    </div>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="toll_amount"
                                        name="toll_amount"
                                        class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 pl-12 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                                        x-bind:required="hasToll"
                                        value="{{ old('toll_amount', $draftData['toll_amount'] ?? '') }}">
                                </div>
                            </div>

                            <!-- Toll Receipt -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Toll Receipt
                                </label>
                                <div class="document-upload-area" id="toll-upload-area">
                                    <input class="hidden" id="toll_report" name="toll_report" type="file" accept=".pdf,.jpg,.jpeg,.png" x-bind:required="hasToll">
                                    <label for="toll_report"
                                        class="document-upload-label group flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 px-4 py-3 sm:px-6 sm:py-4 text-center transition-all hover:border-indigo-400">
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-center">
                                                <svg class="h-7 w-7 sm:h-8 sm:w-8 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                </svg>
                                            </div>
                                            <div class="text-sm">
                                                <span class="font-medium text-indigo-600">Click to upload</span>
                                                <span class="text-gray-500"> or drag and drop</span>
                                            </div>
                                            <p class="text-xs text-gray-500">PDF, JPG, JPEG or PNG</p>
                                        </div>
                                    </label>
                                    <div class="mt-2 hidden" id="toll-preview">
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 sm:px-4 sm:py-3">
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="text-sm text-gray-600 truncate" id="toll-filename">No file selected</span>
                                            </div>
                                            <button type="button" onclick="removeFile('toll')"
                                                class="ml-4 text-sm font-medium text-red-600 hover:text-red-700">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this hidden input to preserve segments data -->
    <input id="segments-data" name="segments_data" type="hidden"
        value="{{ old('segments_data', is_array($segmentsData) ? json_encode($segmentsData) : $draftData['segments_data'] ?? '[]') }}">
</div>

@push('scripts')
    @vite(['resources/js/claim-document.js', 'resources/js/claim-accommodation.js'])
@endpush
