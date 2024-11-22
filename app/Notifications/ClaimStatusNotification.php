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

    public function __construct(Claim $claim, string $status, string $action = 'status_update', $isForClaimOwner = true)
    {
        $this->claim = $claim;
        $this->status = $status;
        $this->action = $action;
        $this->isForClaimOwner = $isForClaimOwner;
        $this->message = $this->createMessage();
    }

    protected function createMessage()
    {
        $statusFormatted = str_replace('_', ' ', $this->status);
        return $this->isForClaimOwner
            ? $this->createMessageForClaimOwner($statusFormatted)
            : $this->createMessageForReviewer($statusFormatted);
    }

    private function getReviewerInfo()
    {
        $reviewer = $this->claim->reviewer;
        if (!$reviewer) {
            Log::warning('No reviewer assigned to claim', ['claim_id' => $this->claim->id]);
            return ['name' => 'System', 'role' => 'System'];
        }

        if (!$reviewer->role) {
            Log::warning('Reviewer has no role', ['reviewer_id' => $reviewer->id]);
            return ['name' => $reviewer->name, 'role' => 'Unknown Role'];
        }

        return [
            'name' => $reviewer->name,
            'role' => $reviewer->role->name
        ];
    }

    private function createMessageForClaimOwner($statusFormatted)
    {
        $messages = [
            // Flow 1: Initial submission
            'submitted' => "Your claim #{$this->claim->id} has been submitted and is pending Admin review.",
            
            // Flow 2: Admin to Datuk
            'approved_admin' => "Your claim #{$this->claim->id} has been approved by Admin and will be sent to Datuk for review.",
            'pending_datuk_review' => "Your claim #{$this->claim->id} has been sent to Datuk for review.",
            
            // Flow 3: Datuk to HR
            'approved_datuk' => "Your claim #{$this->claim->id} has been approved by Datuk and is now pending HR review.",
            
            // Flow 4: HR to Finance
            'approved_hr' => "Your claim #{$this->claim->id} has been approved by HR and is now pending Finance review.",
            
            // Flow 5: Finance completion
            'approved_finance' => "Your claim #{$this->claim->id} has been approved by Finance and is now waiting for payment.",
            'completed' => "Your claim #{$this->claim->id} has been fully processed and marked as completed.",
            
            // Rejection messages
            'rejected_admin' => "Your claim #{$this->claim->id} has been rejected by Admin. Please review and resubmit.",
            'rejected_datuk' => "Your claim #{$this->claim->id} has been rejected by Datuk.",
            'rejected_hr' => "Your claim #{$this->claim->id} has been rejected by HR.",
            'rejected_finance' => "Your claim #{$this->claim->id} has been rejected by Finance.",
            
            // Default status update
            'status_update' => "Your claim #{$this->claim->id} status has been updated to {$statusFormatted}."
        ];

        return $messages[$this->action] ?? $messages['status_update'];
    }

    private function createMessageForReviewer($statusFormatted)
    {
        $claimOwner = $this->claim->user;
        $ownerName = $claimOwner ? ($claimOwner->first_name . ' ' . $claimOwner->second_name) : 'Unknown User';
        
        $messages = [
            // Flow 1: Initial submission to Admin
            'pending_admin_review' => "Claim #{$this->claim->id} from {$ownerName} requires your review.",
            
            // Flow 2: Admin to Datuk
            'pending_review_datuk' => "Claim #{$this->claim->id} from {$ownerName} requires Datuk's review via email.",
            'rejected_datuk_admin' => "Claim #{$this->claim->id} was rejected by Datuk and requires your attention.",
            
            // Flow 3: Datuk to HR
            'pending_review_hr' => "Claim #{$this->claim->id} from {$ownerName} requires HR review.",
            
            // Flow 4: HR to Finance
            'pending_review_finance' => $this->isResubmission($this->claim) ? 
                "Resubmitted claim #{$this->claim->id} from {$ownerName} requires Finance review." :
                "Claim #{$this->claim->id} from {$ownerName} requires Finance review.",
            
            // Resubmission notifications
            'resubmitted_admin' => "Claim #{$this->claim->id} has been resubmitted by {$ownerName} and requires your review.",
            
            // Default review message
            'pending_review' => "Claim #{$this->claim->id} from {$ownerName} requires your review.",

            'resubmitted_review' => "Resubmitted claim #{$this->claim->id} from {$ownerName} requires your review.",

            // Finance specific messages
            'approved_finance' => "Claim #{$this->claim->id} can be marked as done after payment has been processed.",
        ];

        return $messages[$this->action] ?? $messages['pending_review'];
    }

    private function isResubmission(Claim $claim): bool
    {
        return $claim->reviews()
            ->where('status', Claim::STATUS_REJECTED)
            ->exists();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'claim_id' => $this->claim->id,
            'status' => $this->status,
            'action' => $this->action,
            'message' => $this->message,
            'is_for_claim_owner' => $this->isForClaimOwner,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'claim_id' => $this->claim->id,
            'status' => $this->status,
            'action' => $this->action,
            'message' => $this->message,
        ]);
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->claim->user_id);
    }

    public function broadcastAs()
    {
        return 'ClaimStatusNotification';
    }
}
