<nav class="navbar-container bg-wgg-white border-b border-wgg-border shadow-sm">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center space-x-6">
                <svg width="32" height="32" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-wgg-black-950">
                    <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="currentColor"/>
                    <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="currentColor"/>
                    <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="currentColor"/>
                </svg>

                <!-- Navigation Links - Next to Logo -->
                <div class="hidden md:flex items-center space-x-4">
                    <a class="px-4 py-2 rounded-lg transition-all duration-200 font-medium text-xs {{ request()->routeIs('home') ? 'bg-wgg-black-950 text-white shadow-lg scale-105' : 'text-gray-600 hover:bg-gray-100' }}" href="{{ route('home') }}">
                        Home
                    </a>

                    @auth
                        <a class="px-4 py-2 rounded-lg transition-all duration-200 font-medium text-xs {{ request()->routeIs('claims.dashboard') ? 'bg-wgg-black-950 text-white shadow-lg scale-105' : 'text-gray-600 hover:bg-gray-100' }}" href="{{ route('claims.dashboard') }}">
                            Claims
                        </a>

                        @if (Auth::user()->role->name != 'Staff')
                        <a class="px-4 py-2 rounded-lg transition-all duration-200 font-medium text-xs {{ request()->routeIs('claims.approval') ? 'bg-wgg-black-950 text-white shadow-lg scale-105' : 'text-gray-600 hover:bg-gray-100' }}" href="{{ route('claims.approval') }}">
                            Approval
                        </a>
                        @endif

                        <a class="px-4 py-2 rounded-lg transition-all duration-200 font-medium text-xs {{ request()->routeIs('claims.new') ? 'bg-green-600 text-white shadow-lg' : 'text-white bg-green-500 hover:bg-green-600' }}" href="{{ route('claims.new') }}">
                            <span class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-medium" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                <span>New Claim</span>
                            </span>
                        </a>
                    @endauth
                </div>
            </div>
            <!-- Right Side Menu -->
            <div class="hidden md:flex justify-center items-center space-x-4">
                @auth
                    <!-- Notifications -->
                    <div class="relative flex items-center space-x-4">
                        <a href="{{ route('notifications') }}" class="inline-block p-2 rounded-full hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs min-w-[20px] h-5 px-1 flex items-center justify-center rounded-full">
                                    {{ Auth::user()->unreadNotifications->count() > 99 ? '99+' : Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                    </div>

                    <!-- Profile -->
                    <div class="relative flex items-center space-x-4">
                        <a href="{{ route('profile') }}" class="inline-block p-2 rounded-full hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    </div>

                    <!-- Logout -->
                    <div class="relative flex items-center space-x-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-block p-2 rounded-full hover:bg-red-100 text-red-600 hover:text-red-900 transition-colors">
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login.form') }}" class="inline-block p-2 rounded-full hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </a>
                        <a href=" " class="inline-block p-2 rounded-full hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>
