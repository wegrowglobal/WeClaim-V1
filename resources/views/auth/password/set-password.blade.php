@extends('layouts.auth')

@section('title', 'Set Password - WeClaim')

@section('content')
<div class="w-full max-w-md mx-auto px-6 py-12">
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}">
             <svg class="mx-auto h-12 w-auto text-black" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="currentColor"/>
                <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="currentColor"/>
                <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="currentColor"/>
            </svg>
        </a>
    </div>

    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-black">Set Your Password</h1>
        <p class="mt-2 text-sm text-gray-700">Create a secure password for your account.</p>
    </div>

    <form class="space-y-5" method="POST" action="{{ route('password.setup', ['token' => $token]) }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="mb-1 block text-sm font-medium text-black" for="email">Email</label>
            <input class="block w-full rounded-md border border-gray-300 bg-gray-100 px-4 py-3 text-sm text-gray-500 focus:outline-none focus:ring-0"
                id="email"
                name="email"
                type="email"
                value="{{ $email ?? old('email') }}"
                readonly>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-black" for="password">Password</label>
            <input class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('password') border-black @enderror"
                id="password"
                name="password"
                type="password"
                placeholder="Enter password (min. 8 characters)"
                required>
            @error('password')
                <span class="mt-1 text-xs font-medium text-black">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-black" for="password_confirmation">Confirm Password</label>
            <input class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black"
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                placeholder="Confirm your password"
                required>
        </div>

        <button class="flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"
            type="submit">
            Set Password and Activate Account
        </button>
    </form>
</div>
@endsection
