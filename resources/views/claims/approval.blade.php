@php
use App\Services\ClaimService;
use App\Models\Claim;
@endphp

@if(Auth::user()->role !== 'Staff')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-layout>
        <div class="max-w-full-custom border border-wgg-border">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-10 space-y-4">
                    <h2 class="heading-1">Claims Approval</h2>

                    <!-- Claims Statistics -->
                    <div class="space-y-4">
                        <h3 class="heading-2">Claims Overview</h3>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                                <p class="text-sm font-medium text-gray-500 mb-2">Total Claims to Review</p>
                                <p class="text-3xl font-bold text-blue-600">{{ Claim::count() }}</p>
                            </div>
                            <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                                <p class="text-sm font-medium text-gray-500 mb-2">Pending Review</p>
                                <p class="text-3xl font-bold text-yellow-600">{{ Claim::where('status', '!=', Claim::STATUS_DONE)->count() }}</p>
                            </div>
                            <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                                <p class="text-sm font-medium text-gray-500 mb-2">Approved Claims</p>
                                <p class="text-3xl font-bold text-green-600">{{ Claim::where('status', Claim::STATUS_APPROVED_FINANCE)->count() }}</p>
                            </div>
                            <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                                <p class="text-sm font-medium text-gray-500 mb-2">Total Amount to Review</p>
                                <p class="text-3xl font-bold text-indigo-600">RM {{ number_format(Claim::sum('petrol_amount') + Claim::sum('toll_amount'), 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Claims Table -->
                    <div class="mb-10">
                        <div class="overflow-x-auto space-y-4">
                            <div class="flex flex-col sm:flex-row space-x-2 p-2 pl-0">
                                <select id="sortSelect" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 m-px">
                                    <option value="">Sort By</option>
                                    <option value="id">ID</option>
                                    <option value="submitted_at">Submitted</option>
                                    <option value="user">Submitted By</option>
                                    <option value="title">Title</option>
                                    <option value="date_from">Date From</option>
                                    <option value="date_to">Date To</option>
                                    <option value="status">Status</option>
                                </select>
                                <button id="sortOrderBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 m-px">
                                    Ascending
                                </button>
                                <div class="relative">
                                    <input type="text" id="searchInput" placeholder="Search Claims..." class="text-sm pl-10 pr-4 py-2 border rounded-md w-full">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>


                            <div class="bg-white overflow-hidden">
                                <table id="claimsTable" class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="table-header">ID</th>
                                            <th scope="col" class="table-header">Submitted</th>
                                            <th scope="col" class="table-header">Submitted By</th>
                                            <th scope="col" class="table-header">Title</th>
                                            <th scope="col" class="table-header">Date From</th>
                                            <th scope="col" class="table-header">Date To</th>
                                            <th scope="col" class="table-header">Status</th>
                                            <th scope="col" class="table-header">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($claims as $claim)
                                            <tr class="claim-row" data-need-review="{{ $claimService->canReviewClaim(Auth::user(), $claim) ? 'true' : 'false' }}">
                                                <td class="table-item">{{ $claim->id }}</td>
                                                <td class="table-item">{{ $claim->submitted_at->format('d-m-Y') }}</td>
                                                <td class="table-item">
                                                    <div class="flex items-center">
                                                       {{--  @if ($claim->user->profile_picture_url) --}}
                                                        @if($claim->user->profile_picture && Storage::disk('public')->exists(auth()->user()->profile_picture))
                                                            <img src="{{ Storage::url('public/' . $claim->user->profile_picture) }}" alt="Profile Picture" class="h-8 w-8 rounded-full mr-2 object-cover">
                                                        @else
                                                            <div class="h-8 w-8 rounded-full mr-2 flex items-center justify-center text-white font-medium text-lg" style="background-color: {{ '#' . substr(md5($claim->user->first_name), 0, 6) }}">
                                                                {{ strtoupper(substr($claim->user->first_name, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                        {{ $claim->user->first_name . ' ' . $claim->user->second_name }}
                                                    </div>
                                                </td>                                                <td class="table-item">{{ $claim->title }}</td>
                                                <td class="table-item">{{ $claim->date_from->format('d-m-Y') }}</td>
                                                <td class="table-item">{{ $claim->date_to->format('d-m-Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="status-badge
                                                        @if ($claim->status == Claim::STATUS_SUBMITTED)
                                                            bg-orange-100 text-orange-800
                                                        @elseif ($claim->status == Claim::STATUS_APPROVED_ADMIN)
                                                            bg-yellow-100 text-yellow-800
                                                        @elseif ($claim->status == Claim::STATUS_APPROVED_DATUK)
                                                            bg-blue-100 text-blue-800
                                                        @elseif ($claim->status == Claim::STATUS_APPROVED_HR)
                                                            bg-purple-100 text-purple-800
                                                        @elseif ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                                            bg-indigo-100 text-indigo-800
                                                        @elseif ($claim->status == Claim::STATUS_REJECTED)
                                                            bg-red-100 text-red-800
                                                        @elseif ($claim->status == Claim::STATUS_DONE)
                                                            bg-green-100 text-green-800
                                                        @endif
                                                    ">
                                                        {{ str_replace('_', ' ', $claim->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    @if ($claimService->canReviewClaim(Auth::user(), $claim))
                                                        @if ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                                            <form action="{{ route('claims.approve', $claim->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" class="text-green-600 hover:text-green-900">Mark as Done</button>
                                                            </form>
                                                        @else
                                                            <a href="{{ route('claims.review', $claim->id) }}" class="text-blue-600 hover:text-blue-900">Start Review</a>
                                                        @endif
                                                    @else
                                                        <span class="text-wgg-black-400 font-normal">No Action Required</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($claims->hasPages())
                                <div class="m-1">
                                    {{ $claims->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @vite([
        'resources/js/approval_table.js',
        ])
    </x-layout>

@else
    <script>window.location.href = "{{ route('home') }}";</script>
@endif
