@props(['claim'])

<div class="p-6 space-y-6">
    <!-- Approval Remarks -->
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex items-center space-x-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                    <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div class="flex flex-col justify-center items-start">
                    <h3 class="text-sm font-medium text-gray-900">Approval Remarks</h3>
                    <p class="text-xs text-gray-500">Provide feedback for this approval</p>
                </div>
            </div>
        </div>

        <div class="p-4">
            <textarea
                class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 p-4 text-sm text-gray-900 transition-all focus:border-gray-400 focus:bg-white"
                id="remarks" 
                name="remarks" 
                rows="3" 
                placeholder="Please provide any remarks for this approval"
                required></textarea>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end space-x-3 pt-2">
        <button type="button"
            onclick="window.reviewActions.cancelApproval()"
            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
            Cancel
        </button>
        <button type="button"
            onclick="window.reviewActions.submitApproval({{ $claim->id }})"
            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Approve Claim
        </button>
    </div>
</div>

@push('scripts')
@vite(['resources/js/approval-form.js'])
@endpush 