@extends('layouts.auth')

@section('title', 'Forgot Password - WeClaim')

@section('content')
    <div class="flex min-h-[100dvh] w-full items-center justify-center bg-gray-100 px-4 py-8">
        <div class="h-full w-full overflow-y-auto bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-xl">
            <div class="flex min-h-[100dvh] flex-col justify-center px-8 py-12 md:min-h-0">
                <!-- Logo and Header -->
                <div class="mb-8 text-center">
                    <svg class="mx-auto mb-6" width="48" height="48" viewBox="0 0 557 438" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z"
                            fill="#000000" />
                        <path
                            d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z"
                            fill="#000000" />
                        <path
                            d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z"
                            fill="#000000" />
                    </svg>
                    <h1 class="text-2xl font-bold text-black">Forgot Password</h1>
                    <p class="mt-2 text-sm text-gray-500">Enter your email to reset your password</p>
                </div>

                <div class="space-y-6">
                    @if (session('status'))
                        <div class="mb-4 flex items-center gap-2 rounded-md border border-green-100 bg-green-50 p-3">
                            <svg class="h-5 w-5 shrink-0 text-green-500" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                    clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-medium text-green-600">{{ session('status') }}</p>
                        </div>
                    @endif

                    <form class="space-y-5" method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <!-- Email Input -->
                        <div class="relative">
                            <label class="mb-1 block text-sm font-medium text-gray-700" for="email">Email</label>
                            <input class="block w-full rounded-md border border-gray-200 bg-white px-4 py-3 text-sm transition-all focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('email') border-red-300 @enderror"
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                placeholder="your@email.com"
                                required>
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button class="flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm transition-all hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"
                            type="submit">
                            Send Reset Link
                        </button>
                    </form>
                </div>

                <!-- Back to Login -->
                <div class="mt-8 text-center">
                    <a class="inline-flex items-center gap-2 text-sm font-medium text-black hover:text-gray-700 hover:underline"
                        href="{{ route('login') }}">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd"/>
                        </svg>
                        Back to login
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
