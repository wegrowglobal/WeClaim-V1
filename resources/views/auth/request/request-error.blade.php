@extends('layouts.auth')

@section('title', 'Registration Error - WeClaim')

@section('content')
{{-- Replicate structure similar to login page --}}
<div class="w-full max-w-md mx-auto px-6 py-12">
    <div class="text-center">
        {{-- Error Icon --}}
        <div class="mb-4">
             <svg class="mx-auto h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"> {{-- Changed color to red for error --}}
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        {{-- Heading --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Registration Error</h1>
        {{-- Message --}}
        <p class="text-gray-600 mb-6">
            {{-- Display the message passed from the controller, default if not set --}}
            {{ $message ?? 'An unexpected error occurred. Please try again or contact support.' }}
        </p>
        {{-- Button --}}
        <div class="mt-6"> {{-- Adjusted margin --}}
            <a href="{{ route('login') }}" 
               class="flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"> {{-- Matched button style from login/request --}}
                {{-- Removed icon from button text for consistency --}}
                Return to Login
            </a>
        </div>
    </div>
</div>
@endsection 