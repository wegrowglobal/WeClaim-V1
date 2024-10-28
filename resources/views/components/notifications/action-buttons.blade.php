@props(['notification'])

<div class="flex space-x-2 *:w-fit">
    @if($notification->data['is_for_claim_owner'] ?? true)
        @switch($notification->data['action'] ?? null)
            @case('rejected')
                <x-notifications.action-button-link 
                    :href="route('claims.view', $notification->data['claim_id'])"
                    icon="refresh"
                    color="orange"
                    text="Resubmit"
                />
                @break
            @default
                <x-notifications.action-button-link 
                    :href="route('claims.view', $notification->data['claim_id'])"
                    icon="eye"
                    color="indigo"
                    text="View Claim"
                />
        @endswitch
    @else
        <x-notifications.action-button-link 
            :href="route('claims.review', $notification->data['claim_id'])"
            icon="clipboard-check"
            color="yellow"
            text="Review"
        />
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