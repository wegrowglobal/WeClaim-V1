@props(['notifications'])

<div class="divide-y divide-gray-100">
    @forelse ($notifications as $notification)
        @php
            $data = $notification->data;
            $iconColor = $notification->read_at ? 'bg-gray-100' : 'bg-indigo-100';
            $textColor = $notification->read_at ? 'text-gray-600' : 'text-indigo-600';
        @endphp

        <div class="p-4 hover:bg-gray-50 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/30' }}">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 rounded-full {{ $iconColor }} flex items-center justify-center">
                        @if($data['is_owner'])
                            <svg class="h-5 w-5 {{ $textColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5 {{ $textColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium {{ $notification->read_at ? 'text-gray-900' : 'text-indigo-900' }}">
                            {{ $data['message'] }}
                        </p>
                        <span class="text-xs text-gray-500 shrink-0">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    
                    @if($data['url'])
                        <div class="mt-2">
                            <a href="{{ $data['url'] }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                View Details
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>

                @if(!$notification->read_at)
                    <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="p-1 text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="p-8 text-center">
            <div class="mx-auto h-12 w-12 text-gray-400">
                <svg class="h-full w-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-4 text-sm text-gray-500">No new notifications</p>
        </div>
    @endforelse

    @if($notifications->hasPages())
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
