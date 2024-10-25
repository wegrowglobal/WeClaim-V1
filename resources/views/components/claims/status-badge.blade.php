@props(['value'])

@php
$colors = [
    'submitted' => 'bg-blue-100 text-blue-800',
    'approved' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800',
    'cancelled' => 'bg-gray-100 text-gray-800',
];

$status = strtolower($value);
$colorClass = $colors[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
    {{ ucfirst($value) }}
</span>
