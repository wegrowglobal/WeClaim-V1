@extends('layouts.auth')

@section('title', 'Registration Error - WeClaim')

@section('content')
<div class="flex min-h-[100dvh] w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800">
    <div class="h-full w-full overflow-y-auto bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-2xl">
        <div class="flex min-h-[100dvh] flex-col justify-center px-8 py-12 md:min-h-0">
            <div class="text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Registration Error</h2>
                <p class="text-gray-600 mb-6">
                    {{ $message }}
                </p>
                <div class="mt-4">
                    <a href="{{ route('login') }}" class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M19 10a.75.75 0 00-.75-.75H8.704l1.048-.943a.75.75 0 10-1.004-1.114l-2.5 2.25a.75.75 0 000 1.114l2.5 2.25a.75.75 0 101.004-1.114l-1.048-.943h9.546A.75.75 0 0019 10z" clip-rule="evenodd"/>
                        </svg>
                        Return to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 