@php
use App\Models\Claim;
@endphp

<x-layout>

    <div class="claim-indv-container gap-8">

        {{-- Header Titles --}}
        <div class="wgg-flex-row gap-2 m-4 justify-between">
            <a class="btn-back" href="{{ route('claims.dashboard') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                </svg>
                Back
            </a>
            <span class="text-2xl font-semibold text-wgg-black-200">Currently Viewing Claim ID {{ $claim->id }}</span>

        </div>

        {{-- Basic Details --}}

        <div class="wgg-flex-col gap-4">
            <div class="wgg-flex-col gap-2">
                <span class="claim-indv-table-title">Basic Details</span>
                <table>
                    <tbody class="">
                        <tr class="claim-indv-dashboard-table-content">
                            <th>Current Status</th>
                            <td>
                                <span class="claims-dashboard-status-badge
                                @if ($claim->status == Claim::STATUS_SUBMITTED)
                                    bg-orange-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-1-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M9.283 4.002H7.971L6.072 5.385v1.271l1.834-1.318h.065V12h1.312z"/>
                                    </svg>
                                    Submitted
                                @elseif ($claim->status == Claim::STATUS_APPROVED_ADMIN)
                                    bg-yellow-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-2-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M6.646 6.24c0-.691.493-1.306 1.336-1.306.756 0 1.313.492 1.313 1.236 0 .697-.469 1.23-.902 1.705l-2.971 3.293V12h5.344v-1.107H7.268v-.077l1.974-2.22.096-.107c.688-.763 1.287-1.428 1.287-2.43 0-1.266-1.031-2.215-2.613-2.215-1.758 0-2.637 1.19-2.637 2.402v.065h1.271v-.07Z"/>
                                    </svg>
                                    Admin Approved
                                @elseif ($claim->status == Claim::STATUS_APPROVED_DATUK)
                                    bg-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-3-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-8.082.414c.92 0 1.535.54 1.541 1.318.012.791-.615 1.36-1.588 1.354-.861-.006-1.482-.469-1.54-1.066H5.104c.047 1.177 1.05 2.144 2.754 2.144 1.653 0 2.954-.937 2.93-2.396-.023-1.278-1.031-1.846-1.734-1.916v-.07c.597-.1 1.505-.739 1.482-1.876-.03-1.177-1.043-2.074-2.637-2.062-1.675.006-2.59.984-2.625 2.12h1.248c.036-.556.557-1.054 1.348-1.054.785 0 1.348.486 1.348 1.195.006.715-.563 1.237-1.342 1.237h-.838v1.072h.879Z"/>
                                    </svg>
                                    Datuk Approved
                                @elseif ($claim->status == Claim::STATUS_APPROVED_HR)
                                    bg-purple-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-4-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0ZM7.519 5.057c-.886 1.418-1.772 2.838-2.542 4.265v1.12H8.85V12h1.26v-1.559h1.007V9.334H10.11V4.002H8.176c-.218.352-.438.703-.657 1.055ZM6.225 9.281v.053H8.85V5.063h-.065c-.867 1.33-1.787 2.806-2.56 4.218Z"/>
                                    </svg>
                                    HR Approved
                                @elseif ($claim->status == Claim::STATUS_APPROVED_FINANCE)
                                    bg-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-5-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0Zm-8.006 4.158c1.74 0 2.924-1.119 2.924-2.806 0-1.641-1.178-2.584-2.56-2.584-.897 0-1.442.421-1.612.68h-.064l.193-2.344h3.621V4.002H5.791L5.445 8.63h1.149c.193-.358.668-.809 1.435-.809.85 0 1.582.604 1.582 1.57 0 1.085-.779 1.682-1.57 1.682-.697 0-1.389-.31-1.53-1.031H5.276c.065 1.213 1.149 2.115 2.72 2.115Z"/>
                                    </svg>
                                    Finance Approved
                                @elseif ($claim->status == Claim::STATUS_REJECTED)
                                    bg-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                      </svg>
                                    Rejected
                                @elseif ($claim->status == Claim::STATUS_DONE)
                                    bg-green-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    </svg>
                                    Payment
                                @endif
                                </span>
                            </td>
                        </tr>
                        <tr class="claim-indv-dashboard-table-content">
                            <th>Submitted Date</th>
                            <td>{{ $claim->submitted_at->format('d-m-Y') }}</td>
                        </tr>
                        <tr class="claim-indv-dashboard-table-content">
                            <th>Claim Title</th>
                            <td>{{ $claim->title }}</td>
                        </tr>
                        <tr class="claim-indv-dashboard-table-content">
                            <th>Staff Name</th>
                            <td>{{ $claim->user->first_name . ' ' . $claim->user->second_name }}</td>
                        </tr>
                        <tr class="claim-indv-dashboard-table-content">
                            <th>Description</th>
                            <td>{{ $claim->description }}</td>
                        </tr>
                        <tr class="claim-indv-dashboard-table-content">
                            <th>Date From</th>
                            <td>{{ $claim->date_from->format('d-m-Y') }}</td>
                        </tr>
                       <tr class="claim-indv-dashboard-table-content border-0">
                            <th>Date To</th>
                            <td>{{ $claim->date_to->format('d-m-Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Toll Details --}}

            <div class="wgg-flex-col gap-2">
                <span class="claim-indv-table-title">Toll Details</span>
                <table class="text-left">
                    <tbody>
                        <tr class="claim-indv-dashboard-table-content">
                            <th>Toll Amount</th>
                            <td>{{ 'RM' . $claim->toll_amount }}</td>
                        </tr>
                        <tr class="claim-indv-dashboard-table-content border-0">
                            <th>Toll Document</th>
                            <td>
                                <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'toll', 'filename' => $claim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']) }}" target="_blank" class="btn-view-doc">
                                View Toll Document
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                                    <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                                </svg>
                                </a>
                            </td>
                        </tr>
                        <tr class="claim-indv-dashboard-table-content border-0">
                            <th>Email Approval</th>
                            <td>
                                <a href="{{ route('claims.view.document', ['claim' => $claim->id, 'type' => 'email', 'filename' => $claim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']) }}" target="_blank" class="btn-view-doc">
                                View Email Document
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                                    <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                                </svg>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        {{-- Trip Details --}}

        <div class="wgg-flex-col gap-4">
            <span class="claim-indv-table-title">Trip Details</span>
            <table class="text-left">
                <tbody>
                    @if ($claim->locations && $claim->locations->count() > 0)
                        @foreach ($claim->locations->sortBy('order') as $location)
                            <tr class="claim-indv-dashboard-table-content">
                                <th>Location {{ $location->order }}</th>
                                <td>{{ $location->location }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="claim-indv-dashboard-table-content">
                            <th>No locations found</th>
                            <td>Contact System Adminstrator</td>
                        </tr>
                    @endif
                    <tr class="claim-indv-dashboard-table-content border-0">
                        <th>Total Distance</th>
                        <td>{{ $claim->total_distance . ' KM' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="map" style="height: 500px; width: 100%">
            <div id="route-info-panel"></div>
        </div>

        <!-- Remarks -->

            @if ($claim->status == Claim::STATUS_SUBMITTED)
                <form action="" class="wgg-flex-col gap-4">
                    <button type="submit" class="btn-danger wgg-center-content w-fit text-base px-6">
                        <span>Cancel Claim</span>
                    </button>
                </form>
            @elseif ($claim->status == Claim::STATUS_REJECTED)
                <a href="{{ route('claims.new', ['claim_id' => $claim->id]) }}" type="submit" class="btn-green wgg-center-content w-fit text-base px-6">
                    <span>Re-Submit Claim</span>
                </a>
            @endif


    </div>

    <script>
        var claimLocations = @json($claim->locations);
    </script>
    @vite('resources/js/review.js')

</x-layout>
