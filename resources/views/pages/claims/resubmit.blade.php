@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Main Container -->
    <div class="min-h-screen">
        <!-- Content Area -->
        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 sm:mb-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl">Resubmit Claim</h1>
                            <p class="mt-1 text-sm text-gray-500">Update and resubmit your rejected claim</p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <a href="{{ route('claims.dashboard') }}" 
                               class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                </svg>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Progress Steps -->
                <div class="mb-6">
                    <x-forms.progress-steps :currentStep="$currentStep" />
                </div>

                <!-- Form -->
                <form id="claimForm" action="{{ route('claims.process-resubmission', $claim->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <!-- Hidden input for draft data -->
                    <input type="hidden" id="draftData" name="draft_data" value="{{ json_encode([
                        'claim_company' => $claim->claim_company,
                        'date_from' => $claim->date_from,
                        'date_to' => $claim->date_to,
                        'description' => $claim->description,
                        'locations' => $claim->locations->sortBy('order')->map(fn($loc) => [
                            'location' => $loc->from_location,
                            'order' => $loc->order,
                            'id' => $loc->id
                        ])->values(),
                        'total_distance' => $claim->total_distance,
                        'total_cost' => $claim->total_cost,
                        'total_duration' => $claim->total_duration,
                        'accommodations' => $claim->accommodations,
                        'toll_amount' => $claim->toll_amount,
                        'documents' => $claim->documents,
                        'toll_receipts' => $claim->toll_receipts,
                        'email_approval' => $claim->email_approval
                    ]) }}">
                    
                    <!-- Current Step Content -->
                    <div class="transition-opacity duration-300">
                        @include("components.forms.claim.step-{$currentStep}")
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between pt-6">
                        @if($currentStep > 1)
                            <button type="button" onclick="window.claimForm.previousStep({{ $currentStep }})"
                                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Previous
                            </button>
                        @else
                            <div></div>
                        @endif

                        @if($currentStep < 3)
                            <button type="button" onclick="window.claimForm.nextStep({{ $currentStep }})"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Next
                                <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        @else
                            <button type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Resubmit Claim
                            </button>
                        @endif
                    </div>
                </form>

                <!-- Step-specific Info Boxes -->
                @if($currentStep == 1)
                    <x-forms.info-box
                        title="Basic Information"
                        description="Review and update your claim company and date range information."
                    />
                @elseif($currentStep == 2)
                    <x-forms.info-box
                        title="Trip Details"
                        description="Review and modify your travel locations and route if needed."
                    />
                @elseif($currentStep == 3)
                    <x-forms.info-box
                        title="Final Details"
                        description="Review and update your accommodations, toll receipts, and other documents."
                    />
                @endif
            </div>
        </div>
    </div>

    <!-- Floating Rejection Details Box -->
    @if ($claim->reviews()->where('status', 'rejected')->exists())
        @php
            $latestReview = $claim->reviews()->where('status', 'rejected')->latest()->first();
        @endphp
        <div id="rejection-details" class="fixed bottom-4 left-4 w-80 bg-white rounded-lg shadow-lg border border-red-200 transition-transform duration-300 transform translate-y-0 hover:translate-y-2">
            <div class="border-b border-red-100 bg-red-50 px-4 py-3 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Claim Rejected</p>
                            <p class="text-xs text-gray-500">{{ $latestReview->created_at->format('M j, Y H:i') }}</p>
                        </div>
                    </div>
                    <button onclick="toggleRejectionDetails()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>
            <div id="rejection-details-content" class="p-4 space-y-3">
                <div>
                    <p class="text-xs font-medium text-gray-500">Rejected By</p>
                    <p class="text-sm text-gray-900">{{ $latestReview->reviewer->name }}</p>
                    <p class="text-xs text-gray-500">{{ $latestReview->reviewer->role->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Reason</p>
                    <p class="text-sm text-gray-900">{{ $latestReview->remarks }}</p>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @vite(['resources/js/claim-form.js', 'resources/js/maps/claim-map.js'])

    <script>
        // Toggle rejection details box
        function toggleRejectionDetails() {
            const content = document.getElementById('rejection-details-content');
            const box = document.getElementById('rejection-details');
            const isHidden = content.classList.contains('hidden');
            
            content.classList.toggle('hidden');
            box.classList.toggle('translate-y-[calc(100%-3.5rem)]', !isHidden);
        }
    </script>

    <style>
        #rejection-details {
            z-index: 50;
            transition: transform 0.3s ease-in-out;
        }
        #rejection-details:hover {
            transform: translateY(0) !important;
        }
    </style>
@endpush
