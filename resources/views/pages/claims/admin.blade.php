@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in mb-8 p-6">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 sm:text-3xl">Claims Management</h2>
                    <p class="mt-1 text-sm text-gray-500 sm:text-base">Manage and monitor all claims in the system</p>
                </div>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="mb-4 flex items-center justify-end gap-2">
            <button class="view-toggle-btn active inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100"
                data-view="table" onclick="toggleView('table')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Table View
            </button>
            <button class="view-toggle-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50"
                data-view="grid" onclick="toggleView('grid')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Grid View
            </button>
        </div>

        <!-- Claims Table Section -->
        <div id="tableView">
            <x-claims.admin-claims-table :claims="$claims" :claimService="$claimService" actions="admin" />
        </div>

        <!-- Claims Grid Section -->
        <div id="gridView" class="hidden">
            <x-claims.claims-list :claims="$claims" :claimService="$claimService" actions="admin" />
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-medium text-gray-900">Delete Claim</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete this claim? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form id="deleteForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <button
                                class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 sm:ml-3 sm:w-auto"
                                type="submit">
                                Delete
                            </button>
                        </form>
                        <button
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                            onclick="hideDeleteModal()" type="button">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/filter.js', 'resources/js/view-toggle.js', 'resources/js/admin.js'])
@endsection
