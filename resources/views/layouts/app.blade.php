<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    <!-- Title -->
    <title>{{ config('app.name', 'Laravel') }} @hasSection('title') - @yield('title') @endif</title>

    @include('partials.cdn-fonts')

    <!-- Vite -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/components/mobile-menu.js'
    ])

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Sortable -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- Favicon -->
    <link type="image/x-icon" href="/resources/favicon.ico" rel="icon">

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="h-full font-sans antialiased">
    <div>
        <!-- Static sidebar for desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-[60] lg:flex lg:w-64 lg:flex-col">
             @include('partials.sidebar')
        </div>

        {{-- Removed lg:pl-64 --}} 
        <div class="flex flex-col flex-1 lg:pl-64 min-h-screen"> 
            {{-- Mobile Top Bar (lg:hidden) --}}
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:hidden">
                {{-- Hamburger Button --}}
                <button type="button" class="-m-2.5 p-2.5 text-gray-700" x-data @click="$dispatch('toggle-sidebar')">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                {{-- Logo (Mobile) --}}
                <div class="flex-1 text-center">
                     <a href="{{ route('home') }}">
                         <svg class="h-8 w-auto inline-block text-gray-900" viewBox="0 0 557 438" fill="none">
                            <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="currentColor"/>
                            <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="currentColor"/>
                            <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="currentColor"/>
                        </svg>
                    </a>
                </div>

                {{-- Profile Link/Avatar (Mobile) --}}
                <a href="{{ route('profile.show') }}">
                    <span class="sr-only">Your profile</span>
                    <x-profile.profile-picture :user="auth()->user()" size="xs" class="h-6 w-6 rounded-full bg-gray-50" />
                </a>
            </div>

            <main class="py-10 bg-white">
                <div class="px-6 sm:px-8 lg:px-10">
                    {{-- Main content --}}
                    @yield('content')
                </div>
            </main>
        </div>
        
        {{-- Mobile sidebar --}}
        <div x-data="{ sidebarOpen: false }" @toggle-sidebar.window="sidebarOpen = !sidebarOpen" x-show="sidebarOpen" class="relative z-[70] lg:hidden" role="dialog" aria-modal="true" x-cloak>
            {{-- Overlay --}}
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80"></div>
            
            <div class="fixed inset-0 flex">
                {{-- Sidebar Panel --}}
                <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative mr-16 flex w-full max-w-xs flex-1">
                    {{-- Close button --}}
                    <div x-show="sidebarOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    {{-- Sidebar content --}}
                    @include('partials.sidebar')
                </div>
            </div>
        </div>

    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
