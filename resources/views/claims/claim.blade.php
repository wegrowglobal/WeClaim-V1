@php
use App\Models\Claim;
@endphp

<x-layout>
    <div class="max-w-full-custom">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-10 space-y-8">

                <!-- Header Section -->
                <div class="flex-between items-center mb-6">
                    <a href="{{ route('claims.dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center font-medium text-sm transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill mr-2" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                        </svg>
                        Back
                    </a>
                    <h2 class="text-md font-semibold text-wgg-black-300">Claim ID {{ $claim->id }}</h2>
                </div>

                <!-- Basic Details -->
                <div class="space-y-2">
                    <h3 class="heading-2">Basic Details</h3>
                    <div class="bg-white overflow-hidden shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Status</th>
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
                                </tr>
                                <tr>
                                    <th class="table-horizontal-header">Submitted Date</th>
                                    <td class="table-horizontal-item">{{ $claim->submitted_at->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th class="table-horizontal-header">Claim Title</th>
                                    <td class="table-horizontal-item">{{ $claim->title }}</td>
                                </tr>
                                <tr>
                                    <th class="table-horizontal-header">Staff Name</th>
                                    <td class="table-horizontal-item">{{ $claim->user->first_name . ' ' . $claim->user->second_name }}</td>
                                </tr>
                                <tr>
                                    <th class="table-horizontal-header">Description</th>
                                    <td class="table-horizontal-item">{{ $claim->description }}</td>
                                </tr>
                                <tr>
                                    <th class="table-horizontal-header">Date From</th>
                                    <td class="table-horizontal-item">{{ $claim->date_from->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th class="table-horizontal-header">Date To</th>
                                    <td class="table-horizontal-item">{{ $claim->date_to->format('d-m-Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Toll Details -->
                <div class="space-y-2">
                    <h3 class="heading-2">Toll Details</h3>
                    <div class="bg-white overflow-hidden shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <th class="table-horizontal-header">Toll Amount</th>
                                    <td class="table-horizontal-item">{{ 'RM' . $claim->toll_amount }}</td>
                                </tr>
                                <tr>
                                    <th class="table-horizontal-header">Toll Document</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']) }}" target="_blank" class="text-blue-600 hover:text-blue-900">View Toll Document</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="table-horizontal-header">Email Approval</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']) }}" target="_blank" class="text-blue-600 hover:text-blue-900">View Email Document</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Trip Details -->
                <div class="space-y-2">
                    <h3 class="heading-2">Trip Details</h3>
                    <div class="bg-white overflow-hidden shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if ($claim->locations && $claim->locations->count() > 0)
                                    @foreach ($claim->locations->sortBy('order') as $location)
                                        <tr>
                                            <th class="table-horizontal-header">Location {{ $location->order }}</th>
                                            <td class="table-horizontal-item">{{ $location->location }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th class="table-horizontal-header">No locations found</th>
                                        <td class="table-horizontal-item">Contact System Administrator</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th class="table-horizontal-header">Total Distance</th>
                                    <td class="table-horizontal-item">{{ $claim->total_distance . ' KM' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="map" style="height: 500px; width: 100%" class="rounded-lg border border-wgg-border shadow-sm">
                    <div id="route-info-panel"></div>
                </div>

                <!-- Actions -->
                <div class="mt-6">
                    @if ($claim->status == Claim::STATUS_SUBMITTED)
                        <form action="" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                Cancel Claim
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="icon-small" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                </svg>
                            </button>
                        </form>
                    @elseif ($claim->status == Claim::STATUS_REJECTED)
                        <a href="{{ route('claims.new', ['claim_id' => $claim->id]) }}" class="btn btn-success">
                            Re-Submit Claim
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        var claimLocations = @json($claim->locations);
    </script>
    @vite('resources/js/review.js')
</x-layout>
