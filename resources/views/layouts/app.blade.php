<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>@yield('title', 'WeClaim')</title>

    @include('partials.cdn-fonts')

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/mobile-menu.js'])
    

</head>
<body>

    <div class="flex relative">


        <!-- Hamburger Menu with Icon SVG -->
        <button id="hamburger-menu" class="z-50 hidden hamburger-menu">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <!-- Mobile Menu -->
        @include('partials.mobile-menu')

        <!-- Navigation Sidebar -->
        <div class="navbar-container">
           @include('partials.navbar')
        </div>

        <!-- Content Here -->
        <div class="content-container @yield('content-class')">
            @yield('content')
        </div>

        <!-- Footer -->
        @include('partials.footer')

    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

</body>
</html>
