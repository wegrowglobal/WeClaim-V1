@extends('layouts.auth')

@section('title', 'Invalid Link - WeClaim')

@section('content')
{{-- Replicate structure similar to login page --}}
<div class="w-full max-w-md mx-auto px-6 py-12">
    <div class="text-center">
        {{-- Icon --}}
        <div class="mb-4">
             <svg class="mx-auto h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path> {{-- Using a generic error/cross icon --}}
            </svg>
        </div>
        {{-- Heading --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Invalid Link</h1>
        {{-- Message --}}
        <p class="text-gray-600 mb-6">
            The password setup link you used is invalid or does not correspond to an existing request.
            Please check the link or request a password reset if you have an existing account.
        </p>
        {{-- Buttons --}}
        <div class="mt-6 space-y-4"> 
            {{-- Password Reset Link (if route exists) --}}
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" 
               class="flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2">
                Request Password Reset
            </a>
            @endif
             {{-- Back to Login Link --}}
             <a href="{{ route('login') }}" 
               class="block w-full text-center text-sm font-medium text-black hover:underline"> {{-- Simple text link --}}
                Return to Login
            </a>
        </div>
    </div>
</div>
@endsection 