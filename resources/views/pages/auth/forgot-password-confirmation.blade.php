@extends('layouts.auth')

@section('title', 'Password Reset Request - WeClaim')

@section('content')
<div class="min-h-screen w-full flex items-center justify-center bg-gradient-to-r from-wgg-black-950 to-black">
    <div class="bg-white p-10 rounded-xl shadow-2xl w-full max-w-md text-center">
        <svg width="60" height="48" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4">
            <path d="M556.869 437.436H0L278.434 0L556.869 437.436ZM278.434 364.53C290.07 364.53 299.508 355.092 299.508 343.456C299.508 331.82 290.07 322.382 278.434 322.382C266.798 322.382 257.36 331.82 257.36 343.456C257.36 355.092 266.798 364.53 278.434 364.53ZM257.36 301.308H299.508V175.074H257.36V301.308Z" fill="#1E1E1E"/>
        </svg>
        
        @if (session('status') === 'success')
            <h1 class="heading-1 mb-4">Reset Link Sent</h1>
            <p class="text-wgg-black-600 mb-6">We have emailed your password reset link. Please check your inbox.</p>
        @else
            <h1 class="heading-1 mb-4">Unable to Send Reset Link</h1>
            <p class="text-wgg-black-600 mb-6">We couldn't find an account with that email address. Please try again or contact support.</p>
        @endif

        <a href="{{ route('login') }}" class="btn flex items-center justify-center bg-wgg-black-950 hover:bg-wgg-black-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
            Return to Login
        </a>
    </div>
</div>
@endsection
