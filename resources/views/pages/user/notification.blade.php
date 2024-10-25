@extends('layouts.app')

@section('content')
    <div class="max-w-full-custom border border-wgg-border">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-10 space-y-4">
                <h2 class="heading-1">Notifications</h2>

                <x-notifications.statistics />

                <div class="space-y-4">
                    <x-notifications.list :notifications="auth()->user()->notifications" />
                </div>
            </div>
        </div>
    </div>
@endsection