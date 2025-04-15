@extends('layouts.auth')

@section('title', 'Registration Rejected - WeClaim')

@section('content')
<div class="flex min-h-[100dvh] w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800">
    <div class="h-full w-full overflow-y-auto bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-2xl">
        <div class="flex min-h-[100dvh] flex-col justify-center px-8 py-12 md:min-h-0">
            <div class="text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Registration Rejected</h2>
                <p class="text-gray-600 mb-6">
                    The registration request has been rejected. An email notification has been sent to the applicant.
                </p>
                <div class="mt-4">
                    <a href="{{ route('login') }}" class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                        Return to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 