<?php

namespace App\Notifications;

use App\Models\Claim\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ClaimStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Claim $claim,
        public string $action,
        public string $message,
        public bool $isForOwner
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'claim_id' => $this->claim->id,
            'action' => $this->action,
            'message' => $this->message,
            'is_owner' => $this->isForOwner,
            'url' => $this->getActionUrl(),
            'timestamp' => now()->toDateTimeString()
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    private function getActionUrl(): ?string
    {
        return match($this->action) {
            'rejected' => route('claims.edit', $this->claim),
            default => route('claims.view', $this->claim)
        };
    }
}
