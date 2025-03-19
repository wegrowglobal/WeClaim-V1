@props(['claim'])

<div class="p-6 space-y-6">
    <!-- Rejection Sections -->
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex items-center space-x-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex flex-col justify-center items-start">
                    <h3 class="text-sm font-medium text-gray-900">Required Revisions</h3>
                    <p class="text-xs text-gray-500">Select sections that need correction</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            <!-- Basic Information -->
            <div class="p-4">
                <div class="flex items-start space-x-3">
                    <input type="checkbox" 
                        id="requires_basic_info" 
                        name="rejection_details[requires_basic_info]" 
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <div class="flex flex-col justify-center items-start">
                        <label for="requires_basic_info" class="text-sm font-medium text-gray-900">
                            Basic Information
                        </label>
                        <p class="text-xs text-gray-500">Company, Dates, Description</p>
                    </div>
                </div>
            </div>

            <!-- Trip Details -->
            <div class="p-4">
                <div class="flex items-start space-x-3">
                    <input type="checkbox" 
                        id="requires_trip_details" 
                        name="rejection_details[requires_trip_details]" 
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <div class="flex flex-col justify-center items-start">
                        <label for="requires_trip_details" class="text-sm font-medium text-gray-900">
                            Trip Details
                        </label>
                        <p class="text-xs text-gray-500">Locations, Route, Distance</p>
                    </div>
                </div>
            </div>

            <!-- Accommodation Details -->
            <div class="p-4">
                <div class="flex items-start space-x-3">
                    <input type="checkbox" 
                        id="requires_accommodation_details" 
                        name="rejection_details[requires_accommodation_details]" 
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <div class="flex flex-col justify-center items-start">
                        <label for="requires_accommodation_details" class="text-sm font-medium text-gray-900">
                            Accommodation Details
                        </label>
                        <p class="text-xs text-gray-500">Hotel and lodging information</p>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="p-4">
                <div class="flex items-start space-x-3">
                    <input type="checkbox" 
                        id="requires_documents" 
                        name="rejection_details[requires_documents]" 
                        class="mt-1 h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <div class="flex flex-col justify-center items-start">
                        <label for="requires_documents" class="text-sm font-medium text-gray-900">
                            Documents
                        </label>
                        <p class="text-xs text-gray-500">Toll Receipt, Email Approval</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Remarks -->
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex items-center space-x-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div class="flex flex-col justify-center items-start">
                    <h3 class="text-sm font-medium text-gray-900">Rejection Remarks</h3>
                    <p class="text-xs text-gray-500">Provide detailed feedback</p>
                </div>
            </div>
        </div>

        <div class="p-4">
            <textarea
                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 p-4 text-sm text-gray-900 transition-all focus:border-gray-400 focus:bg-white"
                id="remarks" 
                name="remarks" 
                rows="3" 
                placeholder="Please provide detailed feedback about what needs to be corrected"
                required></textarea>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end space-x-3 pt-2">
        <button type="button"
            onclick="window.reviewActions.cancelRejection()"
            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
            Cancel
        </button>
        <button type="button"
            onclick="window.reviewActions.submitRejection({{ $claim->id }})"
            class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Reject Claim
        </button>
    </div>
</div>

@push('scripts')
@vite(['resources/js/rejection-form.js'])
@endpush 