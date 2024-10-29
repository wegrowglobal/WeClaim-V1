<nav class="navbar-container bg-wgg-white border-b border-wgg-border shadow-sm">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <svg width="32" height="32" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-wgg-black-950">
                    <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="currentColor"/>
                    <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="currentColor"/>
                    <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="currentColor"/>
                </svg>
            </div>

            <!-- Navigation Links - Center -->
            <div class="hidden md:flex items-center space-x-4">
                <a class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}" href="{{ route('home') }}">
                    Home
                </a>

                @auth
                    <a class="nav-link {{ request()->routeIs('claims.dashboard') ? 'nav-link-active' : '' }}" href="{{ route('claims.dashboard') }}">
                        Claims
                    </a>

                    @if (Auth::user()->role->name != 'Staff')
                    <a class="nav-link {{ request()->routeIs('claims.approval') ? 'nav-link-active' : '' }}" href="{{ route('claims.approval') }}">
                        Approval
                    </a>
                    @endif

                    <a class="nav-link {{ request()->routeIs('claims.new') ? 'bg-green-600 text-white hover:bg-green-700 hover:text-wgg-white px-4 py-2 rounded-md' : '' }}" href="{{ route('claims.new') }}">
                        New Claim
                    </a>
                @endauth
            </div>

            <!-- Right Side Menu -->
            <div class="hidden md:flex items-center space-x-6">
                @auth
                    <!-- Notifications -->
                    <div class="relative">
                        <a href="{{ route('notifications') }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-4 h-4 flex items-center justify-center rounded-full">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('profile') }}" class="flex items-center space-x-2 hover:opacity-80 transition-opacity">
                            @if(Auth::user()->profile_picture && Storage::disk('public')->exists(Auth::user()->profile_picture))
                                <img src="{{ Storage::url(Auth::user()->profile_picture) }}" alt="Profile" class="h-8 w-8 rounded-full object-cover ring-2 ring-gray-100">
                            @else
                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white text-sm font-medium ring-2 ring-gray-100" style="background-color: {{ '#' . substr(md5(Auth::user()->first_name), 0, 6) }}">
                                    {{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}
                                </div>
                            @endif
                        </a>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="flex items-center space-x-2">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                Logout
                            </button>
                        </form>
                    </div>

                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login.form') }}" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors">Request Access</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>
