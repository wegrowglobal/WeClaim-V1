@props(['user', 'size' => 'md', 'classes' => ''])

@php
    $sizes = [
        'sm' => 'h-8 w-8',
        'md' => 'h-16 w-16',
        'lg' => 'h-24 w-24',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    // Add debugging
    $fileExists = $user->profile_picture && file_exists(public_path($user->profile_picture));
    $imagePath = $fileExists ? asset($user->profile_picture) : null;

    Log::info('Profile picture component render', [
        'user_id' => $user->id,
        'profile_picture_path' => $user->profile_picture,
        'file_exists' => $fileExists,
        'image_path' => $imagePath,
    ]);
@endphp

<div class="{{ $sizeClass }} {{ $classes }}">
    <div class="h-full w-full overflow-hidden rounded-full">
        @if ($fileExists)
            <img class="h-full w-full object-cover" src="{{ $imagePath }}" alt="{{ $user->first_name }}'s profile"
                onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-indigo-600 to-indigo-800 text-xl font-bold text-white">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}
            </div>
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-600 to-indigo-800 text-lg font-bold text-white">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}
            </div>
        @endif
    </div>
</div>
