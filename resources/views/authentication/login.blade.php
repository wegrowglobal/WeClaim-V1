@include('components.header')

<div class="min-h-screen flex items-center justify-center bg-gradient-to-r from-wgg-black-950 to-black">
    <div class="bg-white p-10 rounded-xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <svg width="60" height="48" viewBox="0 0 557 438" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4">
                <path d="M202.74 76.0002L126.74 152L177.407 202.667L228.074 253.333L240.74 240.667L253.407 228L215.407 190L177.407 152L240.74 88.6668L304.074 25.3335L291.407 12.6668L278.74 0.000149548L202.74 76.0002Z" fill="#0A0A0A"/>
                <path d="M278.741 101.333L228.074 152L253.408 177.333L278.741 202.667L291.408 190L304.074 177.333L291.408 164.667L278.741 152L303.808 126.933L328.741 102L353.674 127.067L378.741 152L341.674 189.067C321.408 209.333 304.741 226.667 304.741 227.333C304.741 228.133 310.341 234.267 317.141 241.067L329.408 253.333L380.074 202.667L430.741 152L380.074 101.333L329.408 50.6668L278.741 101.333Z" fill="#0A0A0A"/>
                <path d="M12.7409 266L0.0742188 278.666L76.0742 354.666L152.074 430.667L215.408 367.333L278.741 304L341.808 366.933L404.741 430L480.741 354L556.741 278L544.074 265.333L531.408 252.666L468.074 316L404.741 379.333L341.408 316L278.074 252.666L215.008 315.733L152.074 378.667L89.4076 316C55.0076 281.6 26.4742 253.333 26.0742 253.333C25.6742 253.333 19.6742 259.066 12.7409 266Z" fill="#0A0A0A"/>
            </svg>
            <h1 class="text-3xl font-bold text-gray-800">Welcome Back</h1>
            <p class="text-wgg-black-600 mt-2">Sign in to your account</p>
        </div>

        <form method="POST" class="space-y-6" action="{{ route('login') }}">
            @csrf
            <div class="relative">
                <input id="email" value="{{ old('email') }}" name="email" placeholder=" " class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-wgg-black-950 focus:border-wgg-border transition duration-150 ease-in-out peer" type="email" required>
                <label for="email" class="absolute left-2 top-2 text-sm font-medium text-wgg-black-600 transition-all duration-300 transform -translate-y-4 scale-75 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75">Email</label>
            </div>

            <div class="relative">
                <input id="password" name="password" placeholder=" " class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-wgg-border focus:ring-1 focus:ring-wgg-black-950 transition duration-150 ease-in-out peer" type="password" required>
                <label for="password" class="absolute left-2 top-2 text-sm font-medium text-wgg-black-600 transition-all duration-300 transform -translate-y-4 scale-75 z-10 origin-[0] bg-white px-2 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:-translate-y-4 peer-focus:scale-75">Password</label>
            </div>

            @error('password')
            <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div>
                <button class="w-full py-3 px-5 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-wgg-black-950 hover:bg-wgg-black-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out" type="submit">Sign In</button>
            </div>
        </form>

        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm">
                <a href="#" class="font-medium text-wgg-black-600 hover:text-wgg-black-800">Forgot Your Password?</a>
            </div>
            <div class="text-sm">
                <a href="#" class="font-semibold underline text-wgg-black-600 hover:text-wgg-black-800">Request an Account</a>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Developer Tools</h2>
            <div class="flex justify-between">
                <a class="text-sm font-medium text-wgg-black-600 hover:text-wgg-black-800" href="{{ route('home') }}">Go Home</a>
                <a class="text-sm font-medium text-wgg-black-600 hover:text-wgg-black-800" href="{{ route('home') }}">Clear Token Cookies</a>
            </div>
        </div>
    </div>
</div>

@include('components.footer')
