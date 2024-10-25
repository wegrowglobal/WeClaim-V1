@extends('layouts.app')

@section('content')
    <div class="max-w-full-custom border border-wgg-border">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-10 space-y-4">
                <h2 class="heading-1">Claims Dashboard</h2>

                <!-- Claims Statistics -->
                <div class="space-y-4">
                    <h3 class="heading-2">Claims Overview</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <x-others.statistics-card title="Total Claims to Review" value="{{ $statistics['totalClaims'] }}" color="text-blue-600" />
                        <x-others.statistics-card title="Pending Review" value="{{ $statistics['pendingReview'] }}" color="text-yellow-600" />
                        <x-others.statistics-card title="Approved Claims" value="{{ $statistics['approvedClaims'] }}" color="text-green-600" />
                        <x-others.statistics-card title="Total Amount to Review" value="RM {{ number_format($statistics['totalAmount'], 2) }}" color="text-indigo-600" />
                    </div>
                </div>

                <!-- Claims Table -->
                <div class="mb-10">
                    <x-claims.claims-table :claims="$claims" :claimService="$claimService" actions="dashboard" />
                </div>
            </div>
        </div>
    </div>
@endsection