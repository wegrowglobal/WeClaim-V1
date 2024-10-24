<?php

namespace App\Services;
use App\Models\User;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\ClaimReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Str;

class ClaimService
{

    //////////////////////////////////////////////////////////////////

    private const PETROL_RATE = 0.6;
    private const CLAIM_TYPE_PETROL = 'Petrol';
    private const CLAIM_TYPE_ITEMS = 'Items';
    private const CLAIM_TYPE_PETTY_CASH = 'Petty Cash';
    private const CLAIM_TYPE_OTHERS = 'Others';
    private const STATUS_SUBMITTED = 'Submitted';
    private const ROLE_ID_ADMIN = 2;
    private const ROLE_ID_DATUK = 3;
    private const ROLE_ID_HR = 4;
    private const ROLE_ID_FINANCE = 5;

    //////////////////////////////////////////////////////////////////

    public function __construct()
    {

    }

    //////////////////////////////////////////////////////////////////

    public function createOrUpdateClaim(array $data, User $user, $claimId = null)
    {
        if ($claimId) {
            $claim = Claim::findOrFail($claimId);
            $claim->update($this->prepareClaim($data, $user));
            $claim->status = $this->getPreviousNonRejectedStatus($claim) ?? Claim::STATUS_SUBMITTED;
        } else {
            $claim = new Claim($this->prepareClaim($data, $user));
            $initialReviewer = User::whereHas('role', function($query) {
                $query->where('id', self::ROLE_ID_ADMIN);
            })->first();
            $claim->reviewer_id = $initialReviewer ? $initialReviewer->id : null;
        }

        $claim->save();
        $this->createOrUpdateLocations($claim, $data['location']);

        return $claim;
    }

    //////////////////////////////////////////////////////////////////

    private function prepareClaim(array $data, User $user)
    {
        return [
            'user_id' => $user->id,
            'title' => 'Petrol Claim - ' . strtoupper($data['claim_company']),
            'description' => $data['remarks'],
            'petrol_amount' => $this->calculateTotalAmount($data['total_distance']),
            'status' => self::STATUS_SUBMITTED,
            'claim_type' => self::CLAIM_TYPE_PETROL,
            'submitted_at' => now(),
            'claim_company' => strtoupper($data['claim_company']),
            'toll_amount' => $data['toll_amount'],
            'total_distance' => $data['total_distance'],
            'from_location' => $data['location'][0] ?? null,
            'to_location' => end($data['location']) ?? null,
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'token' => \Illuminate\Support\Str::random(32),
        ];
    }

    //////////////////////////////////////////////////////////////////

    private function createOrUpdateLocations(Claim $claim, array $locations)
    {
        $claim->locations()->delete();
        foreach ($locations as $index => $location) {
            $claim->locations()->create([
                'location' => $location,
                'order' => $index + 1,
                'claim_id' => $claim->id,
            ]);
        }
    }

    //////////////////////////////////////////////////////////////////

    public function updateClaimStatus(Claim $claim, string $status)
    {
        $claim->status = $status;
        $claim->save();
        return $claim;
    }

    //////////////////////////////////////////////////////////////////

    public function handleFileUploadsAndDocuments($claim, $tollReport, $emailReport)
    {
        Storage::disk('public')->makeDirectory('uploads/claims/toll/');
        Storage::disk('public')->makeDirectory('uploads/claims/email');

        $tollFileInfo = $this->uploadFile($tollReport, 'uploads/claims/toll', 'toll');
        $emailFileInfo = $this->uploadFile($emailReport, 'uploads/claims/email', 'email');

        ClaimDocument::create([
            'claim_id' => $claim->id,
            'toll_file_name' => $tollFileInfo['fileName'],
            'toll_file_path' => $tollFileInfo['filePath'],
            'email_file_name' => $emailFileInfo['fileName'],
            'email_file_path' => $emailFileInfo['filePath'],
            'uploaded_by' => Auth::id(),
        ]);

        return $claim;
    }

    //////////////////////////////////////////////////////////////////

    private function uploadFile($file, $path, $prefix)
    {
        if (!$file) {
            return ['fileName' => '', 'filePath' => ''];
        }

        $fileName = time() . "_{$prefix}_" . $file->getClientOriginalName();
        $filePath = $file->storeAs($path, $fileName, 'public');
        Log::info("{$prefix} report uploaded", ['file_name' => $fileName]);

        return ['fileName' => $fileName, 'filePath' => $filePath];
    }

    //////////////////////////////////////////////////////////////////

    public function getNextApproverRole(string $currentStatus)
    {
        switch ($currentStatus) {
            case Claim::STATUS_APPROVED_ADMIN:
                return 'Datuk';
            case Claim::STATUS_APPROVED_DATUK:
                return 'HR';
            case Claim::STATUS_APPROVED_HR:
                return 'Finance';
            case Claim::STATUS_APPROVED_FINANCE:
                return null; // No further approver
            default:
                return 'Admin';
        }
    }

    //////////////////////////////////////////////////////////////////

    private function calculateTotalAmount($totalDistance)
    {
        return $totalDistance * self::PETROL_RATE;
    }

