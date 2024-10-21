@php
use App\Services\ClaimService;
use App\Models\Claim;
@endphp

<x-layout>
    <div class="wgg-box-border-shadow p-6">
        <div class="flex flex-col px-4 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Claims Approval</h1>
            <span class="text-red-500 text-sm italic">Temporary data going to be dump into table for testing purpose</span>
        </div>

        <div class="flex justify-between items-center mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full">
                <div class="bg-white space-y-2 p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-600">Total Claims to Review</p>
                    <p class="text-3xl font-semibold text-gray-300">{{ Claim::count() }}</p>
                </div>
                <div class="bg-white space-y-2 p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-600">Pending Review</p>
                    <p class="text-3xl font-semibold text-gray-300">{{ Claim::where('status', '!=', Claim::STATUS_DONE)->count() }}</p>
                </div>
                <div class="bg-white space-y-2 p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-600">Approved Claims</p>
                    <p class="text-3xl font-semibold text-gray-300">{{ Claim::where('status', Claim::STATUS_APPROVED_FINANCE)->count() }}</p>
                </div>
                <div class="bg-white space-y-2 p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-600">Total Amount to Review</p>
                    <p class="text-3xl font-semibold text-gray-300">RM {{ number_format(Claim::sum('petrol_amount') + Claim::sum('toll_amount'), 2) }}</p>
                </div>
            </div>
        </div>

        <div class="flex-col flex gap-4">
            <div class="overflow-x-auto shadow-md sm:rounded-lg p-4">
                <div class="flex justify-end mb-4">
                    
                    <div class="flex flex-col sm:flex-row gap-2">
                        <select id="sortSelect" onchange="sortTable(this.value)" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 focus:ring-2 focus:ring-blue-700 focus:text-blue-700">
                            <option value="">Sort by...</option>
                            <option value="status">Status</option>
                            <option value="submitted_at">Submitted</option>
                            <option value="user">Submitted By</option>
                            <option value="title">Title</option>
                            <option value="date_from">Date From</option>
                            <option value="date_to">Date To</option>
                        </select>
                        <div class="inline-flex rounded-md shadow-sm" role="group">
                            <button type="button" onclick="toggleSortOrder('asc')" id="sortAsc" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700">
                                Ascending
                            </button>
                            <button type="button" onclick="toggleSortOrder('desc')" id="sortDesc" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700">
                                Descending
                            </button>
                        </div>
                    </div>
                </div>
                
                <table id="claimsTable" class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-white uppercase bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID</th>
                            <th scope="col" class="px-6 py-3">Submitted</th>
                            <th scope="col" class="px-6 py-3">Submitted By</th>
                            <th scope="col" class="px-6 py-3">Title</th>
                            <th scope="col" class="px-6 py-3">Date From</th>
                            <th scope="col" class="px-6 py-3">Date To</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($claims as $claim)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $claim->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $claim->submitted_at->format('d-m-Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $claim->user->first_name . ' ' . $claim->user->second_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $claim->title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $claim->date_from->format('d-m-Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $claim->date_to->format('d-m-Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="claims-dashboard-status-badge inline-flex items-center justify-start w-full py-2 px-4 rounded font-bold text-white text-xs whitespace-nowrap overflow-hidden
                                        @if ($claim->status == Claim::STATUS_SUBMITTED)
                                            bg-orange-500
                                        @elseif ($claim->status == Claim::STATUS_APPROVED_ADMIN)
                                            bg-yellow-500
                                        @elseif ($claim->status == Claim::STATUS_APPROVED_DATUK)
                                            bg-blue-500
                                        @elseif ($claim->status == Claim::STATUS_APPROVED_HR)
                                            bg-purple-500
                                        @elseif ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                            bg-indigo-500
                                        @elseif ($claim->status == Claim::STATUS_REJECTED)
                                            bg-red-500
                                        @elseif ($claim->status == Claim::STATUS_DONE)
                                            bg-green-500
                                        @endif
                                    ">
                                        @if ($claim->status == Claim::STATUS_SUBMITTED)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-1-circle-fill mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M9.283 4.002H7.971L6.072 5.385v1.271l1.834-1.318h.065V12h1.312z"/>
                                            </svg>
                                            <span class="truncate">Submitted</span>
                                        @elseif ($claim->status == Claim::STATUS_APPROVED_ADMIN)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-2-circle-fill mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.646 6.24c0-.691.493-1.306 1.336-1.306.756 0 1.313.492 1.313 1.236 0 .697-.469 1.23-.902 1.705l-2.971 3.293V12h5.344v-1.107H7.268v-.077l1.974-2.22.096-.107c.688-.763 1.287-1.428 1.287-2.43 0-1.266-1.031-2.215-2.613-2.215-1.758 0-2.637 1.19-2.637 2.402v.065h1.271v-.07Z"/>
                                            </svg>
                                            <span class="truncate">Admin Approved</span>
                                        @elseif ($claim->status == Claim::STATUS_APPROVED_DATUK)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-3-circle-fill mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-8.082.414c.92 0 1.535.54 1.541 1.318.012.791-.615 1.36-1.588 1.354-.861-.006-1.482-.469-1.54-1.066H5.104c.047 1.177 1.05 2.144 2.754 2.144 1.653 0 2.954-.937 2.93-2.396-.023-1.278-1.031-1.846-1.734-1.916v-.07c.597-.1 1.505-.739 1.482-1.876-.03-1.177-1.043-2.074-2.637-2.062-1.675.006-2.59.984-2.625 2.12h1.248c.036-.556.557-1.054 1.348-1.054.785 0 1.348.486 1.348 1.195.006.715-.563 1.237-1.342 1.237h-.838v1.072h.879Z"/>
                                            </svg>
                                            <span class="truncate">Datuk Approved</span>
                                        @elseif ($claim->status == Claim::STATUS_APPROVED_HR)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-4-circle-fill mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0ZM7.519 5.057c-.886 1.418-1.772 2.838-2.542 4.265v1.12H8.85V12h1.26v-1.559h1.007V9.334H10.11V4.002H8.176c-.218.352-.438.703-.657 1.055ZM6.225 9.281v.053H8.85V5.063h-.065c-.867 1.33-1.787 2.806-2.56 4.218Z"/>
                                            </svg>
                                            <span class="truncate">HR Approved</span>
                                        @elseif ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-5-circle-fill mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0Zm-8.006 4.158c1.74 0 2.924-1.119 2.924-2.806 0-1.641-1.178-2.584-2.56-2.584-.897 0-1.442.421-1.612.68h-.064l.193-2.344h3.621V4.002H5.791L5.445 8.63h1.149c.193-.358.668-.809 1.435-.809.85 0 1.582.604 1.582 1.57 0 1.085-.779 1.682-1.57 1.682-.697 0-1.389-.31-1.53-1.031H5.276c.065 1.213 1.149 2.115 2.72 2.115Z"/>
                                            </svg>
                                            <span class="truncate">Fin. Approved</span>
                                        @elseif ($claim->status == Claim::STATUS_REJECTED)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                            </svg>
                                            <span class="truncate">Rejected</span>
                                        @elseif ($claim->status == Claim::STATUS_DONE)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                            </svg>
                                            <span class="truncate">Payment</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                        @if ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                            <form action="{{ route('claims.approve', $claim->id) }}" method="POST" class="inline w-full">
                                                @csrf
                                                <button type="submit" class="claims-approval-action-button bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded inline-flex items-center transition duration-300 ease-in-out w-full justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                                    </svg>
                                                    <span>Mark as Done</span>
                                                </button>   
                                            </form>
                                        @else
                                            <a href="{{ route('claims.review', $claim->id) }}" class="claims-review-action-button bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded inline-flex items-center transition duration-300 ease-in-out w-full justify-start">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square mr-2 flex-shrink-0" viewBox="0 0 16 16">
                                                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                                </svg>
                                                <span>Review</span>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-gray-500 w-full inline-block">No Action Required</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100">
                            <td colspan="8" class="px-6 py-4 text-sm font-medium text-gray-900">
                                <strong>Total Entries:</strong> {{ $claims->count() }} / {{ $claims->total() }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                @if ($claims->hasPages())
                    <div class="mt-4">
                        {{ $claims->links() }}
                    </div>
                @endif
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('claimsTable');
            const sortSelect = document.getElementById('sortSelect');
            const sortAsc = document.getElementById('sortAsc');
            const sortDesc = document.getElementById('sortDesc');
            let currentSortColumn = '';
            let isAscending = true;

            function sortTable(column, ascending) {
                const rows = Array.from(table.querySelectorAll('tbody tr'));
                const columnIndex = getColumnIndex(column);

                rows.sort((a, b) => {
                    const aValue = a.cells[columnIndex].textContent.trim();
                    const bValue = b.cells[columnIndex].textContent.trim();

                    if (column === 'submitted_at' || column === 'date_from' || column === 'date_to') {
                        return ascending ? new Date(aValue) - new Date(bValue) : new Date(bValue) - new Date(aValue);
                    } else {
                        return ascending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                    }
                });

                const tbody = table.querySelector('tbody');
                rows.forEach(row => tbody.appendChild(row));
            }

            function getColumnIndex(column) {
                switch(column) {
                    case 'status': return 6;
                    case 'submitted_at': return 1;
                    case 'user': return 2;
                    case 'title': return 3;
                    case 'date_from': return 4;
                    case 'date_to': return 5;
                    default: return 0;
                }
            }

            sortSelect.addEventListener('change', function() {
                currentSortColumn = this.value;
                if (currentSortColumn) {
                    sortTable(currentSortColumn, isAscending);
                }
            });

            sortAsc.addEventListener('click', function() {
                isAscending = true;
                if (currentSortColumn) {
                    sortTable(currentSortColumn, isAscending);
                }
            });

            sortDesc.addEventListener('click', function() {
                isAscending = false;
                if (currentSortColumn) {
                    sortTable(currentSortColumn, isAscending);
                }
            });
        });
        </script>
        </div>
    </div>
</x-layout>
