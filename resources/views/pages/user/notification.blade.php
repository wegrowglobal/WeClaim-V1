@php
use App\Services\ClaimService;
use App\Models\Claim;
@endphp

<x-layout>
    <div class="max-w-full-custom border border-wgg-border">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-10 space-y-4">
                <h2 class="heading-1">Notifications</h2>

                <!-- Notifications Statistics -->
                <div class="space-y-4">
                    <h3 class="heading-2">Notifications Overview</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                            <p class="text-sm font-medium text-gray-500 mb-2">Total Notifications</p>
                            <p class="text-3xl font-bold text-blue-600">{{ auth()->user()->notifications->count() }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                            <p class="text-sm font-medium text-gray-500 mb-2">Unread</p>
                            <p class="text-3xl font-bold text-yellow-600">{{ auth()->user()->unreadNotifications->count() }}</p>
                        </div>
                        <div class="bg-white p-6 rounded-lg border border-gray-200 transition-all duration-300 hover:shadow-md">
                            <p class="text-sm font-medium text-gray-500 mb-2">Read</p>
                            <p class="text-3xl font-bold text-green-600">{{ auth()->user()->readNotifications->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="space-y-4">

                    <!-- Notifications List -->
                    <div class="space-y-4">
                        @forelse(auth()->user()->notifications as $notification)
                            <div class="bg-white p-6 rounded-lg border {{
                                $notification->read_at ? 'border-gray-200 opacity-60' :
                                (isset($notification->data['action']) ?
                                    ($notification->data['action'] === 'rejected' ? 'border-2 border-red-600/50' :
                                    ($notification->data['action'] === 'approved' ? 'border-2 border-green-400/50' :
                                    ($notification->data['action'] === 'resubmitted' ? 'border-2 border-yellow-400/50' : 'border-2 border-blue-400/50')))
                                : 'border-2 border-blue-400/50')
                            }} transition-all duration-300 hover:shadow-md">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                                    <div class="flex-grow space-y-2">
                                        <!-- Status Icon and Message -->
                                        <div class="flex items-center space-x-2">
                                            @if(!$notification->read_at)
                                                <div class="h-3 w-3 rounded-full {{
                                                    isset($notification->data['action']) ?
                                                        match($notification->data['action']) {
                                                            'rejected' => 'bg-red-500',
                                                            'approved' => 'bg-green-500',
                                                            'resubmitted' => 'bg-yellow-500',
                                                            default => 'bg-blue-500'
                                                        }
                                                    : 'bg-blue-500'
                                                }}"></div>
                                            @endif
                                            <p class="text-sm font-medium text-wgg-black-700">
                                                @if($notification->data['is_for_claim_owner'] ?? true)
                                                    @if(in_array($notification->data['action'] ?? '', ['approved', 'rejected']))
                                                        {{ $notification->data['message'] }}
                                                    @else
                                                        {{ $notification->data['message'] }}
                                                    @endif
                                                @else
                                                    {{ $notification->data['message'] }}
                                                @endif
                                            </p>
                                        </div>

                                        <!-- Timestamp -->
                                        <p class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon-small" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex space-x-2 *:w-fit">
                                        @if($notification->data['is_for_claim_owner'] ?? true)
                                            @switch($notification->data['action'] ?? null)
                                                @case('rejected')
                                                    <a href="{{ route('claims.claim', $notification->data['claim_id']) }}" class="btn bg-orange-500 hover:bg-orange-700 text-white text-xs p-2 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-small mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                        Resubmit
                                                    </a>
                                                    @break

                                                @default
                                                    <a href="{{ route('claims.claim', $notification->data['claim_id']) }}" class="btn bg-indigo-500 hover:bg-indigo-700 text-white text-xs p-2 flex items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-small mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        View Claim
                                                    </a>
                                            @endswitch
                                        @else
                                            <a href="{{ route('claims.review', $notification->data['claim_id']) }}" class="btn bg-yellow-500 hover:bg-yellow-700 text-white text-xs p-2 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-small mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                Review
                                            </a>
                                        @endif

                                        @if(!$notification->read_at)
                                            <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn bg-emerald-500 hover:bg-emerald-700 text-white text-xs p-2 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-small" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white p-6 rounded-lg border border-gray-200 text-center">
                                <p class="text-gray-500">No notifications found</p>
                            </div>
                        @endforelse
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        console.log('Notification script loaded');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded event fired');
            if (window.Echo) {
                console.log('Echo is available');
                const userId = {{ auth()->id() }};
                console.log('User ID:', userId);
                const channel = window.Echo.private(`App.Models.User.${userId}`);
                console.log('Subscribed to channel:', `App.Models.User.${userId}`);
                
                channel.listen('.ClaimStatusNotification', (notification) => {
                    console.log('ClaimStatusNotification received:', notification);
                    
                    // Update the notification count
                    const countElement = document.getElementById('notification-count');
                    if (countElement) {
                        let currentCount = parseInt(countElement.textContent) || 0;
                        currentCount++;
                        
                        countElement.textContent = currentCount;
                        countElement.classList.remove('hidden');
                        console.log('Updated notification count:', currentCount);
                    } else {
                        console.error('Notification count element not found');
                    }
                    
                    // Refresh the notifications list if on the notifications page
                    if (window.location.pathname === '{{ route('notifications') }}') {
                        console.log('Refreshing notifications page');
                        location.reload();
                    }
                });

                channel.error((error) => {
                    console.error('Error on Echo channel:', error);
                });
            } else {
                console.error('Echo is not available');
            }
        });
    </script>
    @endpush
    
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    </body>

</x-layout>
