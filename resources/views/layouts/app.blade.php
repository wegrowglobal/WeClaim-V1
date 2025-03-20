<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    <!-- Title -->
    <title>@yield('title', 'WeClaim')</title>

    @include('partials.cdn-fonts')

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/mobile-menu.js'])

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
</head>

<body class="flex flex-col min-h-screen bg-gray-50">

    @include('partials.navbar')

    <!-- Hamburger Menu with Icon SVG -->

    <button class="hamburger-menu z-50 hidden" id="hamburger-menu">
        <svg class="icon-large" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <!-- Mobile Menu -->
    @include('partials.mobile-menu')

    <!-- Main Content -->
    <main class="content-container flex-grow bg-white px-4 py-4 md:px-6 md:py-8">
        <div class="max-w-8xl mx-auto w-full">
            @yield('content')
        </div>
    </main>

    <!-- Incomplete Profile Alert -->
    <x-alerts.incomplete-profile />

    @stack('modals')
    @stack('scripts')

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.userId = {{ Auth::id() }};
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>

</html>
