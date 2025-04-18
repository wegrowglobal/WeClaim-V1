<div data-step="1">
    @php
        $draftData = $draftData ?? [];
    @endphp
    
    {{-- Use the new step header component --}}
    <x-forms.claim.step-header 
        title="Basic Information" 
        subtitle="Fill in the basic claim details." 
        currentStep="1" 
        totalSteps="3" />

    {{-- Form Fields --}}
    <div class="space-y-6">
        {{-- Combined Company & Date Range --}}
        <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-3">
            {{-- Company Selection --}}
            <div>
                <label for="claim_company" class="block text-sm font-medium leading-6 text-gray-900">Company</label>
                <div class="mt-2">
                    <select id="claim_company" name="claim_company" required
                            class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6">
                        <option value="" disabled {{ old('claim_company', $draftData['claim_company'] ?? '') == '' ? 'selected' : '' }}>Select Company</option>
                        <option value="WGG" {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGG' ? 'selected' : '' }}>
                            Wegrow Global Sdn. Bhd. (WGG)
                        </option>
                        <option value="WGE" {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGE' ? 'selected' : '' }}>
                            Wegrow Edutainment (M) Sdn. Bhd. (WGE)
                        </option>
                        <option value="WGS" {{ old('claim_company', $draftData['claim_company'] ?? '') == 'WGS' ? 'selected' : '' }}>
                            Wegrow Studios Sdn. Bhd. (WGS)
                        </option>
                    </select>
                </div>
                {{-- Add @error directive if needed --}}
            </div>

            {{-- Date From --}}
            <div>
                <label for="date_from" class="block text-sm font-medium leading-6 text-gray-900">Date From</label>
                <div class="mt-2">
                    <input type="date" id="date_from" name="date_from" value="{{ old('date_from', $draftData['date_from'] ?? '') }}" required
                           class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6">
                </div>
                 {{-- Add @error directive if needed --}}
            </div>
            {{-- Date To --}}
            <div>
                <label for="date_to" class="block text-sm font-medium leading-6 text-gray-900">Date To</label>
                <div class="mt-2">
                    <input type="date" id="date_to" name="date_to" value="{{ old('date_to', $draftData['date_to'] ?? '') }}" required
                           class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6">
                </div>
                 {{-- Add @error directive if needed --}}
            </div>
        </div>

        {{-- Remarks --}}
        <div>
            <label for="remarks" class="block text-sm font-medium leading-6 text-gray-900">Remarks</label>
            <div class="mt-2">
                <textarea id="remarks" name="remarks" rows="3" 
                          class="block w-full rounded-md border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6" 
                          placeholder="Enter any additional notes or remarks">{{ old('remarks', $draftData['remarks'] ?? '') }}</textarea>
            </div>
            {{-- Add @error directive if needed --}}
        </div>
    </div>

    {{-- Hidden Draft Data Input (Keep as is) --}}
    @php
        $accommodations = [];
        if (isset($draftData['accommodations'])) {
            $accommodations = is_string($draftData['accommodations']) 
                ? json_decode($draftData['accommodations'], true) 
                : $draftData['accommodations'];
        }
    @endphp
    <input id="draftData" name="draft_data" type="hidden" value="{{ json_encode(array_merge($draftData, ['accommodations' => $accommodations])) }}">
</div>
