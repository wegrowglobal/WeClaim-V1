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
                   class="w-fit pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-wgg-border"
                   placeholder="Search claims...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="claimsTable" class="min-w-full divide-y divide-gray-200 text-xs">
            <thead>
                <tr>
                    <th scope="col" class="w-16 px-3 py-2 text-left text-gray-500 font-medium" data-sort="id">
                        <div class="flex items-center gap-1 cursor-pointer">
                            ID
                            <i class="fas fa-sort ml-1 opacity-60"></i>
                        </div>
                    </th>
                    @if(Auth::user()->role->name === 'Admin' || Auth::user()->role->name === 'Finance')
                    <th scope="col" class="w-10 px-3 py-2 text-left text-gray-500 font-medium">
                        <span class="sr-only">Export</span>
                    </th>
                    @endif
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
            <tbody class="divide-y divide-gray-200">
                @foreach ($claims as $claim)
                    <tr class="hover:bg-gray-50/50 transition-colors duration-200">

                        <!-- ID -->
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $claim->id }}</td>
                        @if(Auth::user()->role->name === 'Admin' || Auth::user()->role->name === 'Finance')

                        <!-- Export -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            <button type="submit" class="text-green-600 hover:text-green-900">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </button>
                        </td>
                        @endif

                        <!-- Date -->
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            {{ $claim->submitted_at->format('d/m/y') }}
                        </td>

                        <!-- By -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-full flex items-center justify-center text-white text-xs" 
                                     style="background-color: {{ '#' . substr(md5($claim->user->first_name), 0, 6) }}">
                                    {{ strtoupper(substr($claim->user->first_name, 0, 1)) }}
                                </div>
                                <span class="hidden sm:inline text-gray-600">{{ $claim->user->first_name }}</span>
                            </div>
                        </td>

                        <!-- Title -->
                        <td class="px-4 py-3">
                            <div class="max-w-[150px] truncate text-gray-600">{{ $claim->title }}</div>
                        </td>

                        <!-- Period -->
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            {{ $claim->date_from->format('d/m/y') }} - {{ $claim->date_to->format('d/m/y') }}
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-status-badge :status="$claim->status" />
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            @if ($actions === 'approval')
                                @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                    <a href="{{ route('claims.review', $claim->id) }}" 
                                       class="text-xs font-medium text-indigo-600 hover:text-indigo-900">
                                        Review
                                    </a>
                                @else
                                    <span class="text-xs text-gray-500">Pending</span>
                                @endif
                            @elseif ($actions === 'dashboard')
                                <a href="{{ route('claims.view', $claim->id) }}" 
                                   class="text-xs font-medium text-indigo-600 hover:text-indigo-900">
                                    View
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
