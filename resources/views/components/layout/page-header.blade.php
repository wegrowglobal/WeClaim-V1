@props([
    'title' => '', // Default title
    'subtitle' => '', // Default subtitle
])

{{-- Card container --}}
<div {{ $attributes->merge(['class' => 'mb-8 bg-white border border-gray-200 overflow-hidden rounded-lg']) }}>
    <div class="px-4 py-5 sm:p-6"> {{-- Card padding --}}
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <div class="flex items-center gap-3">
                    @if($title)
                        <h1 class="text-2xl font-semibold leading-6 text-gray-900">{{ $title }}</h1>
                    @endif
                </div>
                @if($subtitle)
                    <p class="mt-2 text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0"> {{-- Adjusted margin for action slot --}}
                {{-- Slot for action buttons or other content --}}
                {{ $slot }}
            </div>
        </div>
    </div>
</div> 