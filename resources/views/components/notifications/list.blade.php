@props(['notifications'])

<div class="divide-y divide-gray-100">
    @forelse ($notifications as $notification)
        <div class="p-4 hover:bg-gray-50 transition-colors {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/30' }}">
            <div class="flex items-start space-x-4">
                <!-- Notification Icon -->
                <div class="flex-shrink-0">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $notification->read_at ? 'bg-gray-100' : 'bg-indigo-100' }}">
                        @switch($notification->type)
                            @case('App\Notifications\ClaimStatusChanged')
                                <svg class="h-5 w-5 {{ $notification->read_at ? 'text-gray-600' : 'text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                @break
                            @default
                                <svg class="h-5 w-5 {{ $notification->read_at ? 'text-gray-600' : 'text-indigo-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                        @endswitch
                    </div>
                </div>

                <!-- Notification Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium {{ $notification->read_at ? 'text-gray-900' : 'text-indigo-900' }}">
                            {{ $notification->data['title'] ?? 'Notification' }}
                        </p>
                        <span class="text-xs text-gray-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">
                        {{ $notification->data['message'] ?? 'No message available' }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex-shrink-0 flex items-center space-x-2">
                    @if(!$notification->read_at)
                        <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center p-1 text-indigo-600 hover:text-indigo-900 transition-colors"
                                    title="Mark as read">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </form>
                    @endif
                    
                    @if($notification->data['action_url'] ?? false)
                        <a href="{{ $notification->data['action_url'] }}" 
                           class="inline-flex items-center p-1 text-gray-400 hover:text-gray-600 transition-colors"
                           title="View details">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="p-8 text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <p class="mt-4 text-sm font-medium text-gray-900">No notifications</p>
            <p class="mt-1 text-sm text-gray-500">You're all caught up! Check back later for new updates.</p>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
