@extends('layouts.app')

@section('content')
    <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="card animate-slide-in mb-8 p-4 sm:p-8">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row sm:items-center">
                <div class="text-center sm:text-left">
                    <h2 class="heading-1 text-2xl sm:text-3xl">Notifications</h2>
                    <p class="mt-1 text-sm text-gray-600 sm:text-base">Stay updated with your claims status and activities
                    </p>
                </div>
                <form class="w-full sm:w-auto" action="{{ route('notifications.mark-all-as-read') }}" method="POST">
                    @csrf
                    <button class="btn-secondary inline-flex w-full items-center justify-center gap-2 text-sm sm:w-auto"
                        type="submit">
                        <span>Mark all as read</span>
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="mb-6 flex flex-wrap justify-center gap-4 sm:justify-between">
            <!-- Total Notifications -->
            <div class="stats-pill animate-fade-in">
                <span class="stats-label text-xs">Total</span>
                <span class="stats-value text-sm text-gray-900">{{ auth()->user()->notifications->count() }}</span>
            </div>

            <!-- Unread Notifications -->
            <div class="stats-pill animate-fade-in">
                <span class="stats-label text-xs">Unread</span>
                <span class="stats-value text-sm text-yellow-600">{{ auth()->user()->unreadNotifications->count() }}</span>
            </div>

            <!-- Read Notifications -->
            <div class="stats-pill animate-fade-in">
                <span class="stats-label text-xs">Read</span>
                <span class="stats-value text-sm text-green-600">{{ auth()->user()->readNotifications->count() }}</span>
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
