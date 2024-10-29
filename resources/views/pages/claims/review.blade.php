@extends('layouts.app')

@php
use App\Models\Claim;
@endphp

@section('content')
    <div class="w-full">
        <div class="bg-white rounded-lg overflow-hidden">
            <div class="space-y-8">

                <!-- Header Section -->
                <div class="flex-between items-center mb-6">
                    <a href="{{ route('claims.dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center font-medium text-sm transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill mr-2" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                        </svg>
                        Back
                    </a>
                    <h2 class="text-md font-semibold text-wgg-black-300">Reviewing Claim - {{ $claim->id }}</h2>
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
                                    'url' => route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']),
                                ]
                            ],
                            [
                                'label' => 'Email Approval',
                                'component' => 'claims.document-link',
                                'attributes' => [
                                    'url' => route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']),
                                ]
                            ],
                        ]" />
                    </div>
                </div>

                <!-- Trip Details -->
                <h3 class="heading-2">Trip Details</h3>
                <div class="space-y-4 md:space-y-6">
                    <!-- Locations List -->
                    <div class="bg-white overflow-hidden rounded-lg border border-wgg-border">
                        <div class="bg-white overflow-x-auto">
                            <table class="min-w-full divide-y divide-wgg-border-200">
                                <thead>
                                    <tr>
                                        <th class="table-header">No.</th>
                                        <th class="table-header">Address</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-wgg-border-200">
                                    @if ($claim->locations && $claim->locations->count() > 0)
                                        @foreach ($claim->locations->sortBy('order') as $location)
                                            <tr>
                                                <td class="table-item">{{ $location->order }}</td>
                                                <td class="table-item">
                                                    <div class="break-words">{{ $location->location }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="table-item" colspan="2">
                                                No locations found. Contact System Administrator
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Map -->
                <div class="space-y-4 md:space-y-6 w-full">
                    <h3 class="heading-2">Map Details</h3>
                    <div id="map" class="h-[300px] md:h-[500px] w-full rounded-lg border border-wgg-border shadow-sm">
                        <div id="route-info-panel" class="text-sm"></div>
                    </div>
                </div>

                <!-- Remarks -->
                <form action="{{ route('claims.update', $claim->id) }}" class="flex flex-col w-full gap-4" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-col space-y-4 md:space-y-6 w-full">
                        <h3 class="heading-2">Remarks</h3>
                        <textarea class="form-input" name="remarks" id="remarks" cols="30" rows="5">{{ old('remarks') }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <x-claims.action-button type="submit" name="action" value="approve" class="col-span-1 bg-green-400 hover:bg-green-600">
                            Approve
                        <x-icons.check-circle-fill />
                        </x-claims.action-button>
                        <x-claims.action-button type="submit" name="action" value="reject" class="col-span-1 bg-red-400 hover:bg-red-600">
                            Reject
                            <x-icons.x-circle-fill />
                        </x-claims.action-button>
                    </div>
                    @error('remarks')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </form>

            </div>
        </div>
    </div>

    <script>
        var claimLocations = @json($claim->locations);
    </script>
    @vite('resources/js/review.js')
@endsection
