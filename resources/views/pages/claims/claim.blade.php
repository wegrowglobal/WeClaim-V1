@extends('layouts.app')

@php
    use App\Models\Claim;
@endphp

@section('content')
    <div class="w-full">
        <div class="rounded-lg overflow-hidden">
            <div class="space-y-8">

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
                <h3 class="heading-2">Basic Details</h3>
                <div class="bg-white overflow-hidden rounded-lg border border-wgg-border">
                    <div class="bg-white overflow-x-auto">
                        <x-claims.table :rows="[
                            ['label' => 'Current Status', 'value' => str_replace('_', ' ', $claim->status)],
                            ['label' => 'Submitted Date', 'value' => $claim->submitted_at->format('d-m-Y')],
                            ['label' => 'Claim Title', 'value' => $claim->title],
                            ['label' => 'Staff Name', 'value' => $claim->user->first_name . ' ' . $claim->user->second_name],
                            ['label' => 'Description', 'value' => $claim->description],
                            ['label' => 'Date From', 'value' => $claim->date_from->format('d-m-Y')],
                            ['label' => 'Date To', 'value' => $claim->date_to->format('d-m-Y')],
                        ]" />
                    </div>
                </div>

                <!-- Toll Details -->
                <h3 class="heading-2">Toll Details</h3>
                <div class="bg-white overflow-hidden rounded-lg border border-wgg-border">
                    <div class="bg-white overflow-x-auto">
                        <x-claims.table :rows="[
                            ['label' => 'Toll Amount', 'value' => 'RM' . $claim->toll_amount],
                            [
                                'label' => 'Toll Document',
                                'component' => 'claims.document-link',
                                'attributes' => [
                                    'url' => route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->first()->toll_file_name ?? 'no-file']),
                                ]
                            ],
                            [
                                'label' => 'Email Approval',
                                'component' => 'claims.document-link',
                                'attributes' => [
                                    'url' => route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->first()->email_file_name ?? 'no-file']),
                                ]
                            ],
                        ]" />
                    </div>
                </div>

                <!-- Trip Details -->
                <div class="space-y-4 md:space-y-6">
                    <h3 class="heading-2">Trip Details</h3>

                    <!-- Locations List -->
                    <div class="bg-white overflow-hidden rounded-lg border border-wgg-border">
                        <div class="bg-white overflow-x-auto">
                            <table class="min-w-full divide-y divide-wgg-border-200">
                                <thead>
                                    <tr>
                                        <th class="table-header">No.</th>
                                        <th class="table-header">From</th>
                                        <th class="table-header">To</th>
                                        <th class="table-header">Distance (km)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-wgg-border-200">
                                    @if ($claim->locations && $claim->locations->count() > 0)
                                        @foreach ($claim->locations->sortBy('order') as $location)
                                            <tr>
                                                <td class="table-item">{{ $location->order }}</td>
                                                <td class="table-item">
                                                    <div class="break-words">{{ $location->from_location }}</div>
                                                </td>
                                                <td class="table-item">
                                                    <div class="break-words">{{ $location->to_location }}</div>
                                                </td>
                                                <td class="table-item">{{ number_format($location->distance, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="table-item" colspan="4">
                                                No locations found. Contact System Administrator
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="w-full">
                        <div id="map" class="h-[300px] md:h-[500px] w-full rounded-lg border border-wgg-border shadow-sm">
                            <div id="route-info-panel" class="text-sm"></div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6">
                    @if ($claim->status == Claim::STATUS_SUBMITTED)
                        <form action="" method="POST">
                            @csrf
                            <button type="submit" class="btn py-4 btn-danger">
                                Cancel Claim
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="icon-small" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.647 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
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

    @php
        $locationData = $claim->locations
            ->sortBy('order')
            ->map(function($location) {
                return [
                    'from_location' => $location->from_location,
                    'to_location' => $location->to_location,
                    'order' => $location->order,
                    'distance' => $location->distance
                ];
            })
            ->values()
            ->toArray();
    @endphp

    <script>
        var claimLocations = @json($locationData);
        console.log('Claim locations:', claimLocations);
    </script>
    
    @vite('resources/js/review.js')

@endsection
