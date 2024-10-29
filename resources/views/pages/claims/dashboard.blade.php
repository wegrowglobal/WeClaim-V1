@extends('layouts.app')

@section('content')

<div class="w-full">
    <h2 class="text-3xl font-bold text-gray-900 mb-8">Claims Dashboard</h2>

    <div class="bg-white border border-wgg-border shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
                <div class="w-full sm:w-auto">
                    <input type="text" id="searchInput" class="form-input block w-full sm:w-auto" placeholder="Search Claims...">
                </div>
            </div>

            <x-claims.claims-table :claims="$claims" :claimService="$claimService" actions="dashboard" />
        </div>
    </div>
</div>

@vite(['resources/js/filter.js'])
@endsection