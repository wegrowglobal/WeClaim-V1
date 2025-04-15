@extends('layouts.auth')

@section('title', 'Forgot Password Confirmation - WeClaim')

@section('content')
<div class="w-full max-w-md mx-auto px-6 py-12 text-center">

    <div class="mb-6">
        <svg class="mx-auto h-16 w-16 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z" />
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-black mb-4">Password Reset Email Sent</h1>
    @if (session('status') == 'success')
        <p class="text-sm text-gray-700 mb-8">If an account with that email exists, we have sent a password reset link. Please check your inbox (and spam folder).</p>
    @else
         <p class="text-sm text-gray-700 mb-8">There was an issue sending the password reset link. Please try again later or contact support.</p>
    @endif

    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md bg-black px-6 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2">
        Return to Sign In
    </a>

</div>
@endsection
