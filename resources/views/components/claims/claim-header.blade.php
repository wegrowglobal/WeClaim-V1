@props(['title', 'subtitle', 'status'])

<div class="mb-8 animate-slide-in">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        </div>
        <x-claims.status-badge :status="$status" class="!text-sm" />
    </div>
</div> 