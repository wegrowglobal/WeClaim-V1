<div class="p-6 space-y-6">
    <div>
        <h2 class="text-base font-medium text-gray-900">Basic Information</h2>
        <p class="text-sm text-gray-500 mt-1">Fill in the basic claim details</p>
    </div>
    
    <!-- Info box for guide on how to select a company -->
    <div class="bg-blue-50 p-6 rounded-lg shadow-sm border border-blue-200 relative mb-6">
        <!-- Close button -->
         <div class="absolute top-2 right-4">
            <button type="button" class="text-gray-500 hover:text-gray-700 text-xl font-bold" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
        
        <div class="absolute -top-3 -left-3 bg-blue-600 text-white py-2 px-4 rounded-lg text-xs font-medium uppercase tracking-wide">
            Company Selection Guide
        </div>
        <p class="text-lg font-medium text-blue-700 mb-2 mt-2">How to choose the correct company?</p>
        <div class="space-y-1">
            <div class="flex items-center">
                <div class="flex-shrink-0 mt-1">
                    <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12h.01M12 12a1 1 0 0 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                </div>
                <span class="text-sm text-blue-600"><strong>WGG - Wegrow Global</strong> <svg class="inline-block w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> For travel to Zoo Teruntum, Zoo Melaka & Silverlake Outlet</span>
            </div>
            <div class="flex items-center">
                <div class="flex-shrink-0 mt-1">
                    <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12h.01M12 12a1 1 0 0 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                </div>
                <span class="text-sm text-blue-600"><strong>WGE - Wegrow Edutainment (M)</strong> <svg class="inline-block w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> For travel to Malaysia Heritage Studios, PSKT</span>
            </div>
            <div class="flex items-center">
                <div class="flex-shrink-0 mt-1">
                    <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12h.01M12 12a1 1 0 0 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                </div>
                <span class="text-sm text-blue-600"><strong>Both (WGG & WGE)</strong> <svg class="inline-block w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> If your travel involves locations from both companies</span>
            </div>
        </div>
    </div>

    <!-- Date Range -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">

        <!-- Company Selection -->
        <div class="space-y-2">
            <label for="claim_company" class="block text-sm font-medium text-gray-700">Company</label>
            <select id="claim_company" 
                    name="claim_company" 
                    class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px]"
                    required>
                <option value="">Select Company</option>
                <option value="WGG" {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGG' ? 'selected' : '' }}>Wegrow Global Sdn. Bhd.</option>
                <option value="WGE" {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGE' ? 'selected' : '' }}>Wegrow Edutainment (M) Sdn. Bhd.</option>
                <option value="WGG & WGE" {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGG & WGE' ? 'selected' : '' }}>Both</option>
            </select>
        </div>

        <!-- Date From -->
        <div class="space-y-2">
            <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
            <input type="date" 
                   id="date_from" 
                   name="date_from" 
                   class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px]"
                   value="{{ old('date_from', $draftData['date_from'] ?? '') }}"
                   required>
        </div>

        <div class="space-y-2">
            <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
            <input type="date" 
                   id="date_to" 
                   name="date_to" 
                   class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px]"
                   value="{{ old('date_to', $draftData['date_to'] ?? '') }}"
                   required>
        </div>
    </div>

    <!-- Remarks -->
    <div class="space-y-2">
        <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
        <textarea id="remarks" 
                  name="remarks" 
                  rows="3" 
                  class="form-input p-4 block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                  placeholder="Enter any additional notes or remarks">{{ old('remarks', $draftData['remarks'] ?? '') }}</textarea>
    </div>

    <!-- Hidden Draft Data Input -->
    <input type="hidden" 
           id="draftData" 
           name="draft_data"
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
    <div class="flex justify-end space-x-3">
        <button type="button" 
                id="next-step-button"
                onclick="window.claimForm.nextStep(1)"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
            Next Step
            <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </div>
</div> 