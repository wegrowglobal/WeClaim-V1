<div class="space-y-4">
    <h3 class="heading-2">Notifications Overview</h3>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
        <x-notifications.statistic-card 
            title="Total Notifications" 
            :count="auth()->user()->notifications->count()" 
            color="blue"
        />
        <x-notifications.statistic-card 
            title="Unread" 
            :count="auth()->user()->unreadNotifications->count()" 
            color="yellow"
        />
        <x-notifications.statistic-card 
            title="Read" 
            :count="auth()->user()->readNotifications->count()" 
            color="green"
        />
    </div>
</div>