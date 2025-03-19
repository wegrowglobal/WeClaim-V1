@props(['title', 'subtitle', 'status'])

<div class="mb-4 sm:mb-6 lg:mb-8 animate-slide-in">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        </div>
        <x-claims.status-badge :status="$status" class="!text-sm self-start sm:self-center" />
    </div>
</div> 