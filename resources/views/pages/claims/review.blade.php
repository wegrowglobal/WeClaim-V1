@extends('layouts.app')

@php
    use App\Models\Claim;
@endphp

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 animate-slide-in">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Review Claim</h1>
                <p class="mt-1 text-sm text-gray-500">Review and process claim request</p>
            </div>
            <x-claims.status-badge :status="$claim->status" class="!text-sm" />
        </div>
    </div>

    <div class="space-y-6">
        <x-claims.claim-details :claim="$claim" />
        <x-claims.toll-details :claim="$claim" />

        <div class="space-y-4 animate-slide-in delay-300">
            @include('components.claims.locations-table', ['locations' => $claim->locations])
        </div>

        <!-- Map Container -->
        <div class="relative">
            <div id="map" class="h-[400px] w-full rounded-lg shadow-sm border border-gray-100"></div>
        </div>

        <!-- Review Action -->
        <x-claims.review-action :claim="$claim" />
    </div>
</div>

@php
    $locationData = $claim->locations
        ->sortBy('order')
        ->map(fn($location) => [
            'from_location' => $location->from_location,
            'to_location' => $location->to_location,
            'order' => $location->order,
            'distance' => $location->distance,
            'from_latitude' => $location->from_latitude,
            'from_longitude' => $location->from_longitude,
            'to_latitude' => $location->to_latitude,
            'to_longitude' => $location->to_longitude,
        ])
        ->values()
        ->toArray();
@endphp

<script>
    var claimLocations = @json($locationData);
</script>

@vite(['resources/js/maps/review-map.js', 'resources/js/claim-review.js'])

@endsection
