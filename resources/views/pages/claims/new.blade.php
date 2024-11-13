@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Main Container -->
<div class="min-h-screen">

    <!-- Content Area -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="animate-slide-in">
                <div class="flex items-center justify-between mb-14">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Submit New Claim</h1>
                        <p class="text-gray-600 mt-1">Create a new petrol claim request</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" 
                                onclick="window.claimForm.resetForm()"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-600 bg-white rounded-lg border border-red-200 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset Form
                        </button>
                        <a href="{{ route('claims.dashboard') }}" 
                           class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-200 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            @if(isset($currentStep))
                <!-- Progress Steps Container -->
                <div id="steps-container" class="mb-14 animate-slide-in delay-100">
                    <x-forms.progress-steps :currentStep="$currentStep" />
                </div>

                <!-- Form Wrapper -->
                <form id="claimForm" class="space-y-8">
                    @csrf
                    <!-- Form Container -->
                    <div id="form-container" class="transition-opacity duration-300 animate-slide-in delay-200">
                        <!-- Step Content -->
                        @include("components.forms.claim.step-{$currentStep}")
                    </div>
                </form>
            @else
                <div class="text-center py-12 animate-slide-in">
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
    @vite(['resources/js/claim-form.js', 'resources/js/claim-map.js'])
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.claimForm === 'undefined') {
                console.error('ClaimForm not initialized');
            }
        });
    </script>
@endpush
