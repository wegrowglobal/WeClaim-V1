@props(['title', 'description', 'position' => 'right'])

<div class="fixed {{ $position === 'right' ? 'right-4' : 'left-4' }} bottom-4 w-72 bg-white rounded-lg shadow-lg border border-gray-200 p-4 space-y-2 animate-slide-in z-50">
    <div class="flex items-start justify-between">
        <div class="flex items-center space-x-2">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-900">{{ $title }}</h3>
        </div>
        <button onclick="this.closest('div.fixed').remove()" class="flex-shrink-0 ml-2">
            <svg class="h-4 w-4 text-gray-400 hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <p class="text-xs text-gray-500">{{ $description }}</p>
</div>