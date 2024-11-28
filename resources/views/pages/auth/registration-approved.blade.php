@extends('layouts.auth')

@section('content')
    <div
        class="flex min-h-[100dvh] w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800 md:bg-white">
        <div class="w-full max-w-md md:flex md:h-[100dvh] md:w-full md:max-w-none md:items-center md:justify-center">
            <div
                class="overflow-y-auto rounded-3xl bg-white shadow-2xl md:flex md:h-full md:w-full md:flex-col md:justify-center md:rounded-none md:shadow-none">
                <div class="px-8 py-12 md:text-center">
                    <div class="mb-8 text-center">
                        <svg class="mx-auto mb-4" width="60" height="48" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#0A0A0A" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <h1 class="text-3xl font-bold text-gray-900">Registration Approved!</h1>
                        <p class="mt-2 text-gray-500">The registration request has been approved. An email has been sent to
                            the user with instructions to set up their password.</p>
                    </div>

                    <a class="flex w-full items-center justify-center gap-2 rounded-lg bg-wgg-black-800 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-wgg-black-950"
                        href="{{ route('login') }}">
                        Return to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
