@extends('layouts.auth')

@section('title', 'Reset Password - WeClaim')

@section('content')
    <div class="flex min-h-[100dvh] w-full items-center justify-center bg-gray-100 px-4 py-8">
        <div class="h-full w-full overflow-y-auto bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-xl">
            <div class="flex min-h-[100dvh] flex-col justify-center px-8 py-12 md:min-h-0">
                <!-- Logo and Header -->
                <div class="mb-8 text-center">
                    <svg class="mx-auto mb-6" width="48" height="48" viewBox="0 0 557 438" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="#000000"/>
                        <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="#000000"/>
                        <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="#000000"/>
                    </svg>
                    <h1 class="text-2xl font-bold text-black">Reset Password</h1>
                    <p class="mt-2 text-sm text-gray-500">Enter your new password</p>
                </div>

                <div class="space-y-6">
                    <form class="space-y-5" method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input name="token" type="hidden" value="{{ $token }}">

                        <!-- Email Input -->
                        <div class="relative">
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="email">Email</label>
                            <input class="block w-full rounded-md border border-gray-200 bg-white px-4 py-3 text-sm transition-all focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('email') border-red-300 @enderror"
                                id="email"
                                name="email"
                                type="email"
                                value="{{ $email ?? old('email') }}"
                                placeholder="your@email.com"
                                required
                                readonly>
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="relative">
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="password">New Password</label>
                            <input class="block w-full rounded-md border border-gray-200 bg-white px-4 py-3 pr-10 text-sm transition-all focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('password') border-red-300 @enderror"
                                id="password"
                                name="password"
                                type="password"
                                placeholder="••••••••"
                                required>
                            <button class="absolute inset-y-0 right-0 flex items-center pr-3 mt-6" 
                                type="button"
                                onclick="togglePassword('password')">
                                <svg class="h-5 w-5 text-gray-400" id="showPasswordIcon" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd"
                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                        clip-rule="evenodd"/>
                                </svg>
                                <svg class="hidden h-5 w-5 text-gray-400" id="hidePasswordIcon"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                                        clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </button>
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password Input -->
                        <div class="relative">
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="password_confirmation">Confirm Password</label>
                            <input class="block w-full rounded-md border border-gray-200 bg-white px-4 py-3 pr-10 text-sm transition-all focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                placeholder="••••••••"
                                required>
                            <button class="absolute inset-y-0 right-0 flex items-center pr-3 mt-6" 
                                type="button"
                                onclick="togglePassword('password_confirmation')">
                                <svg class="h-5 w-5 text-gray-400" id="showPasswordConfirmIcon" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd"
                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                        clip-rule="evenodd"/>
                                </svg>
                                <svg class="hidden h-5 w-5 text-gray-400" id="hidePasswordConfirmIcon"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                                        clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Submit Button -->
                        <button class="flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm transition-all hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"
                            type="submit">
                            Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const showIcon = document.getElementById('show' + inputId.charAt(0).toUpperCase() + inputId.slice(1) + 'Icon');
            const hideIcon = document.getElementById('hide' + inputId.charAt(0).toUpperCase() + inputId.slice(1) + 'Icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showIcon.classList.add('hidden');
                hideIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                showIcon.classList.remove('hidden');
                hideIcon.classList.add('hidden');
            }
        }
    </script>
@endsection
