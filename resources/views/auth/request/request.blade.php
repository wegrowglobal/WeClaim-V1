@extends('layouts.auth')

@section('title', 'Request Account - WeClaim')

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
        <h1 class="text-2xl font-bold text-black">Request Account</h1>
        <p class="mt-2 text-sm text-gray-700">Fill in your details to request an account.</p>
    </div>

    {{-- Display ALL validation errors --}}
    @if ($errors->any())
        <div class="mb-4 rounded-md border border-black p-3"> {{-- Simple border, no bg --}}
            <ul class="list-inside text-sm text-black">
                @foreach ($errors->all() as $error)
                    <li class="font-medium">{{ $error }}</li> {{-- Make error text bold --}}
                @endforeach
            </ul>
        </div>
    @endif

    <form class="space-y-5" method="POST" action="{{ route('register.request.store') }}">
        @csrf

        <div>
            <label class="mb-1 block text-sm font-medium text-black" for="first_name">First Name</label>
            <input class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('first_name') border-red-600 @enderror"
                id="first_name"
                name="first_name"
                type="text"
                value="{{ old('first_name') }}"
                placeholder="Enter your first name"
                required>
            @error('first_name')
                <div class="mt-1 flex items-center text-sm text-red-600">
                  <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <span class="font-medium">{{ $message }}</span>
                </div>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-black" for="second_name">Second Name</label>
            <input class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('second_name') border-red-600 @enderror"
                id="second_name"
                name="second_name"
                type="text"
                value="{{ old('second_name') }}"
                placeholder="Enter your second name"
                required>
            @error('second_name')
                <div class="mt-1 flex items-center text-sm text-red-600">
                  <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <span class="font-medium">{{ $message }}</span>
                </div>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-black" for="email">Email</label>
            <input class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('email') border-red-600 @enderror"
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="your@email.com"
                required>
            @error('email')
                <div class="mt-1 flex items-center text-sm text-red-600">
                  <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <span class="font-medium">{{ $message }}</span>
                </div>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-black" for="role_id">Role</label>
            <select class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('role_id') border-red-600 @enderror"
                id="role_id"
                name="role_id"
                required>
                <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Select Role</option>
                @foreach($roles as $id => $roleName)
                    <option value="{{ $id }}" {{ old('role_id') == $id ? 'selected' : '' }}>{{ $roleName }}</option>
                @endforeach
            </select>
            @error('role_id')
                <div class="mt-1 flex items-center text-sm text-red-600">
                  <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <span class="font-medium">{{ $message }}</span>
                </div>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-black" for="department_id">Department</label>
            <select class="block w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm transition focus:border-black focus:outline-none focus:ring-1 focus:ring-black @error('department_id') border-red-600 @enderror"
                id="department_id"
                name="department_id"
                required>
                <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>Select Department</option>
                @foreach($departments as $id => $departmentName)
                    <option value="{{ $id }}" {{ old('department_id') == $id ? 'selected' : '' }}>{{ $departmentName }}</option>
                @endforeach
            </select>
            @error('department_id')
                <div class="mt-1 flex items-center text-sm text-red-600">
                  <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <span class="font-medium">{{ $message }}</span>
                </div>
            @enderror
        </div>

        <button class="flex w-full items-center justify-center rounded-md bg-black px-4 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2"
            type="submit">
            Submit Request
        </button>
    </form>

    <div class="mt-8 text-center">
        <p class="text-sm text-gray-700">
            Already have an account?
            <a class="font-medium text-black hover:underline"
                href="{{ route('login') }}">
                Sign in
            </a>
        </p>
    </div>
</div>
@endsection
