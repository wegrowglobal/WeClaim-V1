@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Notifications</h2>
        <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="mt-4 sm:mt-0">
            @csrf
            <button type="submit" class="btn btn-primary">Mark All as Read</button>
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