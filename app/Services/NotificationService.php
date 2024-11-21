<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\ClaimStatusNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendClaimStatusNotification(Claim $claim, string $status, string $action)
    {
        Log::info('Sending claim status notification', [
            'claim_id' => $claim->id,
            'status' => $status,
            'action' => $action
        ]);

        $claimOwner = $claim->user;
        
        // Determine next reviewer role based on status
        $nextReviewerRole = match ($status) {
            'submitted' => 'Admin',
            'approved_admin' => 'Datuk',
            'approved_datuk' => 'HR',
            'approved_hr' => 'Finance',
            default => null
        };

        // Notify claim owner with specific action
        $ownerAction = match ($action) {
            'approve' => "approved_{$claim->reviewer->role->name}",
            'reject' => "rejected_{$claim->reviewer->role->name}",
            default => $action
        };
        
        $claimOwner->notify(new ClaimStatusNotification($claim, $status, $ownerAction, true));

        // Notify next reviewer if applicable
        if ($nextReviewerRole) {
            $reviewers = User::whereHas('role', function ($query) use ($nextReviewerRole) {
                $query->where('name', $nextReviewerRole);
            })->get();

            foreach ($reviewers as $reviewer) {
                $reviewer->notify(new ClaimStatusNotification(
                    $claim,
                    $status,
                    "pending_review_{$nextReviewerRole}",
                    false
                ));
            }
        }
    }
}
