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

    ///////////////////////////////////////////////////////////////////
    
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
        $reviewer = $this->claim->reviewer;
        $reviewerRole = $reviewer->role->name;

        return $this->isForClaimOwner
            ? $this->createMessageForClaimOwner($statusFormatted)
            : $this->createMessageForReviewer($statusFormatted);
    }

    /////////////////////////////////////////////////////////////////// 

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    ///////////////////////////////////////////////////////////////////

    public function toArray(object $notifiable): array
    {
        return [
            'claim_id' => $this->claim->id,
            'action' => $this->action,
            'message' => $this->message,
        ];
    }

    ///////////////////////////////////////////////////////////////////

    private function getReviewerRole($reviewer)
    {
        if (!$reviewer) {
            Log::warning('No reviewer assigned to this claim');
            return 'System';
        }

        Log::info('Reviewer Details:', [
            'reviewer_id' => $reviewer->id,
            'reviewer_name' => $reviewer->name,
            'role_id' => $reviewer->role_id,
        ]);

        if (!$reviewer->role) {
            Log::warning('Reviewer has no role assigned');
            return 'Unknown Role';
        }

        Log::info('Reviewer Role:', ['role_name' => $reviewer->role->name]);
        return $reviewer->role->name;
    }

    ///////////////////////////////////////////////////////////////////

    private function createMessageForClaimOwner($statusFormatted)
    {
        $messages = [
            'approved' => "Your claim #{$this->claim->id} has been approved by {$this->getReviewerRole($this->claim->reviewer)}.",
            'rejected' => "Your claim #{$this->claim->id} has been rejected by {$this->getReviewerRole($this->claim->reviewer)}.",
            'resubmitted' => "Your claim #{$this->claim->id} has been resubmitted.",
            'submitted' => "Your claim #{$this->claim->id} has been submitted.",
        ];

        return $messages[$this->action] ?? "Your claim #{$this->claim->id} status changed to {$statusFormatted}.";
    }

    ///////////////////////////////////////////////////////////////////

    private function createMessageForReviewer($statusFormatted)
    {
        $messages = [
            'resubmitted' => "Claim #{$this->claim->id} has been resubmitted and requires your review.",
            'submitted' => "Claim #{$this->claim->id} has been submitted and requires your review.",
        ];

        return $messages[$this->action] ?? "Claim #{$this->claim->id} status changed to {$statusFormatted}.";
    }

    ///////////////////////////////////////////////////////////////////
/* 
    protected function createMessage()
    {
        $statusFormatted = str_replace('_', ' ', $this->status);

        // Get the reviewer user
        $reviewer = $this->claim->reviewer;

        // Add more detailed logging
        Log::info('Claim Details:', [
            'claim_id' => $this->claim->id,
            'reviewer_id' => $this->claim->reviewer_id,
            'status' => $this->claim->status,
        ]);

        $reviewerRole = 'Reviewer'; // Default value
        if ($reviewer) {
            Log::info('Reviewer Details:', [
                'reviewer_id' => $reviewer->id,
                'reviewer_name' => $reviewer->name,
                'role_id' => $reviewer->role_id,
            ]);
            
            if ($reviewer->role) {
                $reviewerRole = $reviewer->role->name;
                Log::info('Reviewer Role:', ['role_name' => $reviewerRole]);
            } else {
                Log::warning('Reviewer has no role assigned');
                $reviewerRole = 'Unknown Role';
            }
        } else {
            Log::warning('No reviewer assigned to this claim');
            $reviewerRole = 'System';
        }

        if ($this->isForClaimOwner) {
            // Messages for the claim owner
            switch ($this->action) {
                case 'approved':
                    return "Your claim #{$this->claim->id} has been approved by {$reviewerRole}.";
                case 'rejected':
                    return "Your claim #{$this->claim->id} has been rejected {$reviewerRole}.";
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
 */
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

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->claim->user_id);
    }

    //////////////////////////////////////////////////////////////////

    public function broadcastAs()
    {
        return 'ClaimStatusNotification';
    }

    //////////////////////////////////////////////////////////////////
}
