<nav class="bg-white shadow-sm">
    <div class="mx-auto w-full max-w-7xl">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="animate-slide-in-right flex h-16 items-center justify-between">
                <!-- Left Side -->
                <div class="flex items-center gap-4 sm:gap-8">
                    <!-- Logo -->
                    <a class="animate-fade-in" href="{{ route('home') }}">
                        <svg class="text-[#242424] transition-transform duration-300 hover:scale-110" width="32"
                            height="32" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z"
                                fill="currentColor" />
                            <path
                                d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z"
                                fill="currentColor" />
                            <path
                                d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z"
                                fill="currentColor" />
                        </svg>
                    </a>

                    <!-- Navigation Links -->
                    <div class="hidden items-center gap-4 md:flex">
                        <!-- Each nav item with incremental delays -->
                        <a class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }} animate-fade-in px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-300 hover:text-indigo-600"
                            href="{{ route('home') }}">
                            Home
                        </a>

                        @auth
                            <a class="nav-link {{ request()->routeIs('claims.dashboard') ? 'nav-link-active' : '' }} animate-fade-in px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-300 hover:text-indigo-600"
                                href="{{ route('claims.dashboard') }}">
                                Claims
                            </a>

                            @if (Auth::user()->role_id != 1)
                                <a class="nav-link {{ request()->routeIs('claims.approval') ? 'nav-link-active' : '' }} animate-fade-in px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-300 hover:text-indigo-600"
                                    href="{{ route('claims.approval') }}">
                                    Approval
                                </a>
                            @endif

                            @if (Auth::user()->role_id === 5)
                                <a class="animate-fade-in {{ request()->routeIs('claims.admin') ? 'ring-2 ring-offset-2 ring-purple-500' : '' }} relative inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:from-purple-700 hover:to-indigo-700"
                                    href="{{ route('claims.admin') }}" title="Claims Management">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                </a>
                                <a class="animate-fade-in {{ request()->routeIs('admin.system-config') ? 'ring-2 ring-offset-2 ring-amber-500' : '' }} relative inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:from-amber-700 hover:to-orange-700"
                                    href="{{ route('admin.system-config') }}" title="System Config">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                                <a class="animate-fade-in {{ request()->routeIs('users.management') ? 'ring-2 ring-offset-2 ring-teal-500' : '' }} relative inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:from-teal-700 hover:to-emerald-700"
                                    href="{{ route('users.management') }}" title="User Management">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-2 sm:gap-4">
                    @auth
                        <a class="animate-fade-in hidden items-center gap-2 rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-600 transition-all hover:bg-indigo-100 lg:inline-flex"
                            href="{{ route('claims.new') }}" title="New Claim">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span>New Claim</span>
                        </a>

                        <div class="flex gap-2 lg:hidden">
                            <a class="animate-fade-in rounded-full p-2 text-gray-600 transition-all hover:bg-gray-100"
                                href="{{ route('claims.new') }}" title="New Claim">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </a>
                            <a class="animate-fade-in rounded-full p-2 text-gray-600 transition-all hover:bg-gray-100"
                                href="{{ route('claims.dashboard') }}" title="Claims">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </a>

                            @if (Auth::user()->role_id != 1)
                                <a class="animate-fade-in rounded-full p-2 text-gray-600 transition-all hover:bg-gray-100"
                                    href="{{ route('claims.approval') }}" title="Approval">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </a>
                            @endif

                            @if (Auth::user()->role_id === 5)
                                <a class="animate-fade-in rounded-full p-2 text-gray-600 transition-all hover:bg-gray-100"
                                    href="{{ route('claims.admin') }}" title="Claims Management">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                </a>
                                <a class="animate-fade-in rounded-full p-2 text-gray-600 transition-all hover:bg-gray-100"
                                    href="{{ route('users.management') }}" title="User Management">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <a class="animate-fade-in relative rounded-full p-2 text-gray-600 transition-all hover:bg-gray-100"
                            href="{{ route('notifications') }}" title="Notifications">
                            <svg class="@if (Auth::user()->unreadNotifications->count() > 0) text-indigo-600 @endif h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </a>

                        <a class="animate-fade-in rounded-full p-2 text-gray-600 transition-all hover:bg-gray-100"
                            href="{{ route('profile') }}" title="Profile">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                class="rounded-full p-2 text-gray-600 transition-all hover:bg-red-50 hover:text-red-600"
                                type="submit" title="Sign Out">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    @else
                        <a class="animate-fade-in rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-600 transition-all hover:bg-indigo-100"
                            href="{{ route('login') }}">
                            Sign In
                        </a>
                        <a class="animate-fade-in rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white transition-all hover:bg-indigo-700"
                            href=" ">
                            Create Account
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Breadcrumbs -->
        @if (isset($breadcrumbs) && count($breadcrumbs) > 0)
            <div>
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <nav class="flex py-3" aria-label="Breadcrumb">
                        <div class="breadcrumbs-container animate-slide-in">
                            <ol class="flex items-center space-x-2">
                                @foreach ($breadcrumbs as $index => $breadcrumb)
                                    <li class="flex items-center">
                                        @if ($index > 0)
                                            <svg class="mx-2 h-4 w-4 flex-shrink-0 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        @endif
                                        <div
                                            class="breadcrumb-item {{ $loop->last ? 'text-gray-700 font-medium' : 'text-gray-500' }}">
                                            @if (!$loop->last && $breadcrumb['url'] !== '#')
                                                <a class="transition-colors duration-200 hover:text-indigo-600"
                                                    href="{{ $breadcrumb['url'] }}">
                                                    {{ $breadcrumb['name'] }}
                                                </a>
                                            @else
                                                <span>{{ $breadcrumb['name'] }}</span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </nav>
                </div>
            </div>
        @endif
    </div>
</nav>
