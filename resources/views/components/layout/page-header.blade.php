@props([
    'title' => '', // Default title
    'subtitle' => '', // Default subtitle
])

<div {{ $attributes->merge(['class' => 'mb-8 border-b border-gray-200 pb-5']) }}>
    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            @if($title)
                <h1 class="text-2xl font-semibold leading-6 text-gray-900">{{ $title }}</h1>
            @endif
            @if($subtitle)
                <p class="mt-2 text-sm text-gray-700">{{ $subtitle }}</p>
            @endif
        </div>
        <div>
            {{-- Slot for action buttons or other content --}}
            {{ $slot }}
        </div>
    </div>
</div> 