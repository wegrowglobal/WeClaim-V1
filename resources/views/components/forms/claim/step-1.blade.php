<div class="space-y-6 p-0 sm:p-6">
    <div>
        <h2 class="text-lg font-medium text-gray-900 sm:text-xl">Basic Information</h2>
        <p class="mt-1 text-sm text-gray-500">Fill in the basic claim details</p>
    </div>

    <!-- Info box for guide on how to select a company -->
    <div class="relative mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-sm sm:p-6">
        <!-- Close button -->
        <div class="absolute right-4 top-2">
            <button class="text-xl font-bold text-gray-500 hover:text-gray-700" type="button"
                onclick="this.parentElement.parentElement.remove()">×</button>
        </div>

        <div
            class="absolute -left-3 -top-3 rounded-lg bg-blue-600 px-4 py-2 text-xs font-medium uppercase tracking-wide text-white">
            Guide
        </div>
        <p class="mb-2 mt-2 text-base font-medium text-blue-700 sm:text-lg">How to choose the correct company?</p>
        <div class="space-y-2">
            <div class="flex items-start">
                <div class="mt-1 flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 12h.01M12 12a1 1 0 0 1 0-2 1 1 0 0 1 0 2z" />
                    </svg>
                </div>
                <span class="ml-2 text-xs text-blue-600 sm:text-sm"><strong>WGG - Wegrow Global</strong> <svg
                        class="ml-1 inline-block h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg> For Zoo Teruntum, Zoo Melaka & Silverlake Outlet</span>
            </div>
            <div class="flex items-start">
                <div class="mt-1 flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 12h.01M12 12a1 1 0 0 1 0-2 1 1 0 0 1 0 2z" />
                    </svg>
                </div>
                <span class="ml-2 text-xs text-blue-600 sm:text-sm"><strong>WGE - Wegrow Edutainment (M)</strong> <svg
                        class="ml-1 inline-block h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg> For Malaysia Heritage Studios, PSKT</span>
            </div>
            <div class="flex items-start">
                <div class="mt-1 flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 12h.01M12 12a1 1 0 0 1 0-2 1 1 0 0 1 0 2z" />
                    </svg>
                </div>
                <span class="ml-2 text-xs text-blue-600 sm:text-sm"><strong>Both (WGG & WGE)</strong> <svg
                        class="ml-1 inline-block h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg> If your travel involves locations from both companies</span>
            </div>
        </div>
    </div>

    <!-- Date Range -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-6">

        <!-- Company Selection -->
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700" for="claim_company">Company</label>
            <select
                class="form-input block h-10 w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white sm:h-[38px]"
                id="claim_company" name="claim_company" required>
                <option value="">Select Company</option>
                <option value="WGG"
                    {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGG' ? 'selected' : '' }}>Wegrow
                    Global Sdn. Bhd.</option>
                <option value="WGE"
                    {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGE' ? 'selected' : '' }}>Wegrow
                    Edutainment (M) Sdn. Bhd.</option>
                <option value="WGG & WGE"
                    {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGG & WGE' ? 'selected' : '' }}>Both
                </option>
            </select>
        </div>

        <!-- Date From -->
        <div class="space-y-2 sm:col-span-1">
            <label class="block text-sm font-medium text-gray-700" for="date_from">Date From</label>
            <input
                class="form-input block h-10 w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white sm:h-[38px]"
                id="date_from" name="date_from" type="date"
                value="{{ old('date_from', $draftData['date_from'] ?? '') }}" required>
        </div>

        <!-- Date To -->
        <div class="space-y-2 sm:col-span-1">
            <label class="block text-sm font-medium text-gray-700" for="date_to">Date To</label>
            <input
                class="form-input block h-10 w-full rounded-lg border border-gray-200 bg-gray-50/50 text-sm transition-all focus:border-gray-400 focus:bg-white sm:h-[38px]"
                id="date_to" name="date_to" type="date" value="{{ old('date_to', $draftData['date_to'] ?? '') }}"
                required>
        </div>
    </div>

    <!-- Remarks -->
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700" for="remarks">Remarks</label>
        <textarea
            class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 p-3 text-sm transition-all focus:border-gray-400 focus:bg-white sm:p-4"
            id="remarks" name="remarks" rows="3" placeholder="Enter any additional notes or remarks">{{ old('remarks', $draftData['remarks'] ?? '') }}</textarea>
    </div>

    <!-- Hidden Draft Data Input -->
    <input id="draftData" name="draft_data" type="hidden"
        value="{{ json_encode([
            'claim_company' => old('claim_company', $draftData['claim_company'] ?? ''),
            'date_from' => old('date_from', $draftData['date_from'] ?? ''),
            'date_to' => old('date_to', $draftData['date_to'] ?? ''),
            'remarks' => old('remarks', $draftData['remarks'] ?? ''),
            'total_distance' => old('total_distance', $draftData['total_distance'] ?? '0'),
            'total_cost' => old('total_cost', $draftData['total_cost'] ?? '0'),
            'segments_data' => old('segments_data', $draftData['segments_data'] ?? '[]'),
            'locations' => old('locations', $draftData['locations'] ?? '[]'),
        ]) }}">

    <!-- Navigation Buttons -->
    <div class="flex justify-end">
        <button
            class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
            id="next-step-button" type="button" onclick="window.claimForm.nextStep(1)">
            Next Step
            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </button>
    </div>
</div>
