@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Claims Dashboard</h2>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
                <div class="w-full sm:w-auto mb-4 sm:mb-0">
                    <label for="sortSelect" class="block text-sm font-medium text-gray-700 mb-2">Sort by:</label>
                    <select id="sortSelect" class="form-select block w-full sm:w-auto">
                        <option value="submitted_at">Submitted Date</option>
                        <option value="date_from">Date From</option>
                        <option value="date_to">Date To</option>
                        <option value="status">Status</option>
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <label for="searchInput" class="block text-sm font-medium text-gray-700 mb-2">Search:</label>
                    <input type="text" id="searchInput" class="form-input block w-full sm:w-auto" placeholder="Search claims...">
                </div>
            </div>

            <x-claims.claims-table :claims="$claims" :claimService="$claimService" actions="dashboard" />
        </div>
    </div>
</div>

@vite(['resources/js/approval.js'])
@endsection