@extends('layouts.auth')

@section('title', 'Login - WeClaim')

@section('content')
<div class="min-h-screen w-full flex items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-2xl rounded-3xl overflow-hidden">
            <div class="px-8 py-12">
                <div class="text-center mb-8">
                    <svg width="48" height="48" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4">
                        <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="#242424"/>
                        <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="#242424"/>
                        <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="#242424"/>
                    </svg>
                    <h1 class="text-3xl font-bold text-gray-900">Welcome Back</h1>
                    <p class="text-gray-500 mt-2">Sign in to your account</p>
                </div>

                <form method="POST" class="space-y-6" action="{{ route('login') }}">
                    @csrf
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" value="{{ old('email') }}" name="email" class="block w-full px-4 py-3 rounded-lg border-0 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all sm:text-sm" type="email" placeholder="Email" required>
                    </div>

                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <div class="relative">
                            <input id="password" name="password" class="block w-full px-4 py-3 rounded-lg border-0 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-500 transition-all sm:text-sm pr-10" type="password" placeholder="Password" required>
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword()">
                                <svg id="showPasswordIcon" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                <svg id="hidePasswordIcon" class="h-5 w-5 text-gray-400 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if ($errors->has('auth'))
                        <div class="flex items-center gap-2 p-3 bg-red-50/50 rounded-lg border border-red-100 backdrop-blur-sm">
                            <svg class="w-5 h-5 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-red-600 font-medium">{{ $errors->first('auth') }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded">
                            <label for="remember" class="ml-2 block text-sm text-gray-600">
                                Remember me
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-gray-600 hover:text-gray-500">Forgot password?</a>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-wgg-black-800 hover:bg-wgg-black-950 text-white font-medium text-sm rounded-lg shadow-sm transition-all duration-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            Sign In
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <a href="#" class="font-medium text-gray-600 hover:text-gray-500">Request one</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        var passwordInput = document.getElementById('password');
        var showPasswordIcon = document.getElementById('showPasswordIcon');
        var hidePasswordIcon = document.getElementById('hidePasswordIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            showPasswordIcon.classList.add('hidden');
            hidePasswordIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            showPasswordIcon.classList.remove('hidden');
            hidePasswordIcon.classList.add('hidden');
        }
    }
</script>

@endsection
