@props([
    'title' => 'Title',
    'value' => '0',
    'variant' => 'default' // default, success, warning, danger
])

@php
$bgColor = match($variant) {
    'success' => 'bg-green-50',
    'warning' => 'bg-yellow-50',
    'danger' => 'bg-red-50',
    default => 'bg-gray-50',
};
$textColor = match($variant) {
    'success' => 'text-green-700',
    'warning' => 'text-yellow-700',
    'danger' => 'text-red-700',
    default => 'text-gray-700',
};
$ringColor = match($variant) {
    'success' => 'ring-green-600/10',
    'warning' => 'ring-yellow-600/10',
    'danger' => 'ring-red-600/10',
    default => 'ring-gray-600/10',
};
@endphp

<div {{ $attributes->merge(['class' => "{$bgColor} {$ringColor} rounded-lg p-4 ring-1 ring-inset"]) }}>
    <p class="text-sm font-medium leading-6 text-gray-500">{{ $title }}</p>
    <p class="mt-2 text-3xl font-semibold tracking-tight {{ $textColor }}">{{ $value }}</p>
</div> 