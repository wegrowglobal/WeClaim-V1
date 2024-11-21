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

        // Only notify claim owner for specific actions
        $claimOwner = $claim->user;
        if ($claimOwner && $this->shouldNotifyOwner($action)) {
            $claimOwner->notify(new ClaimStatusNotification(
                $claim,
                $status,
                $action,
                true
            ));
        }

        // Skip reviewer notifications for certain actions
        if (in_array($action, ['approved_admin', 'approved_datuk', 'approved_hr', 'approved_finance'])) {
            return;
        }

        // For other cases, proceed with normal notification flow
        $nextReviewerRole = $this->determineNextReviewerRole($status);
        if ($nextReviewerRole) {
            $reviewers = User::whereHas('role', function ($query) use ($nextReviewerRole) {
                $query->where('name', $nextReviewerRole);
            })->get();

            $reviewerAction = $this->determineReviewerAction($status, $claim);
            
            foreach ($reviewers as $reviewer) {
                if ($reviewer->id === $claim->user_id) {
                    continue;
                }
                
                $reviewer->notify(new ClaimStatusNotification(
                    $claim,
                    $status,
                    $reviewerAction,
                    false
                ));
            }
        }
    }

    private function determineNextReviewerRole(string $status): ?string
    {
        return match ($status) {
            Claim::STATUS_SUBMITTED => 'Admin',
            Claim::STATUS_APPROVED_ADMIN => 'Admin', // For Datuk email process
            Claim::STATUS_APPROVED_DATUK => 'HR',
            Claim::STATUS_APPROVED_HR => 'Finance',
            Claim::STATUS_APPROVED_FINANCE => 'Finance',
            Claim::STATUS_DONE => null,
            default => null
        };
    }

    private function determineReviewerAction(string $status, Claim $claim): string
    {
        if ($this->isResubmission($claim)) {
            return match ($status) {
                Claim::STATUS_SUBMITTED => 'resubmitted_admin',
                Claim::STATUS_APPROVED_HR => 'resubmitted_review',
                default => 'pending_review'
            };
        }

        return match ($status) {
            Claim::STATUS_SUBMITTED => 'pending_admin_review',
            Claim::STATUS_APPROVED_ADMIN => 'pending_review_datuk',
            Claim::STATUS_APPROVED_DATUK => 'pending_review_hr',
            Claim::STATUS_APPROVED_HR => 'pending_review_finance',
            default => 'pending_review'
        };
    }

    private function isResubmission(Claim $claim): bool
    {
        return $claim->reviews()
            ->where('status', Claim::STATUS_REJECTED)
            ->exists();
    }

    private function shouldNotifyOwner(string $action): bool
    {
        return in_array($action, [
            'approved_admin',
            'approved_datuk',
            'approved_hr',
            'approved_finance',
            'rejected_admin',
            'rejected_datuk',
            'rejected_hr',
            'rejected_finance',
            'completed'
        ]);
    }
}
