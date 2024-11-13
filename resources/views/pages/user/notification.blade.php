@extends('layouts.app')

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="card p-8 mb-8 animate-slide-in">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="heading-1">Notifications</h2>
                <p class="text-gray-600 mt-1">Stay updated with your claims status and activities</p>
            </div>
            <form action="{{ route('notifications.mark-all-as-read') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="btn-secondary inline-flex items-center gap-2 text-sm">
                    <span>Mark all as read</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <!-- Total Notifications -->
        <div class="stats-card animate-slide-in delay-100">
            <span class="stats-label">Total</span>
            <span class="stats-value text-gray-900">{{ auth()->user()->notifications->count() }}</span>
        </div>

        <!-- Unread Notifications -->
        <div class="stats-card animate-slide-in delay-200">
            <span class="stats-label">Unread</span>
            <span class="stats-value text-yellow-600">{{ auth()->user()->unreadNotifications->count() }}</span>
        </div>

        <!-- Read Notifications -->
        <div class="stats-card animate-slide-in delay-300">
            <span class="stats-label">Read</span>
            <span class="stats-value text-green-600">{{ auth()->user()->readNotifications->count() }}</span>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card animate-slide-in delay-400">
        <div class="divide-y divide-gray-100">
            <x-notifications.list :notifications="auth()->user()->notifications()->paginate(10)" />
        </div>
    </div>
</div>
@endsection