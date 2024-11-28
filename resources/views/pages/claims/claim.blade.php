@extends('layouts.app')

@section('content')
<div class="w-full max-w-7xl mx-auto px-0 sm:px-6 lg:px-8">
    <x-claims.claim-header 
        title="Claim Details"
        subtitle="View and manage claim information"
        :status="$claim->status"
    />

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

        <!-- Action Buttons -->
        <div class="animate-slide-in delay-400">
            @include('components.claims.claim-action', ['claim' => $claim])
        </div>
    </div>
</div>

@php
    $locationData = $claim->locations
        ->sortBy('order')
        ->map(fn($location) => [
            'from_location' => $location->from_location,
            'to_location' => $location->to_location,
            'order' => $location->order,
            'distance' => $location->distance
        ])
        ->values()
        ->toArray();
@endphp

<script>
    var claimLocations = @json($locationData);
</script>

@vite(['resources/js/maps/review-map.js'])
@endsection
