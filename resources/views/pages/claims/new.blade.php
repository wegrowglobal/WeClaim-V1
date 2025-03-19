@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Main Container -->
    <div class="min-h-screen">
        <!-- Content Area -->
        <div class="py-4 sm:py-6 lg:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-4 sm:mb-6 lg:mb-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-lg font-semibold text-gray-900 sm:text-xl lg:text-2xl">
                                {{ isset($resubmitClaim) ? 'Resubmit Claim' : 'Submit New Claim' }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ isset($resubmitClaim) ? 'Update and resubmit your rejected claim' : 'Create a new petrol claim request' }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button onclick="window.claimForm.resetForm()" type="button" 
                                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-2 sm:px-4 text-sm font-medium text-red-600 shadow-sm transition-all hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="hidden sm:inline">Reset Form</span>
                            </button>
                            <a href="{{ route('claims.dashboard') }}" 
                               class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 sm:px-4 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                </svg>
                                <span class="hidden sm:inline">Back to Dashboard</span>
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
                    <!-- Progress Steps -->
                    <div class="mb-6 sm:mb-8">
                        <x-forms.progress-steps :currentStep="$currentStep" />
                    </div>

                    <!-- Form -->
                    <form id="claimForm" class="space-y-6 sm:space-y-8" novalidate>
                        @csrf
                        <div class="transition-opacity duration-300">
                            @include("components.forms.claim.step-{$currentStep}")
                        </div>

                        <!-- Step-specific Navigation -->
                        <div class="flex justify-between mt-6">
                            @if($currentStep > 1)
                                <button type="button" 
                                    onclick="window.claimForm.previousStep({{ $currentStep }})"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                    </svg>
                                    Previous
                                </button>
                            @else
                                <div></div>
                            @endif

                            @if($currentStep < 3)
                                <button type="button" 
                                    onclick="window.claimForm.nextStep({{ $currentStep }})"
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Next Step
                                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </button>
                            @else
                                <button type="submit" 
                                    id="submit-claim-button"
                                    class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    Submit Claim
                                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="rounded-lg bg-white p-6 text-center shadow-sm ring-1 ring-black/5 sm:p-8">
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
    @vite(['resources/js/claim-form.js', 'resources/js/maps/claim-map.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.claimForm === 'undefined') {
                console.error('ClaimForm not initialized');
            }
        });
    </script>
@endpush
