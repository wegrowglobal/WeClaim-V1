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
        $reviewerInfo = $this->getReviewerInfo();
        
        $messages = [
            // Submission related
            'submitted' => "Your claim #{$this->claim->id} has been submitted successfully.",
            'resubmitted' => "Your claim #{$this->claim->id} has been resubmitted successfully.",
            
            // Approval flow
            'approved_admin' => "Your claim #{$this->claim->id} has been approved by Admin and sent to Datuk for review.",
            'approved_datuk' => "Your claim #{$this->claim->id} has been approved by Datuk and sent to HR for review.",
            'approved_hr' => "Your claim #{$this->claim->id} has been approved by HR and sent to Finance for review.",
            'approved_finance' => "Your claim #{$this->claim->id} has been approved by Finance.",
            
            // Rejection flow
            'rejected_admin' => "Your claim #{$this->claim->id} has been rejected by Admin. Please review and resubmit.",
            'rejected_datuk' => "Your claim #{$this->claim->id} has been rejected by Datuk and returned to Admin.",
            'rejected_hr' => "Your claim #{$this->claim->id} has been rejected by HR. Please review with Admin.",
            'rejected_finance' => "Your claim #{$this->claim->id} has been rejected by Finance. Please review with Admin.",
            
            // Completion
            'completed' => "Your claim #{$this->claim->id} has been processed and marked as completed by Finance.",
            
            // Generic status change
            'status_update' => "Your claim #{$this->claim->id} status has been updated to {$statusFormatted}."
        ];

        return $messages[$this->action] ?? $messages['status_update'];
    }

    private function createMessageForReviewer($statusFormatted)
    {
        $messages = [
            // New submissions
            'submitted' => "New claim #{$this->claim->id} requires your review.",
            'resubmitted' => "Claim #{$this->claim->id} has been resubmitted and requires your review.",
            
            // Review requests
            'pending_review_admin' => "Claim #{$this->claim->id} requires Admin review.",
            'pending_review_datuk' => "Claim #{$this->claim->id} requires Datuk's review.",
            'pending_review_hr' => "Claim #{$this->claim->id} requires HR review.",
            'pending_review_finance' => "Claim #{$this->claim->id} requires Finance review.",
            
            // Returns from rejection
            'returned_from_datuk' => "Claim #{$this->claim->id} was rejected by Datuk and requires Admin review.",
            'returned_from_hr' => "Claim #{$this->claim->id} was rejected by HR and requires review.",
            'returned_from_finance' => "Claim #{$this->claim->id} was rejected by Finance and requires review.",
            
            // Completion
            'completed' => "Claim #{$this->claim->id} has been marked as completed.",
            
            // Generic review request
            'pending_review' => "Claim #{$this->claim->id} requires your review."
        ];

        return $messages[$this->action] ?? $messages['pending_review'];
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
        return new BroadcastMessage($this->toArray($notifiable));
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
