@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="card animate-slide-in mb-8 p-4 sm:p-8">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="heading-1 text-2xl sm:text-3xl">Claims Management</h2>
                    <p class="mt-1 text-sm text-gray-600 sm:text-base">Manage all claims in the system</p>
                </div>
            </div>
        </div>

        <!-- Claims Table Section -->
        <div class="card animate-slide-in delay-200">
            <div class="p-6">
                <x-claims.admin-claims-table :claims="$claims" :claimService="$claimService" actions="admin" />
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="fixed inset-0 z-50 hidden" id="deleteModal">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Delete Claim</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to delete this claim? This action
                                    cannot be undone.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                            id="confirmDelete" type="button">Delete</button>
                        <button
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                            type="button" onclick="closeDeleteModal()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/filter.js', 'resources/js/admin.js'])
    @endpush
@endsection
