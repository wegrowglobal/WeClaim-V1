<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>

    @include('partials.navbar')

    <!-- Hamburger Menu with Icon SVG -->
    
    <button id="hamburger-menu" class="z-50 hidden hamburger-menu">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon-large">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <!-- Mobile Menu -->
    @include('partials.mobile-menu')

    <!-- Main Content -->
    <main class="content-container min-h-screen bg-white py-4 px-4 md:py-8 md:px-0">
        <div class="max-w-8xl mx-auto px-0 md:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    @include('partials.footer')


    <!-- Scripts -->
    @stack('scripts')

</body>
</html>
