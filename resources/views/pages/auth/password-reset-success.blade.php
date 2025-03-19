@extends('layouts.auth')

@section('title', 'Password Reset Success - WeClaim')

@section('content')
    <div class="flex min-h-[100dvh] w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800">
        <div class="h-full w-full overflow-y-auto bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-2xl">
            <div class="flex min-h-[100dvh] flex-col justify-center px-8 py-12 md:min-h-0">
                <!-- Success Message -->
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Password Reset Successful!</h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Your password has been changed successfully.
                    </p>
                    <p class="mt-4 text-sm text-gray-500">
                        Redirecting to login page in <span id="countdown" class="font-medium text-indigo-600">5</span> seconds...
                    </p>
                </div>

                <!-- Return to Login Button -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="p-4">
                        <a class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                            href="{{ route('login') }}">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z"
                                    clip-rule="evenodd"/>
                                <path fill-rule="evenodd"
                                    d="M19 10a.75.75 0 00-.75-.75H8.704l1.048-.943a.75.75 0 10-1.004-1.114l-2.5 2.25a.75.75 0 000 1.114l2.5 2.25a.75.75 0 101.004-1.114l-1.048-.943h9.546A.75.75 0 0019 10z"
                                    clip-rule="evenodd"/>
                            </svg>
                            Return to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown timer
        let seconds = 5;
        const countdownDisplay = document.getElementById('countdown');

        const timer = setInterval(() => {
            seconds--;
            countdownDisplay.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = '{{ route('login') }}';
            }
        }, 1000);
    </script>
@endsection
