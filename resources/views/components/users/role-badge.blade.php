@props(['role'])

@php
    $colors = [
        1 => 'bg-blue-100 text-blue-800',        // Staff
        2 => 'bg-purple-100 text-purple-800',    // Manager
        3 => 'bg-green-100 text-green-800',      // HR
        4 => 'bg-yellow-100 text-yellow-800',    // Finance
        5 => 'bg-red-100 text-red-800',          // Admin/SU
        // Add other role IDs and their corresponding Tailwind classes
    ];
    $colorClass = $colors[$role->id] ?? 'bg-gray-100 text-gray-800'; // Default
@endphp

<span class="inline-flex items-center rounded-full {{ $colorClass }} px-2.5 py-0.5 text-xs font-medium">
    {{ $role->name }}
</span> 