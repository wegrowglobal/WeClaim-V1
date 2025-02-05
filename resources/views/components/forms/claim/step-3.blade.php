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
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Accommodation Details</p>
                            <p class="text-xs text-gray-500">Add your accommodation expenses</p>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Accommodations Container -->
                    <div id="accommodations-container" class="space-y-4">
                        @foreach($accommodations as $index => $accommodation)
                            <div class="mb-4 accommodation-entry overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm" data-index="{{ $index }}">
                                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">Accommodation Entry #{{ $index + 1 }}</p>
                                        <button type="button" onclick="removeAccommodation({{ $index }})" class="text-red-600 hover:text-red-700">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                    </div>
                </div>

                                <div class="p-4">
                                    <div class="grid gap-4 sm:grid-cols-2">
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
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                                    class="document-upload-label block cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-4 transition-colors hover:border-indigo-400"
                                                    ondragover="event.preventDefault(); event.stopPropagation(); this.classList.add('border-indigo-400');"
                                                    ondragleave="event.preventDefault(); event.stopPropagation(); this.classList.remove('border-indigo-400');"
                                                    ondrop="event.preventDefault(); event.stopPropagation(); this.classList.remove('border-indigo-400'); const input = document.getElementById('accommodation_receipt_{{ $index }}'); input.files = event.dataTransfer.files; window.accommodationManager.updateFileName({{ $index }}, input);">
                                                    <div class="space-y-2 text-center">
                                                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

            <!-- Toll & Documents Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Documents & Toll</p>
                            <p class="text-xs text-gray-500">Upload required documents and enter toll expenses</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 space-y-6">
                    <!-- Toll Amount -->
                    <div class="space-y-4">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="toll_amount">
                                Toll Amount
                            </label>
                            <div class="relative rounded-lg shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="text-gray-500 sm:text-sm">RM</span>
                </div>
                <input
                                    class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 pl-12 text-sm transition-all focus:border-gray-400 focus:bg-white focus:ring-1 focus:ring-indigo-500"
                    id="toll_amount" name="toll_amount" type="number" step="0.01" min="0" placeholder="0.00"
                    required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <div class="text-gray-400 cursor-help group">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="hidden group-hover:block absolute bottom-full right-0 w-64 p-2 mb-2 bg-gray-800 text-white text-xs rounded shadow-lg z-50">
                                            Please ensure your toll amount matches the receipts you'll be uploading for verification
                                        </div>
                                    </div>
                                </div>
            </div>
            </div>
        </div>

        <!-- Document Upload Grid -->
                    <div class="grid grid-cols-2 gap-4">
            <!-- Toll Receipt Upload -->
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600">
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                                        <p class="text-sm font-medium text-gray-900">Toll Receipt</p>
                                        <p class="text-xs text-gray-500">Upload your toll receipt (PDF or image)</p>
                                    </div>
                    </div>
                </div>

                            <div class="p-4">
                <div class="document-upload-area" id="toll-upload-area">
                    <input class="hidden" id="toll_report" name="toll_report" type="file"
                        accept=".pdf,.jpg,.jpeg,.png" required>
                                    <label for="toll_report"
                                        class="document-upload-label group flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-4 text-center transition-all hover:border-indigo-400">
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-center">
                                                <svg class="h-8 w-8 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

            <!-- Email Approval Upload -->
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600">
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                                        <p class="text-sm font-medium text-gray-900">Email Approval</p>
                                        <p class="text-xs text-gray-500">Upload your email approval (PDF or Image)</p>
                                    </div>
                    </div>
                </div>

                            <div class="p-4">
                <div class="document-upload-area" id="email-upload-area">
                                    <input class="hidden" id="email_report" name="email_report" type="file"
                                        accept=".pdf" required>
                                    <label for="email_report"
                                        class="document-upload-label group flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 px-6 py-4 text-center transition-all hover:border-indigo-400">
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-center">
                                                <svg class="h-8 w-8 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                                            <div class="flex items-center space-x-2">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Add this hidden input to preserve segments data -->
    <input id="segments-data" name="segments_data" type="hidden"
        value="{{ old('segments_data', is_array($segmentsData) ? json_encode($segmentsData) : $draftData['segments_data'] ?? '[]') }}">

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-6">
    <button
                class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
        type="button" onclick="window.claimForm.previousStep(3)">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
        </svg>
        Previous
    </button>

    <button
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        type="submit">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Submit
    </button>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/claim-document.js', 'resources/js/claim-accommodation.js'])
@endpush
