@props(['title', 'count', 'color'])

<div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
    <p class="text-sm font-medium text-gray-500 mb-2">{{ $title }}</p>
    <p class="text-3xl font-bold text-{{ $color }}-600">{{ $count }}</p>
</div>