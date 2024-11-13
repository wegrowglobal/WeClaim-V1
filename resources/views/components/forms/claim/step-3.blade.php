<div class="p-6 space-y-6" data-step="3">
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
            : ($draftData['segments_data'] ?? []);
    @endphp

    <!-- Add hidden inputs to preserve all data -->
    <input type="hidden" 
           id="draftData" 
           name="draft_data"
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
        <h2 class="text-base font-medium text-gray-900">Supporting Documents</h2>
        <p class="text-sm text-gray-500 mt-1">Upload your toll receipts and approval emails</p>
    </div>

    <!-- Trip Summary -->
    <div class="bg-gray-50/50 rounded-lg p-4 space-y-4">
        <h3 class="text-sm font-medium text-gray-900">Trip Summary</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-500">Total Distance</span>
                <p class="text-base font-semibold text-gray-900" data-summary="distance">
                    {{ number_format($totalDistance, 2) }} km
                </p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Petrol Claim</span>
                <p class="text-base font-semibold text-gray-900" data-summary="petrol">
                    RM {{ $totalCost }}
                </p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Total Locations</span>
                <p class="text-base font-semibold text-gray-900" data-summary="locations">
                    {{ count($draftData['locations'] ?? []) }} stops
                </p>
            </div>
        </div>
    </div>

    <!-- Toll Amount -->
    <div class="form-group">
        <label for="toll_amount" class="block text-sm font-medium text-gray-600/80 mb-1">
            Toll Amount (RM)
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">RM</span>
            </div>
            <input type="number" 
                   id="toll_amount" 
                   name="toll_amount" 
                   step="0.01" 
                   min="0"
                   class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm pl-12"
                   placeholder="0.00"
                   required>
        </div>
        <div class="mt-2 p-3 bg-blue-50 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        All toll amounts will be verified against the attached toll receipts. Please ensure the total amount matches your uploaded documents.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Upload Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Toll Receipt Upload -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium text-gray-900">Toll Receipt</h3>
                <p class="text-xs text-gray-500 mt-1">Upload your toll receipt (PDF or image)</p>
            </div>
            
            <div class="document-upload-area" id="toll-upload-area">
                <input type="file" 
                       id="toll_report" 
                       name="toll_report" 
                       class="hidden"
                       accept=".pdf,.jpg,.jpeg,.png"
                       required>
                <label for="toll_report" 
                       class="document-upload-label block p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-gray-400 transition-colors cursor-pointer min-h-[200px] flex items-center justify-center">
                    <div class="space-y-4 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium text-indigo-600 hover:text-indigo-500">
                                Click to upload
                            </span>
                            or drag and drop
                        </div>
                        <p class="text-xs text-gray-500">
                            PDF, PNG, JPG up to 10MB
                        </p>
                    </div>
                </label>
                <div id="toll-preview" class="hidden mt-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span id="toll-filename">No file selected</span>
                        <button type="button" 
                                onclick="removeFile('toll')"
                                class="text-red-600 hover:text-red-700">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Approval Upload -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium text-gray-900">Email Approval</h3>
                <p class="text-xs text-gray-500 mt-1">Upload your approval email (PDF)</p>
            </div>
            
            <div class="document-upload-area" id="email-upload-area">
                <input type="file" 
                       id="email_report" 
                       name="email_report" 
                       class="hidden"
                       accept=".pdf"
                       required>
                <label for="email_report" 
                       class="document-upload-label block p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-gray-400 transition-colors cursor-pointer min-h-[200px] flex items-center justify-center">
                    <div class="space-y-4 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium text-indigo-600 hover:text-indigo-500">
                                Click to upload
                            </span>
                            or drag and drop
                        </div>
                        <p class="text-xs text-gray-500">
                            PDF files only, up to 10MB
                        </p>
                    </div>
                </label>
                <div id="email-preview" class="hidden mt-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span id="email-filename">No file selected</span>
                        <button type="button" 
                                onclick="removeFile('email')"
                                class="text-red-600 hover:text-red-700">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this hidden input to preserve segments data -->
    <input type="hidden" 
           id="segments-data" 
           name="segments_data" 
           value="{{ old('segments_data', is_array($segmentsData) ? json_encode($segmentsData) : $draftData['segments_data'] ?? '[]') }}">
</div>

<!-- Action Buttons -->
<div class="px-6 flex justify-between">
    <button type="button" 
            onclick="window.claimForm.previousStep(3)"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
        </svg>
        Previous
    </button>
    <button type="button" 
            onclick="window.claimForm.handleSubmit(event)"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Debug Form Data
    </button>
</div> 

@push('scripts')
    @vite(['resources/js/claim-document.js'])
@endpush