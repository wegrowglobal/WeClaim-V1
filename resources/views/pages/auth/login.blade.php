@extends('layouts.auth')

@section('title', 'Login - WeClaim')

@section('content')
    <div
        class="flex min-h-[100dvh] w-full flex-col items-center justify-center gap-6 bg-gray-100 px-4 py-8 md:flex-row md:py-0">
        <x-auth.login-form />
        <livewire:auth.changelog-feed />
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const showPasswordIcon = document.getElementById('showPasswordIcon');
            const hidePasswordIcon = document.getElementById('hidePasswordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showPasswordIcon.classList.add('hidden');
                hidePasswordIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                showPasswordIcon.classList.remove('hidden');
                hidePasswordIcon.classList.add('hidden');
            }
        }
    </script>

@endsection
