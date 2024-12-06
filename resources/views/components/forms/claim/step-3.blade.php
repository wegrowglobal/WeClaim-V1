<div class="space-y-6 p-0 sm:p-6" data-step="3">
    @php
        $draftData = $draftData ?? [];

        // Ensure all required data is available
        $claimCompany = $draftData['claim_company'] ?? '';
        $dateFrom = $draftData['date_from'] ?? '';
        $dateTo = $draftData['date_to'] ?? '';
        $remarks = $draftData['remarks'] ?? '';
        $totalDistance = $draftData['total_distance'] ?? 0;
        $totalCost = $draftData['total_cost'] ?? 0;
        $segmentsData = is_string($draftData['segments_data'] ?? '[]')
            ? json_decode($draftData['segments_data'], true)
            : $draftData['segments_data'] ?? [];
    @endphp

    <!-- Add hidden inputs to preserve all data -->
    <input id="draftData" name="draft_data" type="hidden"
        value="{{ json_encode([
            'claim_company' => $claimCompany,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'remarks' => $remarks,
            'total_distance' => $totalDistance,
            'total_cost' => $totalCost,
            'segments_data' => $segmentsData,
            'locations' => $draftData['locations'] ?? [],
        ]) }}">

    <!-- Debug information (optional) -->
    <div class="hidden">
        <pre>{{ print_r($draftData, true) }}</pre>
    </div>

    <div>
        <h2 class="text-lg font-medium text-gray-900">Supporting Documents</h2>
        <p class="mt-1 text-sm text-gray-500">Upload your toll receipts and approval emails</p>
    </div>

    <!-- Documents & Toll Section -->
    <div class="space-y-6">
        <!-- Trip Summary -->
        <div class="rounded-lg bg-gray-50 p-4 sm:p-6">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Trip Summary</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <!-- Total Distance -->
                <div class="flex flex-col items-center rounded-lg bg-white p-4 shadow-sm">
                    <p class="mb-1 text-sm text-gray-500">Total Distance</p>
                    <div class="flex items-baseline">
                        <span class="text-2xl font-semibold text-indigo-600"
                            data-summary="distance">{{ number_format($totalDistance, 2) }}</span>
                        <span class="ml-1 text-sm text-gray-500">km</span>
                    </div>
                </div>

                <!-- Petrol Claim -->
                <div class="flex flex-col items-center rounded-lg bg-white p-4 shadow-sm">
                    <p class="mb-1 text-sm text-gray-500">Petrol Claim</p>
                    <div class="flex items-baseline">
                        <span class="text-sm text-gray-500">RM</span>
                        <span class="ml-1 text-2xl font-semibold text-emerald-600"
                            data-summary="petrol">{{ $totalCost }}</span>
                    </div>
                </div>

                <!-- Total Locations -->
                <div class="flex flex-col items-center rounded-lg bg-white p-4 shadow-sm">
                    <p class="mb-1 text-sm text-gray-500">Total Locations</p>
                    <div class="flex items-baseline">
                        <span class="text-2xl font-semibold text-blue-600"
                            data-summary="locations">{{ count($draftData['locations'] ?? []) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toll Amount -->
        <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50 sm:p-6">
            <div class="mb-4 flex items-center space-x-3">
                <div class="rounded-lg bg-indigo-50 p-2">
                    <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-600">Toll Expenses</p>
            </div>

            <div class="relative mb-4">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="text-gray-500 sm:text-sm">RM</span>
                </div>
                <input
                    class="form-input block w-full rounded-lg border border-gray-200 bg-white/50 pl-12 transition-all focus:border-indigo-400 focus:bg-white sm:text-sm"
                    id="toll_amount" name="toll_amount" type="number" step="0.01" min="0" placeholder="0.00"
                    required>
            </div>

            <div class="flex items-start gap-2 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-blue-400" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm">
                    All toll amounts will be verified against the attached toll receipts.
                </p>
            </div>
        </div>

        <!-- Document Upload Grid -->
        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
            <!-- Toll Receipt Upload -->
            <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50 sm:p-6">
                <div class="mb-4 flex items-center space-x-3">
                    <div class="rounded-lg bg-indigo-50 p-2">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Toll Receipt</p>
                        <p class="mt-0.5 text-xs text-gray-500">PDF or image files accepted</p>
                    </div>
                </div>

                <div class="document-upload-area" id="toll-upload-area">
                    <input class="hidden" id="toll_report" name="toll_report" type="file"
                        accept=".pdf,.jpg,.jpeg,.png" required>
                    <label
                        class="document-upload-label block cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-4 transition-colors hover:border-indigo-400 sm:p-6"
                        for="toll_report">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <div class="text-sm">
                                <span class="font-medium text-indigo-600">Click to upload</span>
                                <span class="hidden text-gray-500 sm:inline"> or drag and drop</span>
                            </div>
                        </div>
                    </label>
                    <div class="mt-3 hidden" id="toll-preview">
                        <div class="flex items-center gap-2 rounded-lg bg-white p-2 text-sm text-gray-600">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="truncate" id="toll-filename">No file selected</span>
                            <button class="ml-auto text-red-600 hover:text-red-700" type="button"
                                onclick="removeFile('toll')">Remove</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Approval Upload -->
            <div class="rounded-xl bg-gray-50/50 p-4 transition-all hover:bg-gray-50 sm:p-6">
                <div class="mb-4 flex items-center space-x-3">
                    <div class="rounded-lg bg-indigo-50 p-2">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Email Approval</p>
                        <p class="mt-0.5 text-xs text-gray-500">PDF files only</p>
                    </div>
                </div>

                <div class="document-upload-area" id="email-upload-area">
                    <input class="hidden" id="email_report" name="email_report" type="file" accept=".pdf"
                        required>
                    <label
                        class="document-upload-label block cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-4 transition-colors hover:border-indigo-400 sm:p-6"
                        for="email_report">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <div class="text-sm">
                                <span class="font-medium text-indigo-600">Click to upload</span>
                                <span class="hidden text-gray-500 sm:inline"> or drag and drop</span>
                            </div>
                        </div>
                    </label>
                    <div class="mt-3 hidden" id="email-preview">
                        <div class="flex items-center gap-2 rounded-lg bg-white p-2 text-sm text-gray-600">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="truncate" id="email-filename">No file selected</span>
                            <button class="ml-auto text-red-600 hover:text-red-700" type="button"
                                onclick="removeFile('email')">Remove</button>
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

<!-- Action Buttons -->
<div class="flex flex-col justify-between space-y-3 px-4 sm:flex-row sm:space-x-3 sm:space-y-0 sm:px-6">
    <button
        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 sm:w-auto"
        type="button" onclick="window.claimForm.previousStep(3)">
        <svg class="mr-2 hidden h-4 w-4 sm:inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
        </svg>
        Previous
    </button>
    <button
        class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
        type="submit">
        <svg class="mr-2 hidden h-4 w-4 sm:inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Submit
    </button>
</div>

@push('scripts')
    @vite(['resources/js/claim-document.js'])
@endpush
