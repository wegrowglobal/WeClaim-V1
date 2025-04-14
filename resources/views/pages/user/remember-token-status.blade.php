@extends('layouts.app')

@section('title', 'Remember Me Status - WeClaim')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Remember Me Status</h1>
        <p class="mt-1 text-sm text-gray-600">Information about your current authentication session</p>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden p-6">
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Session Status</h2>
                <p class="mt-1 text-sm text-gray-600">This shows information about your current login session</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700">Remember Token Status</p>
                    <div class="mt-2 flex items-center">
                        @if($hasRememberToken)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                            <span class="ml-2 text-sm text-gray-500">You have an active "Remember Me" token</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Not Active
                            </span>
                            <span class="ml-2 text-sm text-gray-500">No "Remember Me" token is stored</span>
                        @endif
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700">Authentication Method</p>
                    <div class="mt-2 flex items-center">
                        @if($isRemembered)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Remember Token
                            </span>
                            <span class="ml-2 text-sm text-gray-500">You were logged in via "Remember Me"</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Manual Login
                            </span>
                            <span class="ml-2 text-sm text-gray-500">You manually logged in this session</span>
                        @endif
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700">Session Lifetime</p>
                    <div class="mt-2">
                        <span class="text-sm text-gray-500">Without "Remember Me", your session will expire after {{ $sessionLifetime }} minutes of inactivity</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">How "Remember Me" Works</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>
                                When you check "Remember Me" during login, a secure token is stored in your browser as a cookie. This allows you to stay logged in even after closing your browser. For security, you should only use "Remember Me" on trusted personal devices.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 