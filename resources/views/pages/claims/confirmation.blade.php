@extends('layouts.app')

@section('content')
    <div class="w-full p-4">
        <div class="rounded-lg bg-white p-6 shadow-md">
            <h2 class="mb-4 text-2xl font-bold text-gray-900">{{ $message }}</h2>
            <a class="text-blue-600 hover:text-blue-800" href="{{ route('claims.approval') }}">Back to Claims Approval</a>
        </div>
    </div>
@endsection
