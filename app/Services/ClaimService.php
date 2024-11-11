<?php

namespace App\Services;
use App\Models\User;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\ClaimReview;
use App\Mail\ClaimActionMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Str;
use Illuminate\Support\Arr;


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
        Log::info('ClaimService instantiated');
    }

    //////////////////////////////////////////////////////////////////

    public function createOrUpdateClaim(array $data, User $user, $claimId = null)
    {
        Log::info('Starting createOrUpdateClaim', [
            'claim_id' => $claimId,
            'user_id' => $user->id,
            'data' => Arr::except($data, ['toll_report', 'email_report'])
        ]);

        try {
            if ($claimId) {
                $claim = Claim::findOrFail($claimId);
                Log::info('Updating existing claim', ['claim_id' => $claimId]);
                $claim->update($this->prepareClaim($data, $user));
                $claim->status = $this->getPreviousNonRejectedStatus($claim) ?? Claim::STATUS_SUBMITTED;
            } else {
                Log::info('Creating new claim');
                $claim = new Claim($this->prepareClaim($data, $user));
                $initialReviewer = User::whereHas('role', function($query) {
                    $query->where('id', self::ROLE_ID_ADMIN);
                })->first();

                Log::info('Initial reviewer assigned', [
                    'reviewer_id' => $initialReviewer?->id,
                    'reviewer_name' => $initialReviewer ? ($initialReviewer->first_name . ' ' . $initialReviewer->second_name) : null,
                    'role_id' => self::ROLE_ID_ADMIN,
                    'role_name' => $initialReviewer?->role?->name
                ]);

                $claim->reviewer_id = $initialReviewer?->id;
            }

            $claim->save();
            $this->createOrUpdateLocations($claim, $data['location']);

            Log::info('Claim saved successfully', [
                'claim_id' => $claim->id,
                'status' => $claim->status,
                'reviewer_id' => $claim->reviewer_id
            ]);

            return $claim;

        } catch (\Exception $e) {
            Log::error('Error in createOrUpdateClaim', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'claim_id' => $claimId,
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    private function prepareClaim(array $data, User $user)
    {
        Log::debug('Preparing claim data', [
            'user_id' => $user->id,
            'claim_company' => $data['claim_company'],
            'total_distance' => $data['total_distance']
        ]);

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

    private function createOrUpdateLocations(Claim $claim, array $locationData)
    {
        Log::info('Creating/Updating locations for claim', [
            'claim_id' => $claim->id,
            'location_count' => count($locationData)
        ]);

        try {
            // Clear existing locations
            $claim->locations()->delete();
            
            $locations = array_values($locationData);
            $distances = request()->input('distances', []);
            
            // Validate that we have distances for each location pair
            if (count($locations) < 2) {
                throw new \Exception('At least two locations are required');
            }

            if (count($distances) < count($locations) - 1) {
                throw new \Exception('Distance values are missing for some location pairs');
            }

            // Calculate and update total distance
            $totalDistance = array_sum($distances);
            $claim->update(['total_distance' => $totalDistance]);
            
            // Create location pairs with distances
            for ($i = 0; $i < count($locations) - 1; $i++) {
                $claim->locations()->create([
                    'from_location' => $locations[$i],
                    'to_location' => $locations[$i + 1],
                    'distance' => $distances[$i],
                    'order' => $i + 1,
                ]);

                Log::debug('Created location pair', [
                    'claim_id' => $claim->id,
                    'from' => $locations[$i],
                    'to' => $locations[$i + 1],
                    'distance' => $distances[$i],
                    'order' => $i + 1
                ]);
            }

            Log::info('Locations updated successfully', [
                'claim_id' => $claim->id,
                'total_distance' => $totalDistance,
                'location_pairs' => count($locations) - 1
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating locations', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    public function updateClaimStatus(Claim $claim, string $status)
    {
        Log::info('Updating claim status', [
            'claim_id' => $claim->id,
            'old_status' => $claim->status,
            'new_status' => $status
        ]);

        try {
            $claim->status = $status;
            $claim->save();
            
            Log::info('Claim status updated successfully', [
                'claim_id' => $claim->id,
                'status' => $status
            ]);

            return $claim;

        } catch (\Exception $e) {
            Log::error('Error updating claim status', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    public function handleFileUploadsAndDocuments($claim, $tollReport, $emailReport)
    {
        Log::info('Starting file uploads for claim', [
            'claim_id' => $claim->id,
            'has_toll_report' => !is_null($tollReport),
            'has_email_report' => !is_null($emailReport)
        ]);

        try {
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

            Log::info('File uploads completed successfully', [
                'claim_id' => $claim->id,
                'toll_file' => $tollFileInfo['fileName'],
                'email_file' => $emailFileInfo['fileName']
            ]);

            return $claim;

        } catch (\Exception $e) {
            Log::error('Error handling file uploads', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    private function uploadFile($file, $path, $prefix)
    {
        if (!$file) {
            Log::info("No {$prefix} file provided");
            return ['fileName' => '', 'filePath' => ''];
        }

        try {
            $fileName = time() . "_{$prefix}_" . $file->getClientOriginalName();
            $filePath = $file->storeAs($path, $fileName, 'public');
            
            Log::info("File uploaded successfully", [
                'type' => $prefix,
                'file_name' => $fileName,
                'file_path' => $filePath
            ]);

            return ['fileName' => $fileName, 'filePath' => $filePath];

        } catch (\Exception $e) {
            Log::error("Error uploading {$prefix} file", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    public function getNextApproverRole(string $currentStatus)
    {
        Log::debug('Getting next approver role', ['current_status' => $currentStatus]);

        $nextRole = match ($currentStatus) {
            Claim::STATUS_APPROVED_ADMIN => 'Datuk',
            Claim::STATUS_APPROVED_DATUK => 'HR',
            Claim::STATUS_APPROVED_HR => 'Finance',
            Claim::STATUS_APPROVED_FINANCE => null,
            default => 'Admin'
        };

        Log::info('Next approver role determined', [
            'current_status' => $currentStatus,
            'next_role' => $nextRole
        ]);

        return $nextRole;
    }

    //////////////////////////////////////////////////////////////////

    private function calculateTotalAmount($totalDistance)
    {
        Log::debug('Calculating total amount', ['distance' => $totalDistance]);
        
        $roundedDistance = round(floatval($totalDistance), 2);
        $amount = $roundedDistance * self::PETROL_RATE;
        
        Log::info('Total amount calculated', [
            'distance' => $totalDistance,
            'rounded_distance' => $roundedDistance,
            'rate' => self::PETROL_RATE,
            'amount' => $amount
        ]);
        
        return $amount;
    }

    //////////////////////////////////////////////////////////////////

    public function canReviewClaim(User $user, Claim $claim)
    {
        Log::debug('Checking if user can review claim', [
            'user_id' => $user->id,
            'user_role' => $user->role->name,
            'claim_id' => $claim->id,
            'claim_status' => $claim->status
        ]);

        $canReview = match ($user->role->name) {
            'Admin' => in_array($claim->status, [Claim::STATUS_SUBMITTED, Claim::STATUS_APPROVED_ADMIN]),
            'HR' => $claim->status === Claim::STATUS_APPROVED_DATUK,
            'Finance' => in_array($claim->status, [Claim::STATUS_APPROVED_HR, Claim::STATUS_APPROVED_FINANCE]),
            default => false
        };

        Log::info('Review permission check result', [
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'can_review' => $canReview
        ]);

        return $canReview;
    }

    //////////////////////////////////////////////////////////////////

    public function approveClaim(User $user, Claim $claim)
    {
        Log::info('Starting claim approval process', [
            'user_id' => $user->id,
            'user_role' => $user->role->name,
            'claim_id' => $claim->id,
            'current_status' => $claim->status
        ]);

        try {
            $nextReviewer = null;
            $originalStatus = $claim->status;

            switch ($user->role->name) {
                case 'Admin':
                    if ($claim->status === Claim::STATUS_SUBMITTED) {
                        $claim->status = Claim::STATUS_APPROVED_ADMIN;
                        $claim->reviewer_id = $user->id;
                    } elseif ($claim->status === Claim::STATUS_APPROVED_ADMIN) {
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

            if ($nextReviewer) {
                $claim->reviewer_id = $nextReviewer->id;
                Log::info('Next reviewer assigned', [
                    'reviewer_id' => $nextReviewer->id,
                    'reviewer_role' => $nextReviewer->role->name
                ]);
            }

            $claim->save();

            Log::info('Claim approval completed successfully', [
                'claim_id' => $claim->id,
                'old_status' => $originalStatus,
                'new_status' => $claim->status,
                'next_reviewer_id' => $claim->reviewer_id
            ]);

            return $claim;

        } catch (\Exception $e) {
            Log::error('Error in claim approval process', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    public function rejectClaim(User $user, Claim $claim)
    {
        Log::info('Rejecting claim', [
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'previous_status' => $claim->status
        ]);

        try {
            $claim->status = Claim::STATUS_REJECTED;
            $claim->save();

            Log::info('Claim rejected successfully', [
                'claim_id' => $claim->id,
                'status' => Claim::STATUS_REJECTED
            ]);

            return $claim;

        } catch (\Exception $e) {
            Log::error('Error rejecting claim', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    public function storeRemarks(User $user, Claim $claim, string $remarks)
    {
        Log::info('Storing remarks for claim', [
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'department' => $user->role->name
        ]);

        try {
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

            Log::info('Remarks stored successfully', [
                'claim_id' => $claim->id,
                'review_id' => $claimReview->id,
                'review_order' => $reviewOrder
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing remarks', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    private function getPreviousNonRejectedStatus(Claim $claim)
    {
        Log::debug('Getting previous non-rejected status', ['claim_id' => $claim->id]);

        $lastReview = $claim->reviews()
            ->where('status', '!=', Claim::STATUS_REJECTED)
            ->orderBy('created_at', 'desc')
            ->first();

        Log::info('Previous non-rejected status retrieved', [
            'claim_id' => $claim->id,
            'previous_status' => $lastReview ? $lastReview->status : null
        ]);

        return $lastReview ? $lastReview->status : null;
    }

    //////////////////////////////////////////////////////////////////

    public function getPreviousRejectorRole(Claim $claim)
    {
        Log::debug('Getting previous rejector role', ['claim_id' => $claim->id]);

        $lastRejectedReview = $claim->reviews()
            ->where('status', Claim::STATUS_REJECTED)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastRejectedReview) {
            $reviewer = User::find($lastRejectedReview->reviewer_id);
            
            Log::info('Previous rejector found', [
                'claim_id' => $claim->id,
                'reviewer_id' => $reviewer?->id,
                'reviewer_role' => $reviewer?->role->name
            ]);

            return $reviewer ? $reviewer->role->name : null;
        }

        Log::info('No previous rejector found', ['claim_id' => $claim->id]);
        return null;
    }

    //////////////////////////////////////////////////////////////////

    private function getReviewColumnForRole(string $roleName): string
    {
        Log::debug('Getting review column for role', ['role_name' => $roleName]);

        return match ($roleName) {
            'Admin' => 'remarks_admin',
            'HR' => 'remarks_hr',
            'Finance' => 'remarks_finance',
            default => 'remarks_admin'
        };
    }

    public function sendClaimToDatuk(Claim $claim)
    {
        Log::info('Sending claim to Datuk', [
            'claim_id' => $claim->id,
            'user_id' => $claim->user_id,
            'status' => $claim->status
        ]);

        try {
            $data = [
                'claim' => $claim,
                'locations' => $claim->locations,
            ];

            Mail::to('ammar@wegrow-global.com')->send(new ClaimActionMail($data));

            Log::info('Claim sent to Datuk successfully', ['claim_id' => $claim->id]);

        } catch (\Exception $e) {
            Log::error('Error sending claim to Datuk', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

}
