@extends('layouts.auth')

@section('title', 'Request Account - WeClaim')

@section('content')
    <div
        class="flex min-h-[100dvh] w-full items-center justify-center bg-gradient-to-br from-wgg-black-950 to-wgg-black-800 md:bg-gradient-to-br md:from-wgg-black-950 md:to-wgg-black-800">
        <div class="h-full w-full overflow-y-auto bg-white md:h-auto md:max-w-md md:rounded-3xl md:shadow-2xl">
            <div class="flex min-h-[100dvh] flex-col justify-center px-8 py-12 md:min-h-0">
                <div class="mb-8 text-center">
                    <svg class="mx-auto mb-4" width="48" height="48" viewBox="0 0 557 438" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z"
                            fill="#242424" />
                        <path
                            d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z"
                            fill="#242424" />
                        <path
                            d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z"
                            fill="#242424" />
                    </svg>
                    <h1 class="text-3xl font-bold text-gray-900">Request Account</h1>
                    <p class="mt-2 text-gray-500">Fill in your details to request an account</p>
                </div>

                <form class="space-y-6" method="POST" action="{{ route('register.request') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="sr-only" for="first_name">First Name</label>
                            <input
                                class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 transition-all focus:bg-white focus:ring-2 focus:ring-gray-500 sm:text-sm"
                                id="first_name" name="first_name" type="text" value="{{ old('first_name') }}"
                                placeholder="First Name" required>
                        </div>
                        <div>
                            <label class="sr-only" for="last_name">Last Name</label>
                            <input
                                class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 transition-all focus:bg-white focus:ring-2 focus:ring-gray-500 sm:text-sm"
                                id="last_name" name="last_name" type="text" value="{{ old('last_name') }}"
                                placeholder="Last Name" required>
                        </div>
                    </div>

                    <div>
                        <label class="sr-only" for="email">Email</label>
                        <input
                            class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 transition-all focus:bg-white focus:ring-2 focus:ring-gray-500 sm:text-sm"
                            id="email" name="email" type="email" value="{{ old('email') }}"
                            placeholder="Work Email" required>
                    </div>

                    <div>
                        <label class="sr-only" for="department">Department</label>
                        <select
                            class="block w-full rounded-lg border-0 bg-gray-50 px-4 py-3 transition-all focus:bg-white focus:ring-2 focus:ring-gray-500 sm:text-sm"
                            id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Administration">Administration</option>
                            <option value="Human Resources">Human Resources</option>
                            <option value="Finance and Account">Finance and Account</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Sales">Sales</option>
                            <option value="IT and Technical">IT and Technical</option>
                            <option value="Procurement and Assets">Procurement and Assets</option>
                            <option value="Retails">Retails</option>
                            <option value="All">All</option>
                        </select>
                    </div>

                    @if ($errors->any())
                        <div
                            class="flex items-center gap-2 rounded-lg border border-red-100 bg-red-50/50 p-3 backdrop-blur-sm">
                            <svg class="h-5 w-5 shrink-0 text-red-500" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd" />
                            </svg>
                            <ul class="text-sm font-medium text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <button
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-wgg-black-800 px-4 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-wgg-black-950 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            type="submit">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Submit Request
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a class="font-medium text-gray-600 hover:text-gray-500" href="{{ route('login') }}">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/register-handler.js'])
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                handleRegistration(document.querySelector('form'));
            });
        </script>
    @endpush
@endsection
