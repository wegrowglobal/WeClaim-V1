<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\ClaimStatusNotification;
use Illuminate\Support\Facades\Log;
use App\Services\ClaimService;

class NotificationService
{
    private const NOTIFICATION_MAP = [
        'submitted' => [
            'recipients' => ['admin'],
            'owner_message' => 'Your claim #{id} has been submitted for Admin review',
            'reviewer_message' => 'New claim #{id} from {user} requires Admin review'
        ],
        'approved_admin' => [
            'recipients' => ['owner', 'manager'],
            'owner_message' => 'Your claim #{id} has been approved by Admin',
            'reviewer_message' => 'Claim #{id} requires Manager review'
        ],
        'approved_manager' => [
            'recipients' => ['owner', 'hr'],
            'owner_message' => 'Your claim #{id} has been approved by Manager',
            'reviewer_message' => 'Claim #{id} requires HR review'
        ],
        'approved_hr' => [
            'recipients' => ['owner', 'datuk_email'],
            'owner_message' => 'Your claim #{id} has been approved by HR and sent to Datuk',
            'reviewer_message' => 'Claim #{id} requires Datuk email approval'
        ],
        'approved_datuk' => [
            'recipients' => ['owner', 'finance'],
            'owner_message' => 'Your claim #{id} has been approved by Datuk',
            'reviewer_message' => 'Claim #{id} requires Finance review'
        ],
        'approved_finance' => [
            'recipients' => ['owner'],
            'owner_message' => 'Your claim #{id} has been approved by Finance',
            'reviewer_message' => null
        ],
        'rejected' => [
            'recipients' => ['owner'],
            'owner_message' => 'Your claim #{id} has been rejected by {role}',
            'reviewer_message' => null
        ],
        'resubmitted' => [
            'recipients' => ['previous_reviewer'],
            'owner_message' => 'Your claim #{id} has been resubmitted',
            'reviewer_message' => 'Resubmitted claim #{id} requires your review'
        ],
        'completed' => [
            'recipients' => ['owner'],
            'owner_message' => 'Your claim #{id} has been completed',
            'reviewer_message' => null
        ]
    ];

    public function __construct(
        private ClaimService $claimService
    ) {}

    public function sendNotifications(Claim $claim, string $action, ?User $triggeredBy = null): void
    {
        $config = self::NOTIFICATION_MAP[$action] ?? null;
        if (!$config) {
            Log::error("Unknown notification action: {$action}");
            return;
        }

        try {
            // Notify claim owner
            if (in_array('owner', $config['recipients'])) {
                $this->notifyOwner($claim, $config['owner_message'], $action, $triggeredBy);
            }

            // Notify other recipients
            foreach ($config['recipients'] as $recipientType) {
                match ($recipientType) {
                    'admin' => $this->notifyRole($claim, 'Admin', $config['reviewer_message'], $action, $triggeredBy),
                    'manager' => $this->notifyRole($claim, 'Manager', $config['reviewer_message'], $action, $triggeredBy),
                    'hr' => $this->notifyRole($claim, 'HR', $config['reviewer_message'], $action, $triggeredBy),
                    'finance' => $this->notifyRole($claim, 'Finance', $config['reviewer_message'], $action, $triggeredBy),
                    'datuk_email' => $this->handleDatukNotification($claim),
                    'previous_reviewer' => $this->notifyPreviousReviewer($claim, $config['reviewer_message'], $action, $triggeredBy),
                    default => null
                };
            }
        } catch (\Exception $e) {
            Log::error("Notification failed for claim {$claim->id}: " . $e->getMessage());
        }
    }

    private function notifyOwner(Claim $claim, string $messageTemplate, string $action, ?User $triggeredBy): void
    {
        $message = $this->replacePlaceholders($messageTemplate, $claim, $triggeredBy);
        $notification = new ClaimStatusNotification($claim, $action, $message, true);
        $claim->user->notify($notification);
    }

    private function notifyRole(Claim $claim, string $role, string $messageTemplate, string $action, ?User $triggeredBy): void
    {
        $users = User::whereHas('role', fn($q) => $q->where('name', $role))->get();
        $message = $this->replacePlaceholders($messageTemplate, $claim, $triggeredBy);

        foreach ($users as $user) {
            $notification = new ClaimStatusNotification($claim, $action, $message, false);
            $user->notify($notification);
        }
    }

    private function handleDatukNotification(Claim $claim): void
    {
        // Trigger email to Datuk instead of system notification
        $this->claimService->sendClaimToDatuk($claim);
        
        // Update claim status to pending Datuk approval
        $claim->update([
            'status' => Claim::STATUS_PENDING_DATUK,
            'pending_reviewer_id' => null // No system user for Datuk
        ]);
    }

    private function notifyPreviousReviewer(Claim $claim, string $messageTemplate, string $action, ?User $triggeredBy): void
    {
        if ($lastRejection = $claim->reviews()->latest()->where('status', 'rejected')->first()) {
            $message = $this->replacePlaceholders($messageTemplate, $claim, $triggeredBy);
            $notification = new ClaimStatusNotification($claim, $action, $message, false);
            $lastRejection->reviewer->notify($notification);
        }
    }

    private function replacePlaceholders(string $message, Claim $claim, ?User $triggeredBy = null): string
    {
        return str_replace(
            ['{id}', '{user}', '{role}'],
            [
                $claim->id,
                $claim->user->full_name,
                $triggeredBy?->role->name ?? $claim->pendingReviewer?->role->name ?? 'System'
            ],
            $message
        );
    }
}
