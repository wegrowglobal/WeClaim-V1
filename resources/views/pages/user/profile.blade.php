@extends('layouts.app')

@section('content')
<!-- Add CSRF Token meta tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 animate-slide-in">
        <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
        <p class="mt-2 text-sm text-gray-500">Manage your account information and preferences</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-600 animate-slide-in">
            {{ session('success') }}
        </div>
    @endif

    <!-- Profile Form -->
    <div class="space-y-6 animate-slide-in delay-100">
        <form action="{{ route('profile.update') }}" 
              method="POST" 
              enctype="multipart/form-data"
              class="space-y-6"
              id="profileForm">
            @csrf
            @method('PUT')
            
            <!-- Hidden field for signature -->
            <input type="hidden" name="signature_path" value="{{ auth()->user()->signature_path }}" id="signaturePath">
            
            <!-- Photo Upload Card -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Profile Photo</p>
                            <p class="text-xs text-gray-500">Upload a new profile picture</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                        <div class="profile-picture relative h-24 w-24 rounded-full ring-1 ring-gray-200/50 bg-gray-50 shadow-sm cursor-pointer hover:ring-indigo-400 transition-all">
                            <x-profile.profile-picture :user="auth()->user()" size="lg" />
                            <label class="absolute -bottom-1 -right-1 p-2 rounded-full bg-white shadow-sm cursor-pointer hover:bg-gray-50 transition-colors ring-1 ring-gray-200/50">
                                <input type="file" name="profile_picture" class="hidden" accept="image/*">
                                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </label>
                        </div>
                        <div class="text-center sm:text-left">
                            <h2 class="text-base font-medium text-gray-900">Profile Photo</h2>
                            <p class="text-sm text-gray-500 mt-1">JPG, PNG or GIF (max. 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information Card -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Personal Information</p>
                            <p class="text-xs text-gray-500">Your personal details</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="first_name">First Name</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="{{ old('first_name', auth()->user()->first_name) }}"
                                   class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                   required>
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="second_name">Second Name</label>
                            <input type="text" 
                                   id="second_name" 
                                   name="second_name" 
                                   value="{{ old('second_name', auth()->user()->second_name) }}"
                                   class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                   required>
                            @error('second_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address Fields -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="address">Address</label>
                            <input type="text" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address', auth()->user()->address) }}"
                                   class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                   required>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="city">City</label>
                            <input type="text" 
                                   id="city" 
                                   name="city" 
                                   value="{{ old('city', auth()->user()->city) }}"
                                   class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                   required>
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="state">State</label>
                            <select id="state" 
                                    name="state" 
                                    class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                    required>
                                <option value="">Select State</option>
                                @foreach($stateOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('state', auth()->user()->state) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('state')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="zip_code">Postal Code</label>
                            <input type="text" 
                                   id="zip_code" 
                                   name="zip_code" 
                                   value="{{ old('zip_code', auth()->user()->zip_code) }}"
                                   class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                   required>
                            @error('zip_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="country">Country</label>
                            <input type="text" 
                                   id="country" 
                                   name="country" 
                                   value="{{ old('country', auth()->user()->country) }}"
                                   class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                   required>
                            @error('country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Card -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Contact Information</p>
                            <p class="text-xs text-gray-500">Your contact details</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email Address</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', auth()->user()->email) }}"
                                   class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="phone">Phone Number</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', auth()->user()->phone) }}"
                                   class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                   required>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banking Information Card -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Banking Information</p>
                            <p class="text-xs text-gray-500">Your bank account details for reimbursements</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="bank_name">Bank Name</label>
                        <select id="bank_name" 
                                name="bank_name" 
                                class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                                required>
                            <option value="">Select a Bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank }}" {{ old('bank_name', auth()->user()->bankingInformation?->bank_name) === $bank ? 'selected' : '' }}>
                                    {{ $bank }}
                                </option>
                            @endforeach
                        </select>
                        @error('bank_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="account_holder">Account Holder Name</label>
                        <input type="text" 
                               id="account_holder" 
                               name="account_holder" 
                               value="{{ old('account_holder', auth()->user()->bankingInformation?->account_holder) }}"
                               class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                               required>
                        @error('account_holder')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="account_number">Account Number</label>
                        <input type="text" 
                               id="account_number" 
                               name="account_number" 
                               value="{{ old('account_number', auth()->user()->bankingInformation?->account_number) }}"
                               class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600"
                               required>
                        @error('account_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Digital Signature</p>
                            <p class="text-xs text-gray-500">Your signature for claim approvals and documents</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="w-full">
                        <livewire:user-signature />
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:justify-between gap-4 mt-6">
                <button type="button" 
                        onclick="Profile.showChangePasswordModal()"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Change Password
                </button>

                <button type="submit" 
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@vite(['resources/js/maps/profile-map.js', 'resources/js/profile.js'])

<script>
function toggleVisibility(inputId) {
    const input = document.getElementById(inputId);
    const showIcon = document.getElementById(`show${inputId.charAt(0).toUpperCase() + inputId.slice(1)}Icon`);
    const hideIcon = document.getElementById(`hide${inputId.charAt(0).toUpperCase() + inputId.slice(1)}Icon`);

    if (input.type === 'password') {
        input.type = 'text';
        showIcon.classList.add('hidden');
        hideIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        showIcon.classList.remove('hidden');
        hideIcon.classList.add('hidden');
    }
}

// Add event listener for signature updates
window.addEventListener('signature-updated', (event) => {
    const signaturePathInput = document.getElementById('signaturePath');
    if (signaturePathInput) {
        const newPath = event.detail.signature_path || '';
        signaturePathInput.value = newPath;
        console.log('Signature Update Event:', {
            event: event,
            detail: event.detail,
            newValue: signaturePathInput.value,
            inputElement: signaturePathInput
        });

        // Store in localStorage as backup
        localStorage.setItem('lastSignaturePath', newPath);
    } else {
        console.error('Signature path input not found');
    }
});

// Add form submission handler
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default submission

    // Get the signature path from localStorage if it exists
    const lastSignaturePath = localStorage.getItem('lastSignaturePath');
    const signaturePathInput = document.getElementById('signaturePath');
    
    // If we have a stored path and the input is empty or unchanged, use the stored path
    if (lastSignaturePath && (!signaturePathInput.value || signaturePathInput.value === '{{ auth()->user()->signature_path }}')) {
        signaturePathInput.value = lastSignaturePath;
    }

    const formData = new FormData(this);
    console.log('Form Submission Data:', {
        allData: Object.fromEntries(formData),
        signaturePath: formData.get('signature_path'),
        signatureInputValue: signaturePathInput.value,
        storedPath: lastSignaturePath
    });

    // Submit the form
    this.submit();
});

// Add error handling function
async function handleResponse(response) {
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
        return await response.json();
    }
    throw new Error('Response was not JSON');
}

// Update the Profile class initialization
document.addEventListener('DOMContentLoaded', function() {
    window.profileInstance = new Profile();
});
</script>
@endsection