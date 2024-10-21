<x-layout>

    <div class="claim-review-container gap-8">

        {{-- Header Titles --}}
        <div class="wgg-flex-row gap-2 m-4 justify-between">
            <a class="btn-back" href="{{ route('claims.approval') }}">
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
                <span class="review-table-title">Basic Details</span>
                <table>
                    <tbody class="">
                        <tr class="review-dashboard-table-content">
                            <th>Submitted Date</th>
                            <td>{{ $claim->submitted_at->format('d-m-Y') }}</td>
                        </tr>
                        <tr class="review-dashboard-table-content">
                            <th>Claim Title</th>
                            <td>{{ $claim->title }}</td>
                        </tr>
                        <tr class="review-dashboard-table-content">
                            <th>Staff Name</th>
                            <td>{{ $claim->user->first_name . ' ' . $claim->user->second_name }}</td>
                        </tr>
                        <tr class="review-dashboard-table-content">
                            <th>Description</th>
                            <td>{{ $claim->description }}</td>
                        </tr>
                        <tr class="review-dashboard-table-content">
                            <th>Date From</th>
                            <td>{{ $claim->date_from->format('d-m-Y') }}</td>
                        </tr>
                       <tr class="review-dashboard-table-content border-0">
                            <th>Date To</th>
                            <td>{{ $claim->date_to->format('d-m-Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Toll Details --}}

            <div class="wgg-flex-col gap-2">
                <span class="review-table-title">Toll Details</span>
                <table class="text-left">
                    <tbody>
                        <tr class="review-dashboard-table-content">
                            <th>Toll Amount</th>
                            <td>{{ 'RM' . $claim->toll_amount }}</td>
                        </tr>
                        <tr class="review-dashboard-table-content border-0">
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
                        <tr class="review-dashboard-table-content border-0">
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
            <span class="review-table-title">Trip Details</span>
            <table class="text-left">
                <tbody>
                    @if ($claim->locations && $claim->locations->count() > 0)
                        @foreach ($claim->locations->sortBy('order') as $location)
                            <tr class="review-dashboard-table-content">
                                <th>Location {{ $location->order }}</th>
                                <td>{{ $location->location }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="review-dashboard-table-content">
                            <th>No locations found</th>
                            <td>Contact System Adminstrator</td>
                        </tr>
                    @endif
                    <tr class="review-dashboard-table-content border-0">
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
        <form action="{{ route('claims.update', $claim->id) }}" class="wgg-flex-col gap-4" method="POST">
            @csrf
            @method('PUT')
            <div class="wgg-flex-col gap-2">
                <label for="remarks" class="form-label-other">Remarks</label>
                <textarea class="form-input" name="remarks" id="remarks" cols="30" rows="5">{{ old('remarks') }}</textarea>
            </div>
            <div class="wgg-flex-row gap-2">
                <button type="submit" name="action" value="approve" class="btn-green wgg-center-content w-fit text-base px-6">
                    Approve
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                </button>
                <button type="submit" name="action" value="reject" class="btn-danger wgg-center-content w-fit text-base px-6">
                    Reject
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                    </svg>
                </button>
            </div>
            @error('remarks')
            <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </form>

    </div>

    <script>
        var claimLocations = @json($claim->locations);
    </script>
    @vite('resources/js/review.js')

</x-layout>