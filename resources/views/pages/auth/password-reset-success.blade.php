@extends('layouts.auth')

@section('title', 'Password Reset Success - WeClaim')

@section('content')
    <div
        class="flex min-h-screen w-full items-center justify-center bg-gradient-to-r from-wgg-black-950 to-black md:bg-gradient-to-r md:from-wgg-black-950 md:to-black">
        <div
            class="flex min-h-screen w-full flex-col items-center justify-center bg-white p-8 md:min-h-0 md:w-full md:max-w-md md:rounded-xl md:p-10 md:shadow-2xl">
            <div class="mb-6">
                <svg class="mx-auto text-green-500" width="60" height="48" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h2 class="mb-4 text-center text-2xl font-bold text-gray-900">Password Reset Successful!</h2>
            <p class="mb-4 text-center text-gray-600">Your password has been changed successfully.</p>
            <p class="text-center text-sm text-gray-500">Redirecting to login page in <span id="countdown">5</span>
                seconds...</p>
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
