<div class="space-y-6 p-0 sm:p-6" data-step="1">
    @php
        $draftData = $draftData ?? [];
    @endphp
    
    <script>
        console.log('Step 1 - Initial draft data:', @json($draftData));
    </script>
    <div>
        <h2 class="text-lg font-medium text-gray-900 sm:text-xl">Basic Information</h2>
        <p class="mt-1 text-sm text-gray-500">Fill in the basic claim details</p>
    </div>

    <!-- Company Selection Guide -->
    <div class="rounded-lg bg-blue-50/50 p-4">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-sm font-medium text-gray-900">Company Selection Guide</h3>
            </div>
            <button class="text-gray-400 hover:text-gray-600" type="button" onclick="this.parentElement.parentElement.remove()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="mt-4 space-y-3">
            <div class="rounded bg-white/70 p-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-blue-600">WGG</span>
                    <span class="text-sm text-gray-900">Wegrow Global</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">For Zoo Teruntum, Zoo Melaka & Silverlake Outlet</p>
            </div>

            <div class="rounded bg-white/70 p-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-blue-600">WGE</span>
                    <span class="text-sm text-gray-900">Wegrow Edutainment</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">For Malaysia Heritage Studios, PSKT</p>
            </div>

            <div class="rounded bg-white/70 p-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-blue-600">WGS</span>
                    <span class="text-sm text-gray-900">Wegrow Studios</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">For Others</p>
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
                <option value="WGS"
                    {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGS' ? 'selected' : '' }}>Wegrow
                    Studios Sdn. Bhd.</option>
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
    @php
        // Ensure accommodations data is preserved
        $accommodations = [];
        if (isset($draftData['accommodations'])) {
            $accommodations = is_string($draftData['accommodations']) 
                ? json_decode($draftData['accommodations'], true) 
                : $draftData['accommodations'];
        }
    @endphp
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
            'accommodations' => $accommodations
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
