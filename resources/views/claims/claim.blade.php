@php
use App\Models\Claim;
@endphp

<x-layout>
    <div class="max-w-full rounded-lg border border-wgg-border">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-8 py-10">
                <div class="flex justify-between items-center mb-6">
                    <a href="{{ route('claims.dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill mr-2" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                        </svg>
                        Back
                    </a>
                    <h2 class="text-2xl font-semibold text-gray-900">Claim ID: {{ $claim->id }}</h2>
                </div>
                <!-- Basic Details -->
                <div class="mb-10">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Basic Details</h3>
                    <div class="bg-white overflow-hidden shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Status</th>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
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
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted Date</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $claim->submitted_at->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claim Title</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $claim->title }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $claim->user->first_name . ' ' . $claim->user->second_name }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $claim->description }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date From</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $claim->date_from->format('d-m-Y') }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date To</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $claim->date_to->format('d-m-Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Toll Details -->
                <div class="mb-10">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Toll Details</h3>
                    <div class="bg-white overflow-hidden shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toll Amount</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ 'RM' . $claim->toll_amount }}</td>
                                </tr>
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toll Document</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']) }}" target="_blank" class="text-blue-600 hover:text-blue-900">View Toll Document</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Approval</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']) }}" target="_blank" class="text-blue-600 hover:text-blue-900">View Email Document</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Trip Details -->
                <div class="mb-10">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Trip Details</h3>
                    <div class="bg-white overflow-hidden shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if ($claim->locations && $claim->locations->count() > 0)
                                    @foreach ($claim->locations->sortBy('order') as $location)
                                        <tr>
                                            <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location {{ $location->order }}</th>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $location->location }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No locations found</th>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Contact System Administrator</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th class="w-1/3 px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Distance</th>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $claim->total_distance . ' KM' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="map" style="height: 500px; width: 100%" class="rounded-lg shadow-md">
                    <div id="route-info-panel"></div>
                </div>

                <!-- Actions -->
                <div class="mt-6">
                    @if ($claim->status == Claim::STATUS_SUBMITTED)
                        <form action="" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Cancel Claim
                            </button>
                        </form>
                    @elseif ($claim->status == Claim::STATUS_REJECTED)
                        <a href="{{ route('claims.new', ['claim_id' => $claim->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Re-Submit Claim
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
