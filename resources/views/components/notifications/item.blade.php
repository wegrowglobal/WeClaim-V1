@props(['notification'])

<div class="group relative bg-white p-5 transition-all duration-200 hover:bg-gray-50 {{ 
    $notification->read_at ? 'opacity-35' : 'ring-1 ring-gray-200'
}}">
    <div class="flex items-start justify-between gap-4">
        <!-- Status Icon & Message -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3">
                @if(!$notification->read_at)
                    <span class="flex-shrink-0 h-2.5 w-2.5 rounded-full {{
                        isset($notification->data['action']) ?
                            match($notification->data['action']) {
                                'rejected_admin', 'rejected_datuk', 'rejected_hr', 'rejected_finance' => 'bg-red-500',
                                'approved_admin', 'approved_datuk', 'approved_hr', 'approved_finance' => 'bg-green-500',
                                'resubmitted' => 'bg-yellow-500',
                                'completed' => 'bg-purple-500',
                                default => 'bg-blue-500'
                            }
                        : 'bg-blue-500'
                    }}"></span>
                @endif
                <p class="text-sm font-medium text-gray-900 truncate">
                    {{ $notification->data['message'] }}
                </p>
            </div>
            <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
                <time datetime="{{ $notification->created_at }}">
                    {{ $notification->created_at->diffForHumans() }}
                </time>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
            @if($notification->data['is_for_claim_owner'] ?? true)
                @if(str_contains($notification->data['action'], 'rejected'))
                    <a href="{{ route('claims.resubmit', $notification->data['claim_id']) }}"
                       class="btn-secondary text-xs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Resubmit</span>
                    </a>
                @else
                    <a href="{{ route('claims.view', $notification->data['claim_id']) }}"
                       class="btn-secondary text-xs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>View</span>
                    </a>
                @endif
            @else
                <a href="{{ route('claims.review', $notification->data['claim_id']) }}"
                   class="btn-primary text-xs">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span>Review</span>
                </a>
            @endif

            @unless($notification->read_at)
                <form action="{{ route('notifications.mark-as-read', $notification->id) }}" 
                      method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="btn-secondary text-xs inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Mark as read</span>
                    </button>
                </form>
            @endunless
        </div>
    </div>
</div>