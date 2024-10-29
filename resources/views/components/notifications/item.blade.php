@props(['notification'])

<div class="bg-white p-6 rounded-lg border {{ 
    $notification->read_at ? 'border-gray-200 opacity-60' :
    (isset($notification->data['action']) ?
        ($notification->data['action'] === 'rejected' ? 'border-2 border-red-600/50' :
        ($notification->data['action'] === 'approved' ? 'border-2 border-green-400/50' :
        ($notification->data['action'] === 'resubmitted' ? 'border-2 border-yellow-400/50' : 'border-2 border-blue-400/50')))
    : 'border-2 border-blue-400/50')
}} transition-all duration-300 hover:shadow-md">
    <div class="grid grid-cols-3 gap-4">
        <div class="w-fit col-span-2 space-y-2">
            <x-notifications.status-icon-and-message :notification="$notification" />
            <x-notifications.timestamp :notification="$notification" />
        </div>
        <div class="col-span-1 justify-self-start md:justify-self-end">
            <x-notifications.action-buttons :notification="$notification" />
        </div>
    </div>
</div>