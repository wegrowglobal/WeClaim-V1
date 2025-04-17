@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="mx-auto bg-white w-full">

        {{-- Header using page-header component --}}
        <x-layout.page-header 
            :title="isset($resubmitClaim) ? 'Resubmit Claim' : 'Submit New Claim'"
            :subtitle="isset($resubmitClaim) ? 'Update and resubmit your rejected claim' : 'Create a new petrol claim request'">
            <div class="flex flex-col gap-2 sm:flex-row">
                 {{-- Reset Button --}}
                <button onclick="window.claimForm.resetForm()" type="button" 
                        class="inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-700 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M15.323 11.453a.75.75 0 10-1.06-1.06l-2.47 2.47V6.75a.75.75 0 00-1.5 0v6.113l-2.47-2.47a.75.75 0 00-1.06 1.06l3.75 3.75a.75.75 0 001.06 0l3.75-3.75zm-8.146-3.18a.75.75 0 00-1.06 1.06l2.47 2.47v-6.113a.75.75 0 00-1.5 0V10.81l-2.47-2.47a.75.75 0 10-1.06 1.06l3.75 3.75a.75.75 0 001.06 0l3.75-3.75a.75.75 0 00-1.06-1.06l-2.47 2.47V5.75a.75.75 0 00-1.5 0v5.053l-2.47-2.47z" clip-rule="evenodd" />
                      </svg>
                    Reset Form
                </button>
                {{-- Back Button --}}
                <a href="{{ route('claims.dashboard') }}" 
                   class="inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                   <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                  </svg>
                    Back to Dashboard
                </a>
            </div>
        </x-layout.page-header>

        @if (isset($resubmitClaim))
            {{-- Keep resubmitClaimData script --}}
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
            <!-- Form -->
            <form id="claimForm" class="space-y-6 sm:space-y-8" novalidate>
                @csrf
                {{-- Use a container for the step content for potential transitions --}}
                <div id="claim-step-container" class="transition-opacity duration-300">
                    @include("components.forms.claim.step-{$currentStep}")
                </div>

                <!-- Step Navigation Buttons -->
                <div class="flex items-center justify-between">
                    @if($currentStep > 1)
                        <button type="button" 
                            onclick="window.claimForm.previousStep({{ $currentStep }})"
                            class="inline-flex items-center justify-center rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                            </svg>
                            Previous
                        </button>
                    @else
                        <div></div> {{-- Placeholder for alignment --}}
                    @endif

                    @if($currentStep < 3)
                        <button type="button" 
                            onclick="window.claimForm.nextStep({{ $currentStep }})"
                            class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                            Next Step
                            <svg class="-ml-0.5 ml-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @else
                        {{-- Submit Button --}}
                        <button type="submit" 
                            id="submit-claim-button"
                            class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black disabled:opacity-50 disabled:cursor-not-allowed">
                             <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            Submit Claim
                        </button>
                    @endif
                </div>
            </form>
        @else
            {{-- Error message --}}
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>Current step not defined. Please try refreshing the page or starting over.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div> 
@endsection

@push('scripts')
    @vite(['resources/js/claims/claim-form.js'])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.claimForm === 'undefined') {
                console.error('ClaimForm not initialized');
            } else {
                 window.claimForm.loadStepContent({{ $currentStep ?? 1 }}); 
            }
        });
    </script>
@endpush
