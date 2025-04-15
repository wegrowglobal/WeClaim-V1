@extends('layouts.auth')

@section('title', 'Password Reset Successful - WeClaim')

@section('content')
<div class="w-full max-w-md mx-auto px-6 py-12 text-center">

    {{-- Success Icon --}}
    <div class="mb-6">
        <svg class="mx-auto h-16 w-16 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>

    {{-- Title & Message --}}
    <h1 class="text-2xl font-bold text-black mb-4">Password Reset Successfully</h1>
    <p class="text-sm text-gray-700 mb-8">Your password has been updated. You can now sign in with your new password.</p>

    {{-- Action Button --}}
    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md bg-black px-6 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2">
        Return to Sign In
    </a>

</div>
@endsection
