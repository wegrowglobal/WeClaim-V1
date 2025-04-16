@extends('layouts.app')

@section('content')
<!-- Add CSRF Token meta tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Use the new page header component --}}
    <x-layout.page-header 
        title="Profile Settings" 
        subtitle="Manage your account information and preferences.">
        {{-- No actions needed on the right for this page, so the slot is empty --}}
    </x-layout.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif
    
    @if($errors->has('error'))
         <div class="mb-6 rounded-md bg-red-50 p-4">
             <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-red-800">{{ $errors->first('error') }}</p>
                </div>
              </div>
        </div>
    @endif

    <!-- Profile Form -->
    <form action="{{ route('profile.update') }}" 
          method="POST" 
          enctype="multipart/form-data"
          id="profileForm">
        @csrf
        @method('PUT')
        
        <div class="space-y-12"> {{-- Main spacing for sections --}}
            <!-- Hidden field for signature -->
            <input type="hidden" name="signature_path" value="{{ auth()->user()->signature_path }}" id="signaturePath">
            
            <!-- Profile Photo Section -->
            <div class="border-b border-gray-900/10 pb-12">
                <h2 class="text-base font-semibold leading-7 text-gray-900">Profile Photo</h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">Update your profile picture.</p>
                <div class="mt-6 flex flex-col sm:flex-row items-center gap-x-6">
                     <div class="profile-picture relative h-24 w-24 rounded-full ring-1 ring-gray-300 bg-gray-100 cursor-pointer hover:ring-black transition-all">
                        <x-profile.profile-picture :user="auth()->user()" size="lg" /> {{-- Adjusted size --}}
                        <label class="absolute -bottom-1 -right-1 p-1.5 rounded-full bg-white shadow-sm cursor-pointer hover:bg-gray-100 transition-colors ring-1 ring-gray-300">
                            <input id="profile_picture_input" type="file" name="profile_picture" class="hidden" accept="image/*">
                            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/></svg>
                        </label>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-gray-600 sm:mt-0">JPG, PNG or GIF. Max size of 2MB.</p>
                </div>
                 @error('profile_picture')
                     <div class="mt-2 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>
                 @enderror
            </div>

            <!-- Personal Information Section -->
            <div class="border-b border-gray-900/10 pb-12">
                <h2 class="text-base font-semibold leading-7 text-gray-900">Personal Information</h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">Use a permanent address where you can receive mail.</p>
                <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    {{-- First Name --}}
                    <div class="sm:col-span-3">
                        <label for="first_name" class="block text-sm font-medium leading-6 text-gray-900">First Name</label>
                        <div class="mt-2">
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', auth()->user()->first_name) }}" required 
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('first_name') ring-red-500 @enderror">
                        </div>
                         @error('first_name')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                    {{-- Second Name --}}
                    <div class="sm:col-span-3">
                        <label for="second_name" class="block text-sm font-medium leading-6 text-gray-900">Second Name</label>
                        <div class="mt-2">
                            <input type="text" id="second_name" name="second_name" value="{{ old('second_name', auth()->user()->second_name) }}" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('second_name') ring-red-500 @enderror">
                        </div>
                         @error('second_name')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                    {{-- Address --}}
                    <div class="col-span-full">
                        <label for="address" class="block text-sm font-medium leading-6 text-gray-900">Street address</label>
                        <div class="mt-2">
                             <input type="text" id="address" name="address" value="{{ old('address', auth()->user()->address) }}" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('address') ring-red-500 @enderror">
                        </div>
                         @error('address')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                </div>
                {{-- New grid specifically for the 4 inline address fields --}}
                <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-4">
                    {{-- State --}}
                     <div class="sm:col-span-1"> {{-- Set to 1 column --}}
                        <label for="state" class="block text-sm font-medium leading-6 text-gray-900">State / Territory</label>
                        <div class="mt-2">
                            <select id="state" name="state" required class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('state') ring-red-500 @enderror">
                                <option value="">Select State</option>
                                @foreach($stateOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('state', auth()->user()->state) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                         @error('state')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                    {{-- City --}}
                    <div class="sm:col-span-1"> {{-- Set to 1 column --}}
                        <label for="city" class="block text-sm font-medium leading-6 text-gray-900">City</label>
                        <div class="mt-2">
                             <select id="city" name="city" required class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('city') ring-red-500 @enderror">
                                <option value="">Select State First</option> 
                            </select>
                        </div>
                         @error('city')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                    {{-- ZIP Code --}}
                    <div class="sm:col-span-1"> {{-- Set to 1 column --}}
                        <label for="zip_code" class="block text-sm font-medium leading-6 text-gray-900">ZIP / Postal code</label>
                        <div class="mt-2">
                            <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code', auth()->user()->zip_code) }}" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('zip_code') ring-red-500 @enderror">
                        </div>
                        @error('zip_code')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                    {{-- Country --}}
                     <div class="sm:col-span-1"> {{-- Set to 1 column --}}
                        <label for="country" class="block text-sm font-medium leading-6 text-gray-900">Country</label>
                        <div class="mt-2">
                             <input type="text" id="country" name="country" value="Malaysia" readonly required
                                   class="block w-full rounded-md border-0 bg-gray-100 py-2.5 px-3 text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none sm:text-sm sm:leading-6 @error('country') ring-red-500 @enderror">
                        </div>
                         @error('country')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="border-b border-gray-900/10 pb-12">
                <h2 class="text-base font-semibold leading-7 text-gray-900">Contact Information</h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">How can we reach you?</p>
                 <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                        <div class="mt-2">
                            <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('email') ring-red-500 @enderror">
                        </div>
                         @error('email')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                    <div class="sm:col-span-3">
                        <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Phone Number</label>
                        <div class="mt-2">
                             <input type="tel" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('phone') ring-red-500 @enderror">
                        </div>
                        @error('phone')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                </div>
            </div>

            <!-- Banking Information Section -->
             <div class="border-b border-gray-900/10 pb-12">
                <h2 class="text-base font-semibold leading-7 text-gray-900">Banking Information</h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">Your bank details for claim reimbursements.</p>
                <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="bank_name" class="block text-sm font-medium leading-6 text-gray-900">Bank Name</label>
                        <div class="mt-2">
                             <select id="bank_name" name="bank_name" required class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-black sm:max-w-xs sm:text-sm sm:leading-6 @error('bank_name') ring-red-500 @enderror">
                                <option value="">Select a Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank }}" {{ old('bank_name', optional(auth()->user()->bankingInformation)->bank_name) === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                         @error('bank_name')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                    <div class="sm:col-span-3">
                        <label for="account_holder_name" class="block text-sm font-medium leading-6 text-gray-900">Account Holder Name</label>
                        <div class="mt-2">
                            <input type="text" id="account_holder_name" name="account_holder_name" value="{{ old('account_holder_name', optional(auth()->user()->bankingInformation)->account_holder_name) }}" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('account_holder_name') ring-red-500 @enderror">
                        </div>
                        @error('account_holder_name')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                     <div class="sm:col-span-full">
                        <label for="account_number" class="block text-sm font-medium leading-6 text-gray-900">Account Number</label>
                        <div class="mt-2">
                           <input type="text" id="account_number" name="account_number" value="{{ old('account_number', optional(auth()->user()->bankingInformation)->account_number) }}" required
                                   class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-black sm:text-sm sm:leading-6 @error('account_number') ring-red-500 @enderror">
                        </div>
                         @error('account_number')<div class="mt-1 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>@enderror
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="border-b border-gray-900/10 pb-12">
                <h2 class="text-base font-semibold leading-7 text-gray-900">Digital Signature</h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">Draw your signature for claim approvals and documents.</p>
                 <div class="mt-6 border border-gray-200 rounded-md p-4">
                     <livewire:user-signature />
                 </div>
                 @error('signature_path')
                    <div class="mt-2 flex items-center text-sm text-red-600"><svg class="mr-1 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">{{ $message }}</span></div>
                @enderror
            </div>
            
        </div> {{-- End main spacing div --}}

        <!-- Action Buttons -->
        <div class="mt-8 flex items-center justify-end gap-x-6 pt-6 border-t border-gray-900/10">
            <button type="button" 
                    onclick="Profile.showChangePasswordModal()"
                    class="inline-flex items-center justify-center rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                Change Password
            </button>
            <button type="submit" 
                    class="inline-flex items-center justify-center rounded-md bg-black px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                Save Changes
            </button>
        </div>
    </form>
