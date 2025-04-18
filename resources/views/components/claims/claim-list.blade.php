@props([
    'claims' => [],
    'claimService'
])

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Claim ID</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Submitted By</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date Range</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount (RM)</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                    <span class="sr-only">Actions</span>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($claims as $claim)
                <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#{{ $claim->id }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $claim->user->full_name ?? 'N/A' }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $claim->date_from->format('d M Y') }} - {{ $claim->date_to->format('d M Y') }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($claim->total_amount, 2) }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        @php
                            $statusClass = match($claim->status) {
                                \App\Models\Claim\Claim::STATUS_SUBMITTED, 
                                \App\Models\Claim\Claim::STATUS_PENDING_HR, 
                                \App\Models\Claim\Claim::STATUS_PENDING_DATUK => 'bg-yellow-100 text-yellow-800',
                                \App\Models\Claim\Claim::STATUS_APPROVED_HR, 
                                \App\Models\Claim\Claim::STATUS_APPROVED_DATUK, 
                                \App\Models\Claim\Claim::STATUS_APPROVED_FINANCE, 
                                \App\Models\Claim\Claim::STATUS_DONE => 'bg-green-100 text-green-800',
                                \App\Models\Claim\Claim::STATUS_REJECTED, 
                                \App\Models\Claim\Claim::STATUS_CANCELLED => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                            {{ $claim->status }}
                        </span>
                    </td>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <a href="{{ route('claims.show', $claim->id) }}" class="text-indigo-600 hover:text-indigo-900">View<span class="sr-only">, Claim #{{ $claim->id }}</span></a>
                        {{-- Add other conditional actions based on role/status if needed --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                        No claims found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div> 