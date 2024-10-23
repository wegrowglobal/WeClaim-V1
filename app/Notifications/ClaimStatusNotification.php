<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class ClaimStatusNotification extends Notification implements ShouldBroadcast
{
    public $claim;
    public $status;
    public $action;
    public $message;
    private $isForClaimOwner;

    //////////////////////////////////////////////////////////////////

    public function __construct(Claim $claim, string $status, string $action = 'status_update', $isForClaimOwner = true)
    {
        $this->claim = $claim;
        $this->status = $status;
        $this->action = $action;
        $this->isForClaimOwner = $isForClaimOwner;
        $this->message = $this->createMessage();
    }

    ///////////////////////////////////////////////////////////////////

    protected function createMessage()
    {
        $statusFormatted = str_replace('_', ' ', $this->status);

        // Log the reviewer_id
        Log::info('Claim reviewer_id:', ['reviewer_id' => $this->claim->reviewer_id]);

        // Get the reviewer user
        $reviewer = User::find($this->claim->reviewer_id);

        // Ensure the reviewer exists and has a role
        $reviewer = User::with('role')->find($this->claim->reviewer_id);

        $reviewerRole = 'Reviewer'; // Default value
        if ($reviewer && $reviewer->role) {
            $reviewerRole = $reviewer->role->name;
        }

        Log::info('Reviewer:', ['reviewer' => $reviewer]);
        Log::info('Reviewer Role:', ['role' => $reviewer ? $reviewer->role : null]);

        if ($this->isForClaimOwner) {
            // Messages for the claim owner
            switch ($this->action) {
                case 'approved':
                    return "Your claim #{$this->claim->id} has been approved by {$reviewerRole}.";
                case 'rejected':
                    return "Your claim #{$this->claim->id} has been rejected.";
                case 'resubmitted':
                    return "Your claim #{$this->claim->id} has been resubmitted.";
                case 'submitted':
                    return "Your claim #{$this->claim->id} has been submitted.";
                case 'status_update':
                default:
                    return "Your claim #{$this->claim->id} status changed to {$statusFormatted}.";
            }
        } else {
            // Messages for role users
            switch ($this->action) {
                case 'resubmitted':
                    return "Claim #{$this->claim->id} has been resubmitted and requires your review.";
                case 'submitted':
                    return "Claim #{$this->claim->id} has been submitted and requires your review.";
                case 'status_update':
                default:
                    return "Claim #{$this->claim->id} status changed to {$statusFormatted}.";
            }
        }
    }

    ///////////////////////////////////////////////////////////////////

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'claim_id' => $this->claim->id,
            'status' => $this->status,
            'action' => $this->action,
            'message' => $this->message,
            'is_for_claim_owner' => $this->isForClaimOwner,
        ]);
    }

    ///////////////////////////////////////////////////////////////////

    public function toDatabase($notifiable)
    {
        return [
            'claim_id' => $this->claim->id,
            'status' => $this->status,
            'action' => $this->action,
            'message' => $this->message,
            'is_for_claim_owner' => $this->isForClaimOwner,
        ];
    }

    ///////////////////////////////////////////////////////////////////

    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    ///////////////////////////////////////////////////////////////////

    public function broadcastOn()
    {
        // Broadcast to a private channel based on the user ID
        return new PrivateChannel('users.' . $notifiable->id);
    }

    //////////////////////////////////////////////////////////////////
}
