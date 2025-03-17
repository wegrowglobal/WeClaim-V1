@extends('layouts.auth')

@section('title', 'Login - WeClaim')

@section('content')
    <div
        class="flex min-h-[100dvh] w-full items-center justify-center bg-gray-100 px-4 py-8 md:py-0">
        <div class="flex w-full flex-col items-center justify-center gap-8 md:flex-row md:gap-8 md:px-8 max-w-6xl">
            <div class="w-full max-w-md">
                <x-auth.login-form />
                
                <!-- Mobile Changelog Button -->
                <div class="mt-6 flex justify-center md:hidden">
                    <button 
                        type="button" 
                        onclick="toggleChangelog()"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        View What's New
                    </button>
                </div>
            </div>
            
            <!-- Desktop Changelog -->
            <div class="w-full max-w-md hidden md:block">
                <livewire:auth.changelog-feed />
            </div>
            
            <!-- Mobile Changelog Modal -->
            <div id="mobileChangelogModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 md:hidden hidden">
                <div class="absolute inset-0 bg-black bg-opacity-50" onclick="toggleChangelog()"></div>
                <div class="relative w-full max-w-md z-10">
                    <livewire:auth.changelog-feed />
                </div>
            </div>
        </div>
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
        
        function toggleChangelog() {
            const modal = document.getElementById('mobileChangelogModal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = ''; // Re-enable scrolling
            }
        }
    </script>

@endsection
