@extends('layouts.app')

@section('content')
    @auth
        <div class="container mx-auto px-4 py-8">
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

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side -->
                    <div class="lg:col-span-1 space-y-6 bg-white p-6 rounded-lg shadow-sm border border-wgg-border">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-forms.date-input name="date_from" label="From" :value="old('date_from')" required />
                            <x-forms.date-input name="date_to" label="To" :value="old('date_to')" required />
                        </div>

                        <x-forms.number-input name="toll_amount" label="Toll Amount" :value="old('toll_amount')" required step="0.01" min="0" />

                        <x-forms.file-upload-grid>
                            <x-forms.file-upload name="toll_report" label="Toll Report" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                            <x-forms.file-upload name="email_report" label="Email Approval" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" />
                        </x-forms.file-upload-grid>

                        <x-forms.file-size-note />

                        <x-forms.textarea name="remarks" label="Remarks" :value="old('remarks', $existingClaim->remarks ?? '')" />

                        <x-forms.select name="claim_company" label="Claim Company" :options="['wge' => 'WGE', 'wgg' => 'WGG', 'wgg & wge' => 'WGG & WGE']" :selected="old('claim_company')" required />

                        <x-forms.location-inputs />

                        <x-forms.add-remove-location-buttons />

                        <x-forms.submit-button :text="$existingClaim ? 'Update Claim' : 'Submit Claim'" class="w-full" />
                    </div>

                    <!-- Right Side -->
                    <div class="lg:col-span-2">
                        <div id="map" class="w-full h-64 sm:h-96 lg:h-full rounded-lg shadow-md"></div>
                    </div>
                </div>
            </form>
        </div>

        @vite(['resources/js/form.js'])
    @endauth

    @guest
        <script>window.location.href = "{{ route('login') }}";</script>
    @endguest
@endsection
