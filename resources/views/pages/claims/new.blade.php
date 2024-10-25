@extends('layouts.app')

@section('content')
    @auth
        <div class="flex flex-col gap-2">
            @php
                $existingClaim = request()->has('claim_id') ? \App\Models\Claim::find(request()->claim_id) : null;
            @endphp

            <h2 class="text-3xl font-bold text-gray-900 mb-6">
                {{ $existingClaim ? 'Editing or Re-Submit Claim' : 'New Claim' }}
            </h2>

            @if($existingClaim)
                <x-claims.existing-claim-details :claim="$existingClaim" />
            @endif

            <div class="max-w-full">
                <form action="{{ route('claims.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    @if($existingClaim)
                        <input type="hidden" name="claim_id" value="{{ $existingClaim->id }}">
                    @endif

                    <x-forms.error-summary />

                    <div class="grid grid-cols-3 rounded-lg shadow-sm">
                        <!-- Left Side -->
                        <div class="p-14 border border-r-0 border-wgg-border col-span-1 flex flex-col gap-4 rounded-l-lg">
                            <x-forms.date-input name="date_from" label="From" :value="old('date_from')" required />
                            <x-forms.date-input name="date_to" label="To" :value="old('date_to')" required />
                            <x-forms.number-input name="toll_amount" label="Toll Amount" :value="old('toll_amount')" required step="0.01" min="0" />

                            <x-forms.file-upload-grid>
                                <x-forms.file-upload name="toll_report" label="Toll Report" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                                <x-forms.file-upload name="email_report" label="Email Approval" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                            </x-forms.file-upload-grid>

                            <x-forms.file-size-note class="mb-4" />

                            <x-forms.textarea name="remarks" label="Remarks" :value="old('remarks', $existingClaim->remarks ?? '')" />

                            <x-forms.select name="claim_company" label="Claim Company" :options="['wge' => 'WGE', 'wgg' => 'WGG', 'wgg & wge' => 'WGG & WGE']" :selected="old('claim_company')" required />

                            <x-forms.location-inputs />

                            <x-forms.add-remove-location-buttons />

                            <x-forms.submit-button :text="$existingClaim ? 'Update Claim' : 'Submit Claim'" />
                        </div>

                        <!-- Right Side -->
                        <div class="border border-wgg-border col-span-2 rounded-r-lg">
                            <div id="map" class="w-full h-full"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @vite(['resources/js/form.js'])
    @endauth

    @guest
        <script>window.location.href = "{{ route('login') }}";</script>
    @endguest
@endsection