    //////////////////////////////////////////////////////////////////

    public function canReviewClaim(User $user, Claim $claim)
    {
        $user = Auth::user();
        switch ($user->role->name) {
            case 'Admin':
                return in_array($claim->status, [Claim::STATUS_SUBMITTED, Claim::STATUS_APPROVED_ADMIN]);
            case 'HR':
                return $claim->status === Claim::STATUS_APPROVED_DATUK;
            case 'Finance':
                return in_array($claim->status, [Claim::STATUS_APPROVED_HR, Claim::STATUS_APPROVED_FINANCE]);
            default:
                return false;
        }
    }

    //////////////////////////////////////////////////////////////////

    public function approveClaim(User $user, Claim $claim)
    {
        Log::info('Attempting to approve claim', [
            'user_id' => $user->id,
            'user_role' => $user->role->name,
            'claim_id' => $claim->id,
            'current_status' => $claim->status
        ]);

        $nextReviewer = null;
        $originalStatus = $claim->status;

        switch ($user->role->name) {
            case 'Admin':
                if ($claim->status === Claim::STATUS_SUBMITTED || $claim->status === Claim::STATUS_APPROVED_ADMIN) {
                    $claim->status = Claim::STATUS_APPROVED_DATUK;
                    $nextReviewer = User::whereHas('role', function($query) {
                        $query->where('id', self::ROLE_ID_HR);
                    })->first();
                }
                break;
            case 'HR':
                if ($claim->status === Claim::STATUS_APPROVED_DATUK) {
                    $claim->status = Claim::STATUS_APPROVED_HR;
                    $nextReviewer = User::whereHas('role', function($query) {
                        $query->where('id', self::ROLE_ID_FINANCE);
                    })->first();
                }
                break;
            case 'Finance':
                if ($claim->status === Claim::STATUS_APPROVED_HR) {
                    $claim->status = Claim::STATUS_APPROVED_FINANCE;
                } elseif ($claim->status === Claim::STATUS_APPROVED_FINANCE) {
                    $claim->status = Claim::STATUS_DONE;
                }
                break;
            default:
                Log::warning('Unauthorized role attempting to approve claim', [
                    'user_role' => $user->role->name,
                    'claim_id' => $claim->id
                ]);
                return $claim;
        }

        // If there's a next reviewer, set the reviewer_id
        if ($nextReviewer) {
            $claim->reviewer_id = $nextReviewer->id;
        } else {
            // If there's no next reviewer (e.g., claim is done), keep the current reviewer
            $claim->reviewer_id = $user->id;
        }

        Log::info('Updating claim reviewer', [
            'claim_id' => $claim->id,
            'new_reviewer_id' => $claim->reviewer_id,
            'new_status' => $claim->status
        ]);

        try {
            $claim->save();
            Log::info('Claim updated successfully', [
                'claim_id' => $claim->id,
                'old_status' => $originalStatus,
                'new_status' => $claim->status,
                'new_reviewer_id' => $claim->reviewer_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update claim', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        return $claim;
    }

    //////////////////////////////////////////////////////////////////

    public function rejectClaim(User $user, Claim $claim)
    {
        $claim->status = Claim::STATUS_REJECTED;
        $claim->save();
        return $claim;
    }

    //////////////////////////////////////////////////////////////////

    public function storeRemarks(User $user, Claim $claim, string $remarks)
    {
        $reviewOrder = ClaimReview::where('claim_id', $claim->id)
            ->where('department', $user->role->name)
            ->count() + 1;

        $claimReview = new ClaimReview([
            'claim_id' => $claim->id,
            'reviewer_id' => $user->id,
            'remarks' => $remarks,
            'review_order' => $reviewOrder,
            'department' => $user->role->name,
            'reviewed_at' => now(),
            'status' => $claim->status,
        ]);

        $claimReview->save();
    }

    //////////////////////////////////////////////////////////////////

    private function getPreviousNonRejectedStatus(Claim $claim)
    {
        $lastReview = $claim->reviews()
        ->where('status', '!=', Claim::STATUS_REJECTED)
        ->orderBy('created_at', 'desc')
        ->first();

        return $lastReview ? $lastReview->status : null;
    }

    //////////////////////////////////////////////////////////////////

    public function getPreviousRejectorRole(Claim $claim)
    {
        // Fetch the last review where the claim was rejected
        $lastRejectedReview = $claim->reviews()
            ->where('status', Claim::STATUS_REJECTED)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastRejectedReview) {
            // Get the reviewer who rejected the claim
            $reviewer = User::find($lastRejectedReview->reviewer_id);

            if ($reviewer) {
                return $reviewer->role->name;
            }
        }

        return null;
    }

    //////////////////////////////////////////////////////////////////

    private function getReviewColumnForRole(string $roleName): string
    {
        switch ($roleName) {
            case 'Admin':
                return 'remarks_admin';
            case 'HR':
                return 'remarks_hr';
            case 'Finance':
                return 'remarks_finance';
            default:
                return 'remarks_admin';
        }
    }

}
