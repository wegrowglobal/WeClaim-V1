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
            'action' => $action,
            'user_id' => $claim->user_id ?? 'null'
        ]);

        try {
            // Only notify claim owner for specific actions
            $claimOwner = $claim->user;
            if ($claimOwner && $this->shouldNotifyOwner($action)) {
                $notification = new ClaimStatusNotification(
                    $claim,
                    $status,
                    $action,
                    true
                );

                $claimOwner->notify($notification);
            }

            // Skip reviewer notifications for certain actions
            if (in_array($action, [
                'approved_admin',
                'approved_manager',
                'approved_hr',
                'approved_datuk',
                'approved_finance'
            ])) {
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

                    try {
                        $notification = new ClaimStatusNotification(
                            $claim,
                            $status,
                            $reviewerAction,
                            false
                        );
                        $reviewer->notify($notification);
                    } catch (\Exception $e) {
                        Log::error('Failed to send notification to reviewer', [
                            'reviewer_id' => $reviewer->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in sendClaimStatusNotification', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function determineNextReviewerRole(string $status): ?string
    {
        return match ($status) {
            Claim::STATUS_SUBMITTED => 'Admin',
            Claim::STATUS_APPROVED_ADMIN => 'Manager',
            Claim::STATUS_APPROVED_MANAGER => 'HR',
            Claim::STATUS_APPROVED_HR => 'HR', // HR still handles Datuk email
            Claim::STATUS_APPROVED_DATUK => 'Finance',
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
            Claim::STATUS_APPROVED_ADMIN => 'pending_manager_review',
            Claim::STATUS_APPROVED_MANAGER => 'pending_hr_review',
            Claim::STATUS_APPROVED_HR => 'pending_datuk_review',
            Claim::STATUS_APPROVED_DATUK => 'pending_finance_review',
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
            'approved_manager',
            'approved_hr',
            'approved_datuk',
            'approved_finance',
            'rejected_admin',
            'rejected_manager',
            'rejected_hr',
            'rejected_datuk',
            'rejected_finance',
            'completed'
        ]);
    }
}
