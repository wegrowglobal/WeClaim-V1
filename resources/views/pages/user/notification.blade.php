@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex flex-row justify-between items-center mb-6">
        <h2 class="heading-1 font-bold text-gray-900">Notifications</h2>
        <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="">
            @csrf
            <button type="submit" class="text-xs flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-700 transition-colors">
                <span>Mark all as read</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="" height="" fill="currentColor" class="icon-large" viewBox="0 0 16 16">
                    <path d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992zm-.92 5.14.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486z"/>
                </svg>
            </button>
        </form>
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="">
            <x-notifications.list :notifications="auth()->user()->notifications()->paginate(10)" />
        </div>
    </div>

    <div class="mt-6">
        {{ auth()->user()->notifications()->paginate(10)->links() }}
    </div>
</div>
@endsection