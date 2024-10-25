@props(['notification'])

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
        {{ $notification->data['message'] }}
    </p>
</div>