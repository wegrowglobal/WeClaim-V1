@extends('layouts.app')

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 animate-slide-in">
        <h1 class="heading-1">Profile Settings</h1>
        <p class="text-gray-600 mt-1">Manage your account information and preferences</p>
    </div>

    <!-- Profile Form -->
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-black/5 animate-slide-in delay-100">
        <form action="{{ route('profile.update') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="divide-y divide-gray-100">
            @csrf
            @method('PUT')
            
            <!-- Photo Upload Section -->
            <div class="p-6">
                <div class="flex items-center gap-6">
                    <div class="profile-picture relative h-24 w-24 rounded-full ring-1 ring-gray-200/50 bg-gray-50 shadow-sm">
                        @if(auth()->user()->profile_picture)
                            <img src="{{ Storage::url(auth()->user()->profile_picture) }}" 
                                 alt="Profile picture" 
                                 class="w-full h-full object-cover rounded-full">
                        @else
                            <div class="w-full h-full rounded-full flex items-center justify-center text-2xl font-medium text-gray-400 bg-gray-50">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                            </div>
                        @endif
                        
                        <label class="absolute -bottom-1 -right-1 p-2 rounded-full bg-white shadow-sm cursor-pointer hover:bg-gray-50 transition-colors ring-1 ring-gray-200/50">
                            <input type="file" 
                                   id="profile_picture" 
                                   name="profile_picture" 
                                   class="hidden" 
                                   accept="image/*">
                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </label>
                    </div>
                    
                    <div>
                        <h2 class="text-base font-medium text-gray-900">Profile Photo</h2>
                        <p class="text-sm text-gray-500 mt-1">JPG, PNG or GIF (max. 2MB)</p>
                    </div>
                </div>
            </div>
            
            <!-- Personal Information Section -->
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="first_name" class="block text-sm font-medium text-gray-600/80 mb-1">First Name</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="{{ auth()->user()->first_name }}"
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="second_name" class="block text-sm font-medium text-gray-600/80 mb-1">Second Name</label>
                        <input type="text" 
                               id="second_name" 
                               name="second_name" 
                               value="{{ auth()->user()->second_name }}"
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                               required>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="email" class="block text-sm font-medium text-gray-600/80 mb-1">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ auth()->user()->email }}"
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="block text-sm font-medium text-gray-600/80 mb-1">Phone Number</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ auth()->user()->phone }}"
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                               required>
                    </div>
                </div>
            </div>

            <!-- Address Information Section -->
            <div class="p-6 space-y-6">
                <div>
                    <h2 class="text-base font-medium text-gray-600">Address Information</h2>
                    <p class="text-sm text-gray-500 mt-1">Your residential address details</p>
                </div>

                <!-- Address Textarea -->
                <div class="form-group">
                    <label for="address" class="block text-sm font-medium text-gray-600/80 mb-1">Street Address</label>
                    <textarea 
                        id="address" 
                        name="address" 
                        rows="3"
                        class="p-4 form-textarea block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm resize-none"
                        required
                    >{{ auth()->user()->address }}</textarea>
                </div>

                <!-- City and State -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="city" class="block text-sm font-medium text-gray-600/80 mb-1">City</label>
                        <input type="text" 
                               id="city" 
                               name="city" 
                               value="{{ auth()->user()->city }}"
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="state" class="block text-sm font-medium text-gray-600/80 mb-1">State</label>
                        <select 
                            id="state" 
                            name="state" 
                            class="form-select block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px] px-3 py-2"
                            required
                        >
                            @foreach($stateOptions as $value => $label)
                                <option value="{{ $value }}" {{ auth()->user()->state === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Zip Code and Country -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="zip_code" class="block text-sm font-medium text-gray-600/80 mb-1">Zip Code</label>
                        <input type="text" 
                               id="zip_code" 
                               name="zip_code" 
                               value="{{ auth()->user()->zip_code }}"
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="country" class="block text-sm font-medium text-gray-600/80 mb-1">Country</label>
                        <input type="text" 
                               id="country" 
                               name="country" 
                               value="{{ auth()->user()->country }}"
                               class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm"
                               required>
                    </div>
                </div>
            </div>

            <!-- Banking Details Section -->
            <div class="p-6 space-y-6">
                <div>
                    <h2 class="text-base font-medium text-gray-900">Banking Information</h2>
                    <p class="text-sm text-gray-500 mt-1">Your bank account details for reimbursements</p>
                </div>

                <div class="space-y-4">
                    <div class="form-group">
                        <label for="bank_name" class="block text-sm font-medium text-gray-600/80 mb-1">Bank Name</label>
                        <select 
                            id="bank_name" 
                            name="bank_name" 
                            class="form-select block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm h-[38px] px-3 py-2"
                            required
                        >
                            <option value="Bank A" {{ auth()->user()->bankingInformation?->bank_name === 'Bank A' ? 'selected' : '' }}>Bank A</option>
                            <option value="Bank B" {{ auth()->user()->bankingInformation?->bank_name === 'Bank B' ? 'selected' : '' }}>Bank B</option>
                            <option value="Bank C" {{ auth()->user()->bankingInformation?->bank_name === 'Bank C' ? 'selected' : '' }}>Bank C</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="account_holder" class="block text-sm font-medium text-gray-600/80 mb-1">Account Holder Name</label>
                        <div class="relative">
                            <input type="password" 
                                   id="account_holder" 
                                   name="account_holder" 
                                   value="{{ auth()->user()->bankingInformation?->account_holder }}"
                                   class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm pr-10"
                                   required>
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="toggleVisibility('account_holder')">
                                <svg id="showAccountHolderIcon" class="w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                <svg id="hideAccountHolderIcon" class="w-4 h-4 text-gray-600 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="account_number" class="block text-sm font-medium text-gray-600/80 mb-1">Account Number</label>
                        <div class="relative">
                            <input type="password" 
                                   id="account_number" 
                                   name="account_number" 
                                   value="{{ auth()->user()->bankingInformation?->account_number }}"
                                   class="form-input block w-full rounded-lg border border-gray-200 bg-gray-50/50 focus:bg-white focus:border-gray-400 transition-all sm:text-sm pr-10"
                                   required>
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="toggleVisibility('account_number')">
                                <svg id="showAccountNumberIcon" class="w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                <svg id="hideAccountNumberIcon" class="w-4 h-4 text-gray-600 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Section -->
            <div class="p-6 bg-gray-50/50">
                <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
                    <a href=" " 
                       class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg border border-red-200 shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                        <svg class="w-4 h-4 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Reset Password
                    </a>

                    <button type="submit" 
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Success Message Toast -->
@if (session('success'))
    <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 ease-in-out opacity-100 translate-y-0"
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 3000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

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
</script>
@endsection