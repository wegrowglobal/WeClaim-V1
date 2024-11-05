@extends('layouts.auth')

@section('title', 'Claim Action Status - WeClaim')

@section('content')
<div class="min-h-screen w-full flex items-center justify-center bg-gradient-to-r from-wgg-black-950 to-black">
    <div class="bg-white p-10 rounded-none md:rounded-xl shadow-2xl w-full max-w-md h-full md:h-auto">
        <div class="text-center mb-8">
            @if(isset($alreadyProcessed) && $alreadyProcessed)
                <svg width="60" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4">
                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="#0A0A0A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h1 class="heading-1">Action Not Available</h1>
                <p class="text-wgg-black-600 mt-2">{{ $message ?? 'This claim is not available for processing.' }}</p>
            @elseif(isset($success) && $success)
                <svg width="60" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#0A0A0A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h1 class="heading-1">Success!</h1>
                <p class="text-wgg-black-600 mt-2">{{ $message ?? 'Action completed successfully.' }}</p>
            @else
                <svg width="60" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4">
                    <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="#0A0A0A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h1 class="heading-1">Oops!</h1>
                <p class="text-wgg-black-600 mt-2">{{ $message ?? 'An error occurred while processing your request.' }}</p>
            @endif
        </div>

        @if((isset($success) && $success || isset($alreadyProcessed)) && isset($claim))
            <div class="space-y-6">
                <div class="relative">
                    <div class="form-input-base">
                        <span class="text-sm text-wgg-black-600">Claim ID</span>
                        <br>
                        <span class="font-medium">#{{ $claim->id }}</span>
                    </div>
                </div>

                <div class="relative">
                    <div class="form-input-base">
                        <span class="text-sm text-wgg-black-600">Status</span>
                        <br>
                        <span class="font-medium">{{ str_replace('_', ' ', $claim->status) }}</span>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
