@php
    use App\Models\Claim;
@endphp

@props(['claims', 'claimService', 'actions', 'rows'])

<div class="bg-white rounded-lg overflow-hidden">
    <!-- Search Input -->
    <div class="border-b border-gray-100 pb-4">
        <div class="relative focus-within:shadow-sm">
            <input type="text" 
                   id="searchInput"
                   class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-wgg-border"
                   placeholder="Search claims...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="claimsTable">
            <thead class="text-xs bg-gray-50">
                <tr>
                    <th scope="col" class="w-16 px-3 py-2 text-left text-gray-500 font-medium" data-sort="id">
                        <div class="flex items-center gap-1 cursor-pointer">
                            ID
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th scope="col" class="w-24 px-3 py-2 text-left text-gray-500 font-medium" data-sort="submitted">
                        <div class="flex items-center gap-1 cursor-pointer">
                            Date
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th scope="col" class="w-32 px-3 py-2 text-left text-gray-500 font-medium" data-sort="user">
                        <div class="flex items-center gap-1 cursor-pointer">
                            By
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th scope="col" class="px-3 py-2 text-left text-gray-500 font-medium" data-sort="title">
                        <div class="flex items-center gap-1 cursor-pointer">
                            Title
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th scope="col" class="w-40 px-3 py-2 text-left text-gray-500 font-medium" data-sort="dateFrom">
                        <div class="flex items-center gap-1 cursor-pointer">
                            Period
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th scope="col" class="w-28 px-3 py-2 text-left text-gray-500 font-medium" data-sort="status">
                        <div class="flex items-center gap-1 cursor-pointer">
                            Status
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    <th scope="col" class="w-20 px-3 py-2 text-right text-gray-500 font-medium">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($claims as $claim)
                    <tr class="text-xs hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-600">{{ $claim->id }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $claim->submitted_at->format('d/m/y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <x-profile.profile-picture :user="$claim->user" size="sm" />
                                <span class="text-gray-600">{{ $claim->user->first_name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="max-w-[250px] truncate text-gray-600">{{ $claim->title }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            {{ $claim->date_from->format('d/m/y') }} - {{ $claim->date_to->format('d/m/y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-status-badge :status="$claim->status" />
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            @if ($actions === 'approval')
                                @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                    <a href="{{ route('claims.review', $claim->id) }}" 
                                       class="text-xs font-medium text-indigo-600 hover:text-indigo-900">
                                        Review
                                    </a>
                                @else
                                    <span class="text-xs text-gray-500">
                                        @switch($claim->status)
                                            @case(Claim::STATUS_DONE)
                                                Completed
                                                @break
                                            @case(Claim::STATUS_CANCELLED)
                                                Cancelled
                                                @break
                                            @case(Claim::STATUS_REJECTED)
                                                Rejected
                                                @break
                                            @case(Claim::STATUS_APPROVED_FINANCE)
                                                Ready for Payment
                                                @break
                                            @default
                                                Pending
                                        @endswitch
                                    </span>
                                @endif
                            @elseif ($actions === 'dashboard')
                                <a href="{{ route('claims.view', $claim->id) }}" 
                                   class="text-xs font-medium text-indigo-600 hover:text-indigo-900">
                                    View Details
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
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
