@extends('layouts.auth')

@section('title', 'Registration Success - WeClaim')

@section('content')
    <div
        class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800 md:bg-gradient-to-br md:from-wgg-black-950 md:to-wgg-black-800">
        <div class="h-full w-full overflow-hidden bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-2xl">
            <div class="flex min-h-screen flex-col justify-center px-8 py-12 md:min-h-0">
                <div class="mb-8 text-center">
                    <svg class="mx-auto mb-4" width="60" height="48" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#0A0A0A" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <h1 class="text-3xl font-bold text-gray-900">Request Submitted!</h1>
                    <p class="mt-2 text-gray-500">Your registration request has been submitted successfully. You will
                        receive an email once your account is approved.</p>
                </div>

                <div>
                    <a class="flex w-full items-center justify-center gap-2 rounded-lg bg-wgg-black-800 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-wgg-black-950 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        href="{{ route('login') }}">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Return to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
