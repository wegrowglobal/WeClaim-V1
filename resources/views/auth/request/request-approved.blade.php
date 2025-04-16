@extends('layouts.auth')

@section('title', 'Registration Approved - WeClaim')

@section('content')
{{-- Replicate structure similar to login page --}}
<div class="w-full max-w-md mx-auto px-6 py-12">
    <div class="text-center">
        {{-- Success Icon --}}
        <div class="mb-4">
            <svg class="mx-auto h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        {{-- Heading --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Registration Approved</h1>
        {{-- Message --}}
        <p class="text-gray-600 mb-6">
            The registration request has been approved successfully. An email has been sent to the user with instructions to set up their password.
        </p>
        {{-- Button --}}
        <div class="mt-6"> {{-- Adjusted margin --}}
            <a href="{{ route('login') }}" 
               class="flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"> {{-- Matched button style from login/request --}}
                Return to Login
            </a>
        </div>
    </div>
</div>
@endsection
