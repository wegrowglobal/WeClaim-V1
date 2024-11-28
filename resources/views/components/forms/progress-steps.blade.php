@props(['currentStep'])

@php
    $steps = [
        1 => [
            'label' => 'Basic Details',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'description' => 'Personal and vehicle information',
        ],
        2 => [
            'label' => 'Trip Details',
            'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
            'description' => 'Route planning and distance calculation',
        ],
        3 => [
            'label' => 'Documents',
            'icon' =>
                'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
            'description' => 'Upload supporting documents',
        ],
    ];
@endphp

<div class="relative">
    <!-- Steps Container -->
    <div class="relative flex items-center justify-between">
        @foreach ($steps as $step => $details)
            <div class="relative flex flex-1 flex-col items-center">
                <!-- Connector Line -->
                @if ($step < count($steps))
                    <div
                        class="{{ $step < $currentStep ? 'bg-indigo-600' : 'bg-gray-200' }} absolute left-1/2 top-5 h-0.5 w-full transition-colors duration-300">
                    </div>
                @endif

                <!-- Step Circle with Animation -->
                <div class="relative z-10">
                    @if ($step === $currentStep)
                        <!-- Pulse Animation for Current Step -->
                        <div
                            class="absolute -inset-2 animate-[ping_2s_ease-in-out_infinite] rounded-full bg-indigo-100/60">
                        </div>
                    @endif

                    <div
                        class="{{ $step < $currentStep
                            ? 'bg-indigo-600'
                            : ($step === $currentStep
                                ? 'bg-indigo-600 ring-2 sm:ring-4 ring-indigo-100'
                                : 'bg-gray-200') }} relative flex h-8 w-8 items-center justify-center rounded-full transition-all duration-300 sm:h-10 sm:w-10">

                        @if ($step < $currentStep)
                            <!-- Completed Step -->
                            <svg class="h-4 w-4 text-white sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        @elseif($step === $currentStep)
                            <!-- Current Step -->
                            <svg class="h-4 w-4 text-white sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $details['icon'] }}" />
                            </svg>
                        @else
                            <!-- Future Step -->
                            <svg class="h-4 w-4 text-gray-400 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $details['icon'] }}" />
                            </svg>
                        @endif
                    </div>
                </div>

                <!-- Step Label and Description -->
                <div class="mt-2 flex flex-col items-center sm:mt-3">
                    <span
                        class="{{ $step <= $currentStep ? 'text-indigo-600' : 'text-gray-500' }} text-xs font-medium transition-colors duration-300 sm:text-sm">
                        {{ $details['label'] }}
                    </span>
                    <span class="mt-1 hidden text-center text-[10px] text-gray-500 sm:block sm:text-xs">
                        {{ $details['description'] }}
                    </span>
                </div>

                <!-- Current Step Indicator -->
                @if ($step === $currentStep)
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 sm:-top-7">
                        <span
                            class="inline-flex items-center rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-medium text-indigo-800 sm:px-2.5 sm:text-xs">
                            Step {{ $step }}/{{ count($steps) }}
                        </span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
