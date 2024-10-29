@extends('layouts.app')

@section('content')
<div class="w-full p-4">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ $message }}</h2>
        <a href="{{ route('claims.approval') }}" class="text-blue-600 hover:text-blue-800">Back to Claims Approval</a>
    </div>
</div>
@endsection 