@extends('layouts.auth')

@section('title', 'Set Password - WeClaim')

@section('content')
    <div
        class="flex min-h-[100dvh] w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800 md:bg-gradient-to-br md:from-wgg-black-950 md:to-wgg-black-800">
        <div class="h-full w-full md:h-auto md:max-w-md">
            <div
                class="flex min-h-[100dvh] flex-col justify-center overflow-y-auto bg-white shadow-2xl md:h-auto md:rounded-3xl">
                <div class="px-8 py-12">
                    <div class="mb-8 text-center">
                        <h1 class="text-3xl font-bold text-gray-900">Set Your Password</h1>
                        <p class="mt-2 text-gray-500">Create a secure password for your account</p>
                    </div>

                    <form class="space-y-6" method="POST" action="{{ route('password.setup', ['token' => $token]) }}">
                        @csrf
                        <input name="token" type="hidden" value="{{ $token }}">
                        <input name="email" type="hidden" value="{{ $email }}">

                        <div>
                            <label class="sr-only" for="password">Password</label>
                            <input
                                class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 transition-all focus:bg-white focus:ring-2 focus:ring-gray-500 sm:text-sm"
                                id="password" name="password" type="password" placeholder="New Password" required>
                        </div>

                        <div>
                            <label class="sr-only" for="password_confirmation">Confirm Password</label>
                            <input
                                class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 transition-all focus:bg-white focus:ring-2 focus:ring-gray-500 sm:text-sm"
                                id="password_confirmation" name="password_confirmation" type="password"
                                placeholder="Confirm Password" required>
                        </div>

                        <button
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-wgg-black-800 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-wgg-black-950"
                            type="submit">
                            Set Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