</div>

@vite(['resources/js/user/profile.js'])

{{-- Keep existing script block for JS functions and event listeners --}}
<script>
// ... existing functions ...

// Dynamic city dropdown logic (keep as is from previous step)
document.addEventListener('DOMContentLoaded', function() {
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('city');
    // Ensure citiesByState is correctly parsed as a JS object
    // Using html_entity_decode and json_encode to handle potential special characters safely
    const citiesByState = JSON.parse('{!! html_entity_decode(json_encode($citiesByState)) !!}');
    const initialUserState = stateSelect.value; // Get current selected state on load
    const initialUserCity = "{{ old('city', optional(auth()->user())->city) }}"; // Use optional() for safety

    function populateCities(selectedState) {
        // Clear current options
        citySelect.innerHTML = ''; 

        // Add a default placeholder option
        const placeholder = document.createElement('option');
        placeholder.value = '';
        // Check if selectedState exists and has cities before setting placeholder text
        placeholder.textContent = selectedState && citiesByState[selectedState]?.length > 0 ? 'Select City' : 'Select State First';
        placeholder.disabled = !selectedState; // Disable if no state is selected
        citySelect.appendChild(placeholder);

        // Populate with new cities if state is selected and has cities
        if (selectedState && citiesByState[selectedState]) {
            citiesByState[selectedState].forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        } 
        
        // If a city was previously selected for this state, re-select it
        // Ensure the state matches the initial state before trying to select the city
        if (selectedState === initialUserState && initialUserCity && citiesByState[selectedState]?.includes(initialUserCity)) {
             citySelect.value = initialUserCity;
        } else {
            // If state changed or initial city not valid for this state, ensure placeholder is selected
             citySelect.value = ''; 
        }
    }

    // Initial population on page load
    populateCities(initialUserState);

    // Update cities when state changes
    stateSelect.addEventListener('change', function() {
        populateCities(this.value);
    });

    // Ensure Profile class is initialized if it exists in profile.js
    if (typeof Profile === 'function') {
         window.profileInstance = new Profile();
    }
});

// Add event listener for signature updates
window.addEventListener('signature-updated', (event) => {
    const signaturePathInput = document.getElementById('signaturePath');
    if (signaturePathInput) {
        const newPath = event.detail.signature_path || '';
        signaturePathInput.value = newPath;
        localStorage.setItem('lastSignaturePath', newPath);
    } else {
        console.error('Signature path input not found');
    }
});

// Add form submission handler to include potentially updated signature
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const signaturePathInput = document.getElementById('signaturePath');
    const lastSignaturePath = localStorage.getItem('lastSignaturePath');
    
    // Update input from localStorage only if necessary before submission
    if (lastSignaturePath && signaturePathInput && signaturePathInput.value !== lastSignaturePath) {
         // Check if the value in the input is still the original or empty
         const originalPath = "{{ optional(auth()->user())->signature_path }}";
         if (!signaturePathInput.value || signaturePathInput.value === originalPath) {
             signaturePathInput.value = lastSignaturePath;
         }
    }
    // Allow default form submission to proceed
});

</script>
@endsection

@push('scripts')
{{-- Keep existing pushed scripts if any --}}
{{-- Example: 
<script>
    // Potentially initialize other JS libraries or components
</script>
--}}
@endpush