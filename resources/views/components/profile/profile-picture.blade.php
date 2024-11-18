@props(['user', 'size' => 'md', 'classes' => ''])

@php
$sizes = [
    'sm' => 'h-8 w-8',
    'md' => 'h-16 w-16',
    'lg' => 'h-24 w-24'
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="{{ $sizeClass }} {{ $classes }}">
    @if($user->profile_picture && Storage::disk('public')->exists($user->profile_picture))
        <img src="{{ asset('storage/' . $user->profile_picture) }}" 
             alt="{{ $user->first_name }}'s profile" 
             class="w-full h-full object-cover rounded-full">
    @else
        <div class="w-full h-full rounded-full flex items-center justify-center text-xl font-bold text-white bg-gradient-to-br from-indigo-600 to-indigo-800">
            {{ strtoupper(substr($user->first_name, 0, 1)) }}
        </div>
    @endif
</div>