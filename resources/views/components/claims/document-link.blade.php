@props(['url'])

<a href="{{ $url }}" target="_blank" class="text-blue-600 hover:text-blue-900">
    {{ $slot }}
</a>
