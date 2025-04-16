@extends('layouts.auth')

@section('title', 'Link Expired - WeClaim')

@section('content')
{{-- Replicate structure similar to login page --}}
<div class="w-full max-w-md mx-auto px-6 py-12">
    <div class="text-center">
        {{-- Icon --}}
        <div class="mb-4">
             <svg class="mx-auto h-12 w-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
             </svg>
        </div>
        {{-- Heading --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Link Expired</h1>
        {{-- Message --}}
        <p class="text-gray-600 mb-6">
            This password setup link has expired or is no longer valid.
            Please request a password reset or contact support if you need assistance.
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