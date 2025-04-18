@props(['currentStep'])

<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-100 px-3 py-2 sm:px-4 sm:py-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
            <div class="flex items-center space-x-2 sm:space-x-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">Progress</p>
                    <p class="text-xs text-gray-500">Complete all steps to submit your claim</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs sm:text-sm font-medium text-gray-700">Step</span>
                <div class="flex h-6 w-6 sm:h-7 sm:w-7 items-center justify-center rounded-full bg-indigo-600 px-2 text-xs sm:text-sm font-medium text-white">
                    {{ $currentStep }}/3
                </div>
            </div>
        </div>
    </div>
</div>
