@extends('layouts.auth')

@section('title', 'Request Account - WeClaim')

@section('content')
    <div class="flex min-h-[100dvh] w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800">
        <div class="h-full w-full overflow-y-auto bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-2xl">
            <div class="flex min-h-[100dvh] flex-col justify-center px-8 py-12 md:min-h-0">
                <div class="mb-8 text-center">
                    <svg class="mx-auto mb-4" width="48" height="48" viewBox="0 0 557 438" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z"
                            fill="#242424" />
                        <path
                            d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z"
                            fill="#242424" />
                        <path
                            d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z"
                            fill="#242424" />
                    </svg>
                    <h1 class="text-2xl font-bold text-gray-900">Request Account</h1>
                    <p class="mt-2 text-sm text-gray-500">Fill in your details to request an account</p>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Account Request</p>
                                <p class="text-xs text-gray-500">Your information will be reviewed</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <form class="space-y-4" method="POST" action="{{ route('register.request') }}">
                            @csrf

                            <div class="relative">
                                <label class="sr-only" for="first_name">First Name</label>
                                <input class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600 @error('first_name') border-red-300 @enderror"
                                    id="first_name"
                                    name="first_name"
                                    type="text"
                                    value="{{ old('first_name') }}"
                                    placeholder="First Name"
                                    required>
                                @error('first_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="relative">
                                <label class="sr-only" for="last_name">Last Name</label>
                                <input class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600 @error('last_name') border-red-300 @enderror"
                                    id="last_name"
                                    name="last_name"
                                    type="text"
                                    value="{{ old('last_name') }}"
                                    placeholder="Last Name"
                                    required>
                                @error('last_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="relative">
                                <label class="sr-only" for="email">Email</label>
                                <input class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600 @error('email') border-red-300 @enderror"
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    placeholder="Email"
                                    required>
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="relative">
                                <label class="sr-only" for="department">Department</label>
                                <select class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-indigo-600 @error('department') border-red-300 @enderror"
                                    id="department"
                                    name="department"
                                    required>
                                    <option value="" disabled {{ old('department') ? '' : 'selected' }}>Select Department</option>
                                    <option value="Administration" {{ old('department') == 'Administration' ? 'selected' : '' }}>Administration</option>
                                    <option value="Human Resources" {{ old('department') == 'Human Resources' ? 'selected' : '' }}>Human Resources</option>
                                    <option value="Finance and Account" {{ old('department') == 'Finance and Account' ? 'selected' : '' }}>Finance and Account</option>
                                    <option value="Marketing" {{ old('department') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                    <option value="Sales" {{ old('department') == 'Sales' ? 'selected' : '' }}>Sales</option>
                                    <option value="IT and Technical" {{ old('department') == 'IT and Technical' ? 'selected' : '' }}>IT and Technical</option>
                                    <option value="Procurement and Assets" {{ old('department') == 'Procurement and Assets' ? 'selected' : '' }}>Procurement and Assets</option>
                                    <option value="Retails" {{ old('department') == 'Retails' ? 'selected' : '' }}>Retails</option>
                                    <option value="All" {{ old('department') == 'All' ? 'selected' : '' }}>All</option>
                                </select>
                                @error('department')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                type="submit">
                                Submit Request
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a class="font-medium text-indigo-600 hover:text-indigo-500"
                            href="{{ route('login') }}">
                            Sign in
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/register-handler.js'])
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                handleRegistration(document.querySelector('form'));
            });
        </script>
    @endpush
@endsection
