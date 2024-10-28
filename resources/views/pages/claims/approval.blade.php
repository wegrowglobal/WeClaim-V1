@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Claims Approval</h2>

    <div class="bg-white border border-wgg-border shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
                <div class="w-full sm:w-auto">
                    <input type="text" id="searchInput" class="form-input block w-full sm:w-auto" placeholder="Search Claims...">
                </div>
            </div>

            <x-claims.claims-table :claims="$claims" :claimService="$claimService" actions="approval" />
        </div>
    </div>
</div>

@vite(['resources/js/approval.js'])
@endsection