<x-layout>
    @auth


    <main class="main *:font-wgg gap-10">

    <div class="wgg-flex-col gap-2">

        @php
        $existingClaim = null;
        if (request()->has('claim_id')) {
            $existingClaim = \App\Models\Claim::find(request()->claim_id);
        }
        @endphp

        <!-- Claims Form Container -->

        @if($existingClaim)
        <div class="wgg-flex-col gap-10">

            @if($existingClaim)

            <div class="wgg-box-border-shadow">

                <!-- Rejected Claim Data -->
                <div class="flex flex-col px-4">
                    <h1 class="text-2xl text-wgg-black-950 font-semibold">
                        {{ $existingClaim ? 'Editing or Re-Submit Claim' . ' - ' . $existingClaim->id : 'New Claim' }}
                    </h1>
                    <span class="text-red-500">Refer this table to view your old claim data.</span>
                </div>

                <div class="flex-col flex gap-4">
                    <table class="table-auto">
                        <tr class="claims-dashboard-table-header">

                            <th>Submitted At</th>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>Toll Amount</th>
                            <th>Toll Document</th>
                            <th>Email Document</th>
                        </tr>

                        <tr class="claims-dashboard-table-row">
                            <th>{{ $existingClaim->submitted_at->format('d-m-Y') }}</th>
                            <th>{{ $existingClaim->date_to->format('d-m-Y') }}</th>
                            <th>{{ $existingClaim->date_from->format('d-m-Y') }}</th>
                            <th>{{ $existingClaim->toll_amount }}</th>
                            <th>
                                <a href="{{ route('claims.view.document', ['claim' => $existingClaim->id, 'type' => 'toll', 'filename' => $existingClaim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']) }}" target="_blank" class="btn-view-doc">
                                View Document
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                                    <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                                </svg>
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('claims.view.document', ['claim' => $existingClaim->id, 'type' => 'email', 'filename' => $existingClaim->documents->where('email_file_name', '!=', null)->first()->email_file_name ?? 'no-file']) }}" target="_blank" class="btn-view-doc">
                                View Document
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" class="bi bi-box-arrow-up-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
                                    <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
                                </svg>
                                </a>
                            </th>
                        </tr>
                    </table>
                </div>

            </div>

            <div class="wgg-box-border-shadow">


                <div class="wgg-flex-col gap-4">
                    <div class="wgg-flex-col gap-2">
                        <h1 class="claim-indv-table-title">
                            Old Location Details<br>
                            <span class="text-red-500 text-base font-normal">Refer this table to view your old claim data.</span>
                        </h1>

                        <table>
                            <tbody class="">
                                @foreach($existingClaim->locations as $index => $location)
                                <tr class="claim-indv-dashboard-table-content border-b-0">
                                    <th>Location {{ $index + 1 }}</th>
                                    <td>{{ $location->location }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            @endif

        @else
        <div>
        @endif


            <form class="wgg-flex-col gap-6" action="{{ route('claims.new') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($existingClaim)
                    <input type="hidden" name="claim_id" value="{{ $existingClaim->id }}">
                @endif

                <!-- Date & Map Container -->
                <div class="form-container-1 shadow-md">

                    <!-- Left Side -->
                    <div class="wgg-flex-col col-span-1 gap-2">

                        <!-- Date Range -->
                        <div class="wgg-flex-col gap-2">

                            <div class="wgg-flex-col gap-2 w-full">
                                <label class="form-label" for="date-from">From</label>
                                <input value="" class="form-input text-wgg-black-950" type="date" name="date_from" id="date-from">
                            </div>

                            <div class="wgg-flex-col gap-2 w-full">
                                <label class="form-label" for="date-to">To</label>
                                <input value="" class="form-input text-wgg-black-950" type="date" name="date_to" id="date-to">
                            </div>

                        </div>

                        <!-- Date Range Error -->
                        <div>
                            @error('date_to')
                            <span class="error-text">*{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Toll Section -->
                        <div class="wgg-flex-col gap-2">
                            <!-- Toll Amount -->
                            <div class="wgg-flex-col gap-2">
                                <label for="toll_amount" class="form-label">Toll Amount</label>
                                <input value="" class="form-input" type="number" name="toll_amount" id="toll_amount" step="0.01">
                            </div>
                        </div>

                        <!-- Toll Amount Error -->
                        <div>
                            @error('toll_amount')
                                <span class="error-text">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Attachments -->
                        <div class="wgg-flex-col gap-2">
                            <!-- Toll Report Attachment -->
                            <div class="file-input-container basis-1/2">
                                <input class="hidden" type="file" name="toll_report" id="toll_report">
                                <label for="toll_report" class="form-label">
                                    <span id="toll_file_label">Toll Report</span>
                                </label>
                                <!-- Progress Bar -->
                                <div id="toll_progress_container" class="progress-container hidden">
                                    <div id="toll_progress_bar" class="progress-bar" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- Email Report Attachment -->
                            <div class="file-input-container basis-1/2">
                                <input class="hidden" type="file" name="email_report" id="email_report">
                                <label for="email_report" class="form-label">
                                    <span id="email_file_label">Email Approval</span>
                                </label>
                                <!-- Progress Bar -->
                                <div id="email_progress_container" class="progress-container hidden">
                                    <div id="email_progress_bar" class="progress-bar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="wgg-flex-col gap-2">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-input" name="remarks" id="remarks" cols="30" rows="5">{{ old('remarks', $existingClaim ? $existingClaim->remarks : '') }}</textarea>
                        </div>

                        <!-- Options 1. WGE 2. WGG 3. WGG & WGE -->
                        <div class="wgg-flex-col gap-2">
                            <label for="claim_company" class="form-label">Options</label>
                            <select name="claim_company" id="claim_company" class="form-input">
                                <option value="wge">WGE</option>
                                <option value="wgg">WGG</option>
                                <option value="wgg & wge">WGG & WGE</option>
                            </select>
                        </div>


                        <!-- Location Input Container -->
                        <div class="wgg-flex-col gap-2" id="location-input-container">
                            <div class="info-box">
                                <span class="wgg-center-content gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hand-index-thumb-fill" viewBox="0 0 16 16">
                                        <path d="M8.5 1.75v2.716l.047-.002c.312-.012.742-.016 1.051.046.28.056.543.18.738.288.273.152.456.385.56.642l.132-.012c.312-.024.794-.038 1.158.108.37.148.689.487.88.716q.113.137.195.248h.582a2 2 0 0 1 1.99 2.199l-.272 2.715a3.5 3.5 0 0 1-.444 1.389l-1.395 2.441A1.5 1.5 0 0 1 12.42 16H6.118a1.5 1.5 0 0 1-1.342-.83l-1.215-2.43L1.07 8.589a1.517 1.517 0 0 1 2.373-1.852L5 8.293V1.75a1.75 1.75 0 0 1 3.5 0"/>
                                    </svg>
                                    <strong>Change Order -</strong> Drag the Location</span>
                            </div>

                            <!-- Location 1 -->
                            <div class="wgg-flex-col gap-2" id="location-1">
                                <label for="location-1" class="form-label cursor-grab">Location 1</label>
                                <input type="text" name="location[]" id="location-1" class="form-input location-input" placeholder="">
                            </div>

                            <!-- Hidden Total Distance Input -->
                            <input type="hidden" name="total_distance" id="total-distance-input">


                        </div>

                        <!-- Add Location Button -->
                        <div class="wgg-flex-row gap-2">
                            <a id="add-location-btn" class="btn-blue wgg-center-content w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
                                </svg>
                                Add Location
                            </a>
                            <button type="button" id="remove-location-btn" class="btn-danger w-fit disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-300" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                    <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                </svg>
                            </button>
                        </div>

                    </div>

                    <!-- Right Side -->
                    <div class="wgg-flex-col col-span-2 gap-2">
                        <div id="map" class="wgg-flex-col gap-2"></div>
                        <button type="submit" class="btn-green wgg-center-content w-fit">
                            {{ $existingClaim ? 'Update Claim' : 'Submit Claim' }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-check-fill" viewBox="0 0 16 16">
                                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 1.59 2.498C8 14 8 13 8 12.5a4.5 4.5 0 0 1 5.026-4.47zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
                                <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    </main>
    @section('new-claim-scripts')
    @vite([
        'resources/js/form.js',
        ])
    @endsection
    @endauth

    @guest
        <script>window.location.href = "{{ route('login') }}";</script>
    @endguest



</x-layout>
