@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in mb-8 p-6">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 sm:text-3xl">My Claims</h2>
                    <p class="mt-1 text-sm text-gray-500 sm:text-base">View and manage your claims</p>
                </div>
            </div>
        </div>

        <!-- Claims Table Section -->
        <div id="tableView">
            <x-claims.claims-table :claims="$claims" :claimService="$claimService" actions="dashboard" />
        </div>

        <!-- Claims Grid Section -->
        <div id="gridView" class="hidden">
            <x-claims.claims-list :claims="$claims" :claimService="$claimService" actions="dashboard" />
        </div>
    </div>

    @vite(['resources/js/filter.js'])
@endsection
