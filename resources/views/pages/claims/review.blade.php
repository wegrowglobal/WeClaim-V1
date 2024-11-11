@extends('layouts.app')

@php
use App\Models\Claim;
@endphp

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 animate-slide-in">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Review Claim</h1>
                <p class="mt-1 text-sm text-gray-400">Review and process claim request</p>
            </div>
            <x-status-badge :status="$claim->status" class="!text-sm" />
        </div>
    </div>

    <div class="space-y-6">
        <!-- Basic Details Card -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-100">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Details</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Submitted Date</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $claim->submitted_at->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Staff Name</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $claim->user->first_name }} {{ $claim->user->second_name }}</dd>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="text-sm font-medium text-gray-500">Claim Title</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $claim->title }}</dd>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $claim->description }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Period</dt>
                        <dd class="mt-1 text-sm text-gray-600">
                            {{ $claim->date_from->format('d M Y') }} - {{ $claim->date_to->format('d M Y') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Toll Details Card -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Toll Details</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Toll Amount</dt>
                        <dd class="mt-1 text-sm text-gray-600">RM {{ number_format($claim->toll_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Documents</dt>
                        <dd class="mt-1 space-y-2">
                            @if($claim->documents->first()?->toll_file_name)
                                <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->first()->toll_file_name]) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors gap-2 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Toll Receipt
                                </a>
                            @endif
                            @if($claim->documents->first()?->email_file_name)
                                <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->first()->email_file_name]) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors gap-2 text-sm"">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Email Approval
                                </a>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Trip Details -->
        <div class="space-y-4 animate-slide-in delay-300">
            <h3 class="text-lg font-semibold text-gray-900">Trip Details</h3>
            
            <!-- Locations Table -->
            @include('components.claims.locations-table', ['locations' => $claim->locations])
            
            <!-- Map -->
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 p-4">
                <div id="map" class="h-[400px] w-full rounded-lg"></div>
                <div id="route-info-panel" class="text-sm text-gray-600"></div>
            </div>
        </div>

        <!-- Review Action -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 p-6 animate-slide-in delay-400">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Review Decision</h3>
            
            @if($claim->status === Claim::STATUS_APPROVED_ADMIN && auth()->user()->role->name === 'Admin')
                <form action="{{ route('claims.mail.to.datuk', $claim->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Send to Datuk
                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </form>
            @else
                <form action="{{ route('claims.update', $claim->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <textarea id="remarks" name="remarks" rows="4" 
                                class="p-4 border border-wgg-border mt-1 block w-full rounded-lg shadow-sm focus:border-wgg-border transition-300 sm:text-sm"
                                placeholder="Enter your remarks here...">{{ old('remarks') }}</textarea>
                        @error('remarks')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <button type="submit" name="action" value="approve"
                                class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Approve
                            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        <button type="submit" name="action" value="reject"
                                class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Reject
                            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@php
$locationData = $claim->locations
    ->sortBy('order')
    ->map(function($location) {
        return [
            'from_location' => $location->from_location,
            'to_location' => $location->to_location,
            'order' => $location->order,
            'distance' => $location->distance
        ];
    })
    ->values()
    ->toArray();
@endphp

<script>
var claimLocations = @json($locationData);
console.log('Claim locations:', claimLocations);
</script>

@vite('resources/js/review.js')

@endsection
