@extends('layouts.app')

@section('content')
    @auth
        <div class="w-full">
            @php
                $existingClaim = request()->has('claim_id') ? \App\Models\Claim::find(request()->claim_id) : null;
            @endphp

            <h2 class="text-3xl font-bold text-gray-900 mb-6">
                {{ $existingClaim ? 'Edit or Re-Submit Claim' : 'New Claim' }}
            </h2>

            @if($existingClaim)
                <x-claims.existing-claim-details :claim="$existingClaim" class="mb-6" />
            @endif

            <form action="{{ route('claims.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @if($existingClaim)
                    <input type="hidden" name="claim_id" value="{{ $existingClaim->id }}">
                @endif

                <x-forms.error-summary />

                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <!-- Left Side -->
                    <div class="lg:col-span-1 space-y-6 bg-white p-10 md:p-14 rounded-lg shadow-md border border-wgg-border">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <x-forms.date-input name="date_from" label="From" :value="old('date_from')" required />
                            <x-forms.date-input name="date_to" label="To" :value="old('date_to')" required />
                        </div>

                        <x-forms.number-input name="toll_amount" label="Toll Amount" :value="old('toll_amount')" required step="0.01" min="0" />
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="col-span-1 flex flex-col justify-center items-center py-4 w-full border border-dotted border-wgg-border rounded-lg">
                                <input 
                                    class="hidden @error('toll_report') is-invalid @enderror" 
                                    type="file" 
                                    name="toll_report" 
                                    id="toll_report" 
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    aria-label="Toll Report"
                                    onchange="updateFileLabel(this, 'toll_report_label')"
                                >
                                <label for="toll_report" class="text-xs text-wgg-black-400 font-normal cursor-pointer">
                                    <span id="toll_report_label">Toll Report</span>
                                </label>
                                <x-forms.error :name="'toll_report'" />
                                <progress id="toll_report_progress" class="w-full mt-2 hidden" value="0" max="100"></progress>
                            </div>
                        
                            <div class="col-span-1 flex flex-col justify-center items-center py-4 w-full border border-dotted border-wgg-border rounded-lg">
                                <input 
                                    class="hidden @error('email_report') is-invalid @enderror" 
                                    type="file" 
                                    name="email_report" 
                                    id="email_report" 
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    aria-label="Email Approval"
                                    onchange="updateFileLabel(this, 'email_report_label')"
                                >
                                <label for="email_report" class="text-xs text-wgg-black-400 font-normal cursor-pointer">
                                    <span id="email_report_label">Email Approval</span>
                                </label>
                                <x-forms.error :name="'email_report'" />
                                <progress id="email_report_progress" class="w-full mt-2 hidden" value="0" max="100"></progress>
                            </div>
                        </div>

                        <x-forms.file-size-note />

                        <x-forms.textarea name="remarks" label="Remarks" :value="old('remarks', $existingClaim->remarks ?? '')" />

                        <x-forms.select name="claim_company" label="Claim Company" :options="['wge' => 'WGE', 'wgg' => 'WGG', 'wgg & wge' => 'WGG & WGE']" :selected="old('claim_company')" required />

                        <x-forms.location-inputs />

                        <x-forms.add-remove-location-buttons />

                        <x-forms.submit-button :text="$existingClaim ? 'Update Claim' : 'Submit Claim'" class="w-full" />
                    </div>

                    <!-- Right Side -->
                    <div class="lg:col-span-1 xl:col-span-2">
                        <div id="map" class="w-full h-64 sm:h-96 lg:h-full border border-wgg-border rounded-lg shadow-md"></div>
                    </div>
                </div>
            </form>
        </div>

        @vite(['resources/js/form.js'])
        
        <script>
            function updateFileLabel(input, labelId) {
                const label = document.getElementById(labelId);
                if (input.files && input.files[0]) {
                    label.textContent = input.files[0].name;
                } else {
                    label.textContent = input.getAttribute('aria-label');
                }
            }
        </script>
    
    @endauth

    @guest
        <script>window.location.href = "{{ route('login') }}";</script>
    @endguest
@endsection
