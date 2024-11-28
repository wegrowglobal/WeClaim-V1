@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Main Container -->
    <div class="min-h-screen">

        <!-- Content Area -->
        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-0 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="animate-slide-in">
                    <div class="mb-6 flex flex-col sm:mb-10 sm:flex-row sm:items-center sm:justify-between">
                        <div class="mb-4 flex items-center justify-between sm:mb-0">
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">
                                    {{ isset($resubmitClaim) ? 'Resubmit Claim' : 'Submit New Claim' }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-600 sm:text-base">
                                    {{ isset($resubmitClaim) ? 'Update and resubmit your rejected claim' : 'Create a new petrol claim request' }}
                                </p>
                            </div>
                            <button
                                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white p-2 text-red-600 shadow-sm transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:hidden"
                                type="button" onclick="window.claimForm.resetForm()">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button
                                class="hidden w-full items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:inline-flex sm:w-auto"
                                type="button" onclick="window.claimForm.resetForm()">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset Form
                            </button>
                            <a class="hidden w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 sm:inline-flex sm:w-auto"
                                href="{{ route('claims.dashboard') }}">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                </svg>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                @if (isset($resubmitClaim))
                    <script>
                        try {
                            window.resubmitClaimData = {!! json_encode([
                                'claim' => $resubmitClaim,
                                'locations' => $resubmitClaim->locations,
                                'documents' => $resubmitClaim->documents,
                            ]) !!};
                        } catch (e) {
                            console.error('Error initializing claim data:', e);
                        }
                    </script>
                @endif

                @if (isset($currentStep))
                    <!-- Progress Steps Container -->
                    <div class="animate-slide-in mb-6 delay-100 sm:mb-10" id="steps-container">
                        <x-forms.progress-steps :currentStep="$currentStep" />
                    </div>

                    <!-- Form Wrapper -->
                    <form class="space-y-6 sm:space-y-8" id="claimForm">
                        @csrf
                        <!-- Form Container -->
                        <div class="animate-slide-in transition-opacity delay-200 duration-300" id="form-container">
                            <!-- Step Content -->
                            @include("components.forms.claim.step-{$currentStep}")
                        </div>
                    </form>
                @else
                    <div class="animate-slide-in py-8 text-center sm:py-12">
                        <div class="text-red-500">
                            Error: Current step not defined. Please try refreshing the page.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Add your application scripts --}}
    @vite(['resources/js/claim-form.js', 'resources/js/maps/claim-map.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.claimForm === 'undefined') {
                console.error('ClaimForm not initialized');
            }
        });
    </script>
@endpush
