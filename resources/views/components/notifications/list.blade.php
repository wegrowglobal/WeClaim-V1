@props(['notifications'])

<div class="divide-y divide-gray-100">
    @forelse($notifications as $notification)
        <div class="py-4 first:pt-0 last:pb-0">
            <x-notifications.item :notification="$notification" />
        </div>
    @empty
        <div class="py-12 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-900 mb-1">No notifications</h3>
            <p class="text-sm text-gray-500">You're all caught up! Check back later for new updates.</p>
        </div>
    @endforelse
</div>