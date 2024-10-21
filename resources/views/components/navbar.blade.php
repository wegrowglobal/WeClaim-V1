<nav class="h-full bg-wgg-white">
    <div class="flex flex-col h-full justify-between p-4 rounded-lg shadow-lg border border-wgg-border">
        <div class="space-y-4">
            <!-- Logo -->
            <div class="flex items-center justify-center mb-6">
                <svg width="48" height="48" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-wgg-black-950">
                    <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="currentColor"/>
                    <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="currentColor"/>
                    <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="currentColor"/>
                </svg>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1">
                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out {{ request()->routeIs('home') ? 'bg-wgg-black-950 text-wgg-white hover:bg-wgg-black-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 transform hover:scale-105' }}" href="{{ route('home') }}">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Home
                </a>

                @auth
                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out {{ request()->routeIs('claims.dashboard') ? 'bg-wgg-black-950 text-wgg-white hover:bg-wgg-black-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 transform hover:scale-105' }}" href="{{ route('claims.dashboard') }}">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Claims
                </a>

                @if (Auth::user()->role->name != 'Staff')
                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out {{ request()->routeIs('claims.approval') ? 'bg-wgg-black-950 text-wgg-white hover:bg-wgg-black-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 transform hover:scale-105' }}" href="{{ route('claims.approval') }}">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approval
                </a>
                @endif

                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out {{ request()->routeIs('claims.new') ? 'bg-green-600 text-wgg-white hover:bg-green-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 transform hover:scale-105' }}" href="{{ route('claims.new') }}">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Claim
                </a>
                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out text-gray-400 cursor-not-allowed" href="#" onclick="return false;">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Reports
                </a>

                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out text-gray-400 cursor-not-allowed hover:bg-gray-100" href="#" onclick="return false;">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                </a>

                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out text-gray-400 cursor-not-allowed hover:bg-gray-100" href="#" onclick="return false;">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>

                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out text-gray-400 cursor-not-allowed hover:bg-gray-100" href="#" onclick="return false;">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Help & Support
                </a>

                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out text-gray-400 cursor-not-allowed hover:bg-gray-100" href="#" onclick="return false;">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    History
                </a>

                @endauth

                @guest
                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out {{ request()->routeIs('login') ? 'bg-wgg-black-950 text-wgg-white hover:bg-wgg-black-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transform hover:scale-105" href="{{ route('login') }}">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Login
                </a>
                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out {{ request()->routeIs('register') ? 'bg-wgg-black-950 text-wgg-white hover:bg-wgg-black-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} transform hover:scale-105" href=" ">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Request Access
                </a>
                <a class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 ease-in-out text-gray-600 hover:bg-gray-100 hover:text-gray-900 transform hover:scale-105" href="#">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    About Us
                </a>
                @endguest
            </nav>
        </div>

        @auth
        <div class="mt-6 space-y-2">
            <a href="{{ route('profile') }}" class="w-full flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-blue-500 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 ease-in-out transform hover:scale-10">
                <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                </svg>
                Profile Settings
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md text-white bg-red-500 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 ease-in-out transform hover:scale-105">
                    <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
        @endauth
    </div>
</nav>
