<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\ClaimStatusNotification;

class NotificationService
{

    ///////////////////////////////////////////////////////////////////

    public function sendClaimStatusNotification(Claim $claim, string $status, string $action = 'status_update')
    {
        $claimOwner = $claim->user;
        $reviewers = User::whereHas('role', function ($query) {
            $query->where('name', 'Reviewer');
        })->get();

        // Notify claim owner
        $claimOwner->notify(new ClaimStatusNotification($claim, $status, $action, true));

        // Notify reviewers
        foreach ($reviewers as $reviewer) {
            $reviewer->notify(new ClaimStatusNotification($claim, $status, $action, false));
        }
    }

    ///////////////////////////////////////////////////////////////////

}
