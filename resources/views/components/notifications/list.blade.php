@props(['notifications'])

<div class="space-y-4">
    @forelse($notifications as $notification)
        <x-notifications.item :notification="$notification" />
    @empty
        <div class="bg-white p-6 rounded-lg border border-gray-200 text-center">
            <p class="text-gray-500">No notifications found</p>
        </div>
    @endforelse
</div>