@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <x-claims.claim-header title="Claim Details" subtitle="View and manage claim information" :status="$claim->status" />

        <div class="space-y-6">
            <x-claims.claim-details :claim="$claim" />
            <x-claims.toll-details :claim="$claim" />
            <x-claims.accommodations-table :claim="$claim" :accommodations="$claim->accommodations" />
            <x-claims.cost-summary :claim="$claim" />

            <div class="animate-slide-in space-y-4 delay-300">
                @include('components.claims.locations-table', ['locations' => $claim->locations])
            </div>
            
            <!-- Action Buttons -->
            <div class="animate-slide-in delay-400">
                @include('components.claims.claim-action', ['claim' => $claim])
            </div>
        </div>
    </div>

    @php
        // Prepare location data with all necessary fields from DB
        $locationData = $claim->locations
            ->sortBy('order')
            ->map(
                fn($location) => [
                    'from_location' => $location->from_location,
                    'to_location' => $location->to_location,
                    'order' => $location->order,
                    'distance' => (float) $location->distance,
                    'duration' => $location->duration,
                    'from_latitude' => $location->from_latitude,
                    'from_longitude' => $location->from_longitude,
                    'to_latitude' => $location->to_latitude,
                    'to_longitude' => $location->to_longitude,
                    'cost' => number_format($location->distance * config('claims.rate_per_km'), 2),
                ],
            )
            ->values()
            ->toArray();

        // Calculate totals from DB values
        $totalDistance = $claim->total_distance;
        $totalDuration = $claim->total_duration;
        $totalCost = number_format($claim->total_distance * config('claims.rate_per_km'), 2);
    @endphp

    <script>
        var claimLocations = @json($locationData);
        var claimTotals = @json([
            'distance' => $totalDistance,
            'duration' => $totalDuration,
            'cost' => $totalCost
        ]);
    </script>

    @vite(['resources/js/maps/review-map.js'])
@endsection
