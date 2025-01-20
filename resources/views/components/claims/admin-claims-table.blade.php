@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions'])

<div class="overflow-hidden rounded-lg bg-white">
    @if ($claims->isEmpty())
        <div class="py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-lg font-semibold text-gray-900">No Claims Found</h3>
            <p class="mt-1 text-sm text-gray-500">There are no claims to display at the moment.</p>
        </div>
    @else
        <!-- Search Input -->
        <div class="border-b border-gray-100 pb-4">
            <div class="relative focus-within:shadow-sm">
                <input
                    class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm focus:border-wgg-border focus:outline-none"
                    id="searchInput" type="text" placeholder="Search claims...">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="claimsTable">
                <thead class="bg-gray-50 text-xs">
                    <tr>
                        <th class="w-16 px-3 py-2 text-left font-medium text-gray-500" data-sort="id" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                ID
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-24 px-3 py-2 text-left font-medium text-gray-500" data-sort="submitted"
                            scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Date
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-32 px-3 py-2 text-left font-medium text-gray-500" data-sort="user" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                By
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500" scope="col">
                            Description
                        </th>
                        <th class="w-24 px-3 py-2 text-left font-medium text-gray-500" data-sort="amount"
                            scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Amount
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-24 px-3 py-2 text-left font-medium text-gray-500" data-sort="status"
                            scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Status
                                <i class="fas fa-sort ml-1 opacity-60"></i>
                            </div>
                        </th>
                        <th class="w-24 px-3 py-2 text-right font-medium text-gray-500" scope="col">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($claims as $claim)
                        <tr class="text-xs hover:bg-gray-50/50">
                            <td class="whitespace-nowrap px-3 py-3 text-gray-500">
                                {{ $claim->id }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-gray-500">
                                {{ $claim->created_at->format('d/m/Y') }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-gray-500">
                                {{ $claim->user->first_name }}
                            </td>
                            <td class="px-3 py-3 text-gray-500">
                                <div class="line-clamp-1">
                                    {{ $claim->description }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-gray-500">
                                RM {{ number_format($claim->petrol_amount + $claim->toll_amount, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-gray-500">
                                <x-claims.status-badge :status="$claim->status" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-gray-500">
                                <div class="flex justify-end gap-2">
                                    <a class="text-xs font-medium text-blue-500 hover:text-blue-700"
                                        href="{{ route('claims.view', $claim->id) }}">
                                        View
                                    </a>
                                    <button class="text-xs font-medium text-red-500 hover:text-gray-700"
                                        onclick="showDeleteModal({{ $claim->id }})">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initializeTableSorting === 'function') {
                initializeTableSorting();
            } else {
                console.error('Table sorting functionality not loaded');
            }
        });
    </script>
@endpush
