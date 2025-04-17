@props([
    'title' => '',
    'subtitle' => '',
    'currentStep' => 1,
    'totalSteps' => 3,
])

{{-- Step Header Card --}}
<div {{ $attributes->merge(['class' => 'mb-8 bg-white border border-gray-200 overflow-hidden rounded-lg']) }}>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            {{-- Title and Subtitle --}}
            <div>
                <h3 class="text-lg font-semibold leading-6 text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            {{-- Step Counter --}}
            <div class="mt-4 sm:mt-0 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-500">Step</span>
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-black text-xs font-medium text-white">
                        {{ $currentStep }}/{{ $totalSteps }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 