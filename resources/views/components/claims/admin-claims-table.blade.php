@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions'])

<div class="bg-white rounded-lg shadow-sm ring-1 ring-black/5 animate-slide-in delay-200">
    @if ($claims->isEmpty())
        <div class="flex flex-col items-center justify-center py-12">
            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No Claims Found</h3>
            <p class="mt-1 text-sm text-gray-500">There are no claims to display at the moment.</p>
        </div>
    @else
        <!-- Search Input -->
        <div class="border-b border-gray-100 p-6">
            <div class="relative focus-within:shadow-sm">
                <input
                    class="w-full rounded-lg border border-gray-200 py-2.5 pl-10 pr-4 text-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
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
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-16 px-3 py-2 text-left text-xs font-medium text-gray-500" data-sort="id" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                ID
                                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="submitted" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Date
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-32 px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="user" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                By
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500" scope="col">
                            Description
                        </th>
                        <th class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="amount" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Amount
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500" data-sort="status" scope="col">
                            <div class="flex cursor-pointer items-center gap-1">
                                Status
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th class="w-24 px-4 py-3 text-right text-xs font-medium text-gray-500" scope="col">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($claims as $claim)
                        <tr class="text-xs hover:bg-gray-50/50">
                            <td class="whitespace-nowrap px-3 py-2 text-gray-600">
                                {{ $claim->id }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2 text-gray-600">
                                {{ $claim->created_at->format('d/m/Y') }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <x-profile.profile-picture :user="$claim->user" size="xs" />
                                    <span class="text-gray-600">{{ $claim->user->first_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="line-clamp-1 text-gray-600">
                                    {{ $claim->description }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                RM {{ number_format($claim->total_amount, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-claims.status-badge :status="$claim->status" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-100"
                                        href="{{ route('claims.review', $claim->id) }}">
                                        Review
                                    </a>
                                    @if ($claim->status === Claim::STATUS_DONE)
                                        <form class="inline" action="{{ route('claims.export', $claim->id) }}" method="POST">
                                            @csrf
                                            @method('POST')
                                            <button class="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-medium text-green-600 hover:bg-green-100"
                                                type="submit">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Export
                                            </button>
                                        </form>
                                    @endif
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
