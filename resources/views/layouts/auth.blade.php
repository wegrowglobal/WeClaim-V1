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
    @vite(['resources/css/app.css'])
    
    @stack('styles')
</head>
<body class="bg-gray-100 antialiased">
    <div class="flex">
        @yield('content')
    </div>
    
    @stack('scripts')
</body>
</html>