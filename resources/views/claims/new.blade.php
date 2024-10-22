<x-layout>
    @auth


    <main>

        <div class="flex flex-col gap-2">

            @php
            $existingClaim = null;
            if (request()->has('claim_id')) {
                $existingClaim = \App\Models\Claim::find(request()->claim_id);
            }
            @endphp

            <!-- Claims Form Container -->

            <h2 class="text-3xl font-bold text-gray-900 mb-6">
                {{ $existingClaim ? 'Editing or Re-Submit Claim' : 'New Claim' }}
            </h2>

            @if($existingClaim)
            <div class="max-w-full rounded-lg border border-wgg-border mb-10">
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-10">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claim Details</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Information</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Submitted At</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $existingClaim->submitted_at->format('d-m-Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Date From</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $existingClaim->date_from->format('d-m-Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Date To</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $existingClaim->date_to->format('d-m-Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Toll Amount</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $existingClaim->toll_amount }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Toll Document</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('claims.view.document', ['claim' => $existingClaim->id, 'type' => 'toll', 'filename' => $existingClaim->documents->where('toll_file_name', '!=', null)->first()->toll_file_name ?? 'no-file']) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                View Document
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Email Document</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('claims.view.document', ['claim' => $existingClaim->id, 'type' => 'email', 'filename' => $existingClaim->documents->where('email_file_name', '!=', null)->first()->email_file_name ?? 'no-file']) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                View Document
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @foreach($existingClaim->locations as $index => $location)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Location {{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $location->location }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @else
            <div class="max-w-full">
            @endif
                <form action="{{ route('claims.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    @if($existingClaim)
                        <input type="hidden" name="claim_id" value="{{ $existingClaim->id }}">
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-3 rounded-lg shadow-sm">

                        <!-- Left Side -->
                        <div class="p-14 border border-r-0 border-wgg-border col-span-1 flex flex-col gap-4 rounded-l-lg">

                            <div class="relative">
                                <input value="{{ old('date_from') }}" class="form-input text-wgg-black-950 @error('date_from') is-invalid @enderror w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out peer" type="date" name="date_from" id="date-from" placeholder=" " required onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'">
                                <label for="date-from" class="absolute left-2 top-2 text-xs text-wgg-black-400  font-normal transition-all duration-300 transform -translate-y-4 scale-75 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75">From</label>
                            </div>
                            @error('date_from')
                                <span class="error-text">{{ $message }}</span>
                            @enderror

                            <div class="relative">
                                <input value="{{ old('date_to') }}" class="form-input text-wgg-black-950 @error('date_to') is-invalid @enderror w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out peer" type="date" name="date_to" id="date-to" placeholder=" " required onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'">
                                <label for="date-to" class="absolute left-2 top-2 text-xs text-wgg-black-400  font-normal transition-all duration-300 transform -translate-y-4 scale-75 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75">To</label>
                            </div>
                            @error('date_to')
                                <span class="error-text">{{ $message }}</span>
                            @enderror

                            <div class="relative">
                                <input value="{{ old('toll_amount') }}" class="form-input text-wgg-black-950 @error('toll_amount') is-invalid @enderror w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out peer" type="number" name="toll_amount" id="toll_amount" step="0.01" required min="0" placeholder=" ">
                                <label for="toll_amount" class="absolute left-2 top-2 text-xs text-wgg-black-400  font-normal transition-all duration-300 transform -translate-y-4 scale-75 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75">Toll Amount</label>
                            </div>
                            @error('toll_amount')
                                <span class="error-text">{{ $message }}</span>
                            @enderror

                            <div class="grid grid-cols-2 space-x-2">
                                <div class="col-span-1 flex justify-center items-center py-4 w-full border border-dotted border-wgg-border rounded-lg">
                                    <input class="hidden @error('toll_report') is-invalid @enderror" type="file" name="toll_report" id="toll_report" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <label for="toll_report" class="text-xs text-wgg-black-400  font-normal">
                                        <span id="toll_file_label" class="cursor-pointer">Toll Report</span>
                                    </label>
                                    <!-- Progress Bar -->
                                    <div id="toll_progress_container" class="progress-container hidden">
                                        <div id="toll_progress_bar" class="progress-bar" style="width: 0%"></div>
                                    </div>
                                    @error('toll_report')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-span-1 flex justify-center items-center py-4 w-full border border-dotted border-wgg-border rounded-lg">
                                    <input class="hidden @error('email_report') is-invalid @enderror" type="file" name="email_report" id="email_report" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <label for="email_report" class="text-xs text-wgg-black-400  font-normal">
                                        <span id="email_file_label" class="cursor-pointer">Email Approval</span>
                                    </label>
                                    <!-- Progress Bar -->
                                    <div id="email_progress_container" class="progress-container hidden">
                                        <div id="email_progress_bar" class="progress-bar" style="width: 0%"></div>
                                    </div>
                                    @error('email_report')
                                        <span class="error-text">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="w-full py-2 px-2 bg-yellow-400 text-xs text-wgg-black-950 rounded-lg">
                                <span class="wgg-center-content gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-arrow-down-fill" viewBox="0 0 16 16">
                                        <path d="M8 2a5.53 5.53 0 0 0-3.594 1.342c-.766.66-1.321 1.52-1.464 2.383C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13h8.906C14.502 13 16 11.57 16 9.773c0-1.636-1.242-2.969-2.834-3.194C12.923 3.999 10.69 2 8 2m2.354 6.854-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 9.293V5.5a.5.5 0 0 1 1 0v3.793l1.146-1.147a.5.5 0 0 1 .708.708"/>
                                    </svg>
                                    <strong>Note:</strong> Maximum file upload size is 2MB
                                </span>
                            </div>


                            <div class="relative">
                                <textarea
                                    class="form-input text-wgg-black-950 @error('remarks') is-invalid @enderror w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out"
                                    name="remarks"
                                    id="remarks"
                                    cols="30"
                                    rows="5"
                                    placeholder=" "
                                >{{ old('remarks', $existingClaim ? $existingClaim->remarks : '') }}</textarea>

                                <label
                                    for="remarks"
                                    class="absolute text-sm text-wgg-black-400  font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3"
                                >
                                    Remarks
                                </label>
                            </div>
                            @error('remarks')
                                <span class="error-text">{{ $message }}</span>
                            @enderror

                            <div class="relative">
                                <select name="claim_company" id="claim_company" class="form-input text-wgg-black-950 @error('claim_company') is-invalid @enderror w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out appearance-none bg-white" required>
                                    <option value="">Select an Option</option>
                                    <option value="wge" {{ old('claim_company') == 'wge' ? 'selected' : '' }}>WGE</option>
                                    <option value="wgg" {{ old('claim_company') == 'wgg' ? 'selected' : '' }}>WGG</option>
                                    <option value="wgg & wge" {{ old('claim_company') == 'wgg & wge' ? 'selected' : '' }}>WGG & WGE</option>
                                </select>
                                <label for="claim_company" class="absolute text-sm text-wgg-black-400  font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">
                                    Claim Company
                                </label>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                    </svg>
                                </div>
                            </div>
                            @error('claim_company')
                                <span class="error-text">{{ $message }}</span>
                            @enderror

                            <div class="wgg-flex-col gap-2" id="location-input-container">
                                <div class="wgg-flex-col gap-2" id="location-1">
                                    <div class="relative">
                                        <input type="text" name="location[]" id="location-1" class="form-input location-input text-wgg-black-950 w-full px-4 py-2 pt-6 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out" placeholder=" " required>

                                        <label for="location-1" class="absolute text-sm text-wgg-black-400  font-normal duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3">
                                            Location 1
                                        </label>
                                    </div>
                                </div>
                                <!-- Hidden Total Distance Input -->
                                <input type="hidden" name="total_distance" id="total-distance-input">
                            </div>
                            @error('location.*')
                                <span class="error-text">{{ $message }}</span>
                            @enderror

                            <div class="flex flex-row gap-2">
                                <button id="add-location-btn" type="button" class="w-full py-2 px-5 border border-transparent rounded-md shadow-sm text-sm font-normal text-white bg-wgg-black-950 hover:bg-wgg-black-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill mr-2" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
                                    </svg>
                                    Add Location
                                </button>
                                <button type="button" id="remove-location-btn" class="py-3 px-5 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-300" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                    </svg>
                                </button>
                            </div>

                            <button type="submit" class="btn btn-success py-4">
                                {{ $existingClaim ? 'Update Claim' : 'Submit Claim' }}
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill mr-2" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Right Side -->
                        <div class="border border-wgg-border col-span-2 rounded-r-lg">
                            <div id="map" class="w-full h-full"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </main>
    @vite([
        'resources/js/form.js',
        ])
    @endauth

    @guest
        <script>window.location.href = "{{ route('login') }}";</script>
    @endguest



</x-layout>
