@extends('layouts.auth')

@section('title', 'Login - WeClaim')

@section('content')
    <div class="w-full max-w-md mx-auto px-6 py-12">
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}">
                <svg class="mx-auto h-12 w-auto text-black" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="currentColor"/>
                    <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="currentColor"/>
                    <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="currentColor"/>
                </svg>
            </a>
        </div>

        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-black">Sign In</h1>
            <p class="mt-2 text-sm text-gray-700">Welcome back. Please enter your credentials.</p>
        </div>
        
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 border border-green-200">
                <p class="font-medium text-sm text-green-700">{{ session('status') }}</p>
            </div>
        @endif

        {{-- Display General Errors (Not specific to email/password fields) --}}
        @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
            <div class="mb-4 rounded-md border border-red-600 bg-red-50 p-3">
                 <p class="font-medium text-sm text-red-700">{{ $errors->all()[0] }}</p> {{-- Display the first general error --}}
            </div>
        @endif

        <form id="login-form" class="space-y-6" method="POST" action="{{ route('login') }}">
            @csrf
            
            <div>
                <label class="mb-1 block text-sm font-medium text-black" for="email">Email</label>
                <input class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('email') border-red-600 @enderror"
                    id="email" 
                    name="email" 
                    type="email" 
                    value="{{ old('email') }}" 
                    placeholder="your@email.com"
                    required>
                {{-- Display errors specifically for email --}}
                @error('email')
                    {{-- <span class="mt-1 text-xs font-medium text-red-600 block">{{ $message }}</span> --}}
                    <div class="mt-1 flex items-center text-sm text-red-600">
                      <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                      <span class="font-medium">{{ $message }}</span>
                    </div>
                 @enderror
            </div>

            <div class="relative">
                <label class="mb-1 block text-sm font-medium text-black" for="password">Password</label>
                <div class="flex items-center">
                    <input class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 pr-10 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('password') border-red-600 @enderror"
                        id="password" 
                        name="password" 
                        type="password" 
                        placeholder="••••••••"
                        required>
                    <button type="button" 
                            onclick="togglePasswordVisibility('password')" 
                            class="-ml-10 flex items-center justify-center p-2 text-gray-400 hover:text-gray-600 rounded-md focus:outline-none focus:ring-1 focus:ring-black">
                        <svg id="password-toggle-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
                {{-- Display errors specifically for password --}}
                @error('password')
                     {{-- <span class="mt-1 text-xs font-medium text-red-600 block">{{ $message }}</span> --}}
                     <div class="mt-1 flex items-center text-sm text-red-600">
                       <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                       <span class="font-medium">{{ $message }}</span>
                     </div>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="hidden" name="remember" value="0">
                    <input class="h-4 w-4 rounded border-gray-300 text-black focus:ring-black focus:ring-1 focus:ring-offset-0"
                        id="remember_checkbox"
                        name="_remember_visual"
                        type="checkbox"
                        onchange="document.querySelector('input[name=remember]').value = this.checked ? '1' : '0'">
                    <label class="ml-2 text-sm text-black" for="remember_checkbox">
                        Remember Me
                    </label>
                </div>
                <a class="text-sm font-medium text-black hover:underline"
                    href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            </div>

            <div>
                <button id="login-button" class="flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"
                    type="submit">
                    <span id="login-text">Log In</span>
                    <svg id="login-spinner" class="animate-spin ml-2 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>

        <div class="mt-8 text-center">
            <p class="text-sm text-gray-700">
                Don't have an account?
                <a class="font-medium text-black hover:underline"
                    href="{{ route('register.request.create') }}">
                    Request one
                </a>
            </p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById('password-toggle-icon');
    
    if (!input || !icon) return;

    if (input.type === "password") {
        input.type = "text";
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        `;
    } else {
        input.type = "password";
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        `;
    }
}
</script>
@endpush
