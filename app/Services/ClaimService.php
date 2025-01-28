<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\User;
use App\Models\ClaimDocument;
use App\Models\ClaimReview;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Exception;
use App\Mail\ClaimActionMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ClaimStatusNotification;
use App\Models\SystemConfig;

class ClaimService
{

    //////////////////////////////////////////////////////////////////

    private const PETROL_RATE = 0.6;
    private const CLAIM_TYPE_PETROL = 'Petrol';
    private const STATUS_SUBMITTED = 'Submitted';
    private const ROLE_ID_ADMIN = 2;
    private const ROLE_ID_MANAGER = 6;
    private const ROLE_ID_HR = 3;
    private const ROLE_ID_FINANCE = 4;

    //////////////////////////////////////////////////////////////////

    public function __construct()
    {
        Log::info('ClaimService instantiated');
    }

    public function createClaim(array $validatedData, int $userId): Claim
    {
        DB::beginTransaction();

        try {
            // Get initial admin reviewer
            $initialReviewer = User::whereHas('role', function ($query) {
                $query->where('id', self::ROLE_ID_ADMIN);
            })->first();

            // Format numeric values
            $claim = Claim::create([
                'user_id' => $userId,
                'title' => $validatedData['title'],
                'description' => $validatedData['remarks'],
                'petrol_amount' => (float) $validatedData['petrol_amount'],
                'status' => $validatedData['status'],
                'claim_type' => $validatedData['claim_type'],
                'total_distance' => (float) $validatedData['total_distance'],
                'submitted_at' => now(),
                'claim_company' => $validatedData['claim_company'],
                'toll_amount' => (float) $validatedData['toll_amount'],
                'date_from' => $validatedData['date_from'],
                'date_to' => $validatedData['date_to'],
                'reviewer_id' => $initialReviewer ? $initialReviewer->id : null, // Set initial reviewer
            ]);

            // Create locations if they exist
            if (!empty($validatedData['locations'])) {
                $this->createLocations($claim, $validatedData['locations']);
            }

            // Create accommodations if they exist
            if (!empty($validatedData['accommodations'])) {
                $this->createAccommodations($claim, $validatedData['accommodations']);
            }

            // Create documents if they exist
            if ($this->hasDocuments($validatedData)) {
                $this->createDocuments($claim, $validatedData);
            }

            DB::commit();
            return $claim;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating claim', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function createLocations(Claim $claim, string $locationsJson): void
    {
        $locations = json_decode($locationsJson, true);

        foreach ($locations as $location) {
            // Ensure we have an order value
            $order = $location['order'] ?? 0;

            $claim->locations()->create([
                'from_location' => $location['from_location']['address'] ?? $location['from_location'],
                'to_location' => $location['to_location']['address'] ?? $location['to_location'],
                'distance' => (float) $location['distance'],
                'order' => (int) $order
            ]);
        }
    }

    private function createAccommodations(Claim $claim, array $accommodations): void
    {
        foreach ($accommodations as $accommodation) {
            // Handle file upload if it exists
            $receiptPath = null;
            if (isset($accommodation['receipt']) && $accommodation['receipt']->isValid()) {
                $receiptPath = Storage::disk('public')->put(
                    'accommodation_receipts',
                    $accommodation['receipt']
                );
            } else if (isset($accommodation['receipt_name'])) {
                // If we have an existing receipt name, use that
                $receiptPath = $accommodation['receipt_name'];
            }

            // Create the accommodation record
            $claim->accommodations()->create([
                'location' => $accommodation['location'],
                'location_address' => $accommodation['location_address'] ?? $accommodation['location'],
                'price' => $accommodation['price'],
                'check_in' => $accommodation['check_in'],
                'check_out' => $accommodation['check_out'],
                'receipt_path' => $receiptPath
            ]);
        }
    }

    private function hasDocuments(array $data): bool
    {
        return isset($data['toll_file']) || isset($data['email_file']);
    }

    private function createDocuments(Claim $claim, array $data): void
    {
        $documents = [];

        if (isset($data['toll_file'])) {
            $tollPath = Storage::disk('public')->put(
                'toll_reports',
                $data['toll_file']
            );
            $documents['toll_file_path'] = $tollPath;
            $documents['toll_file_name'] = $data['toll_file']->getClientOriginalName();
        }

        if (isset($data['email_file'])) {
            $emailPath = Storage::disk('public')->put(
                'email_reports',
                $data['email_file']
            );
            $documents['email_file_path'] = $emailPath;
            $documents['email_file_name'] = $data['email_file']->getClientOriginalName();
        }

        if (!empty($documents)) {
            $documents['uploaded_by'] = $claim->user_id;
            $claim->documents()->create($documents);
        }
    }

    public function deleteClaim(Claim $claim): void
    {
        DB::beginTransaction();

        try {
            // Delete associated documents from storage
            foreach ($claim->documents as $document) {
                if ($document->toll_file_path) {
                    Storage::disk('public')->delete($document->toll_file_path);
                }
                if ($document->email_file_path) {
                    Storage::disk('public')->delete($document->email_file_path);
                }
            }

            // The actual records will be deleted by the database cascade
            $claim->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createOrUpdateClaim(array $data, User $user, $claimId = null)
    {
        Log::info('Starting createOrUpdateClaim', [
            'claim_id' => $claimId,
            'user_id' => $user->id,
            'data' => Arr::except($data, ['toll_report', 'email_report'])
        ]);

        try {
            if ($claimId) {
                $claim = Claim::with('user')->findOrFail($claimId);
                Log::info('Updating existing claim', ['claim_id' => $claimId]);
                $claim->update($this->prepareClaim($data, $user));
                $claim->status = $this->getPreviousNonRejectedStatus($claim) ?? Claim::STATUS_SUBMITTED;
            } else {
                Log::info('Creating new claim');
                $claim = new Claim($this->prepareClaim($data, $user));
                $initialReviewer = User::whereHas('role', function ($query) {
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
        return match ($currentStatus) {
            Claim::STATUS_SUBMITTED => self::ROLE_ID_ADMIN,
            Claim::STATUS_APPROVED_ADMIN => self::ROLE_ID_MANAGER,
            Claim::STATUS_APPROVED_MANAGER => self::ROLE_ID_HR,
            Claim::STATUS_APPROVED_HR => null, // HR will send to Datuk
            Claim::STATUS_APPROVED_DATUK => self::ROLE_ID_FINANCE,
            default => null,
        };
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

    public function canReviewClaim(User $user, Claim $claim): bool
    {
        $roleId = $user->role_id;
        return match ($claim->status) {
            Claim::STATUS_SUBMITTED => $roleId === self::ROLE_ID_ADMIN,
            Claim::STATUS_APPROVED_ADMIN => $roleId === self::ROLE_ID_MANAGER,
            Claim::STATUS_APPROVED_MANAGER => $roleId === self::ROLE_ID_HR,
            Claim::STATUS_APPROVED_DATUK => $roleId === self::ROLE_ID_FINANCE,
            default => false,
        };
    }

    //////////////////////////////////////////////////////////////////

    public function approveClaim(User $user, Claim $claim)
    {
        DB::beginTransaction();
        try {
            $nextStatus = match ($claim->status) {
                Claim::STATUS_SUBMITTED => Claim::STATUS_APPROVED_ADMIN,
                Claim::STATUS_APPROVED_ADMIN => Claim::STATUS_APPROVED_MANAGER,
                Claim::STATUS_APPROVED_MANAGER => Claim::STATUS_APPROVED_HR,
                Claim::STATUS_APPROVED_HR => Claim::STATUS_APPROVED_DATUK,
                Claim::STATUS_APPROVED_DATUK => Claim::STATUS_APPROVED_FINANCE,
                Claim::STATUS_APPROVED_FINANCE => Claim::STATUS_DONE,
                default => $claim->status,
            };

            $nextReviewerId = null;
            $nextRoleId = $this->getNextApproverRole($claim->status);
            if ($nextRoleId) {
                $nextReviewer = User::where('role_id', $nextRoleId)->first();
                if ($nextReviewer) {
                    $nextReviewerId = $nextReviewer->getKey();
                }
            }

            $claim->update([
                'status' => $nextStatus,
                'reviewer_id' => $nextReviewerId
            ]);

            // Create review record
            $userRole = $user->role()->first();
            ClaimReview::create([
                'claim_id' => $claim->getKey(),
                'reviewer_id' => $user->getKey(),
                'department' => $userRole ? $userRole->name : 'Unknown',
                'status' => 'approved',
                'reviewed_at' => now()
            ]);

            // Notify relevant users
            $this->notifyRelevantUsers($claim, 'approved');

            DB::commit();
            return $claim;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    //////////////////////////////////////////////////////////////////

    public function rejectClaim(User $user, Claim $claim)
    {
        DB::transaction(function () use ($user, $claim) {
            // Set status to REJECTED
            $claim->status = Claim::STATUS_REJECTED;

            // Set reviewer back to Admin for resubmission
            $nextReviewer = User::whereHas('role', function ($query) {
                $query->where('name', 'Admin');
            })->first();

            if ($nextReviewer) {
                $claim->reviewer_id = $nextReviewer->id;
            }

            $claim->save();

            // Create review record
            $claim->reviews()->create([
                'reviewer_id' => $user->id,
                'remarks' => 'Claim rejected',
                'department' => $user->role->name,
                'review_order' => $claim->reviews()->count() + 1,
                'status' => $claim->status,
                'reviewed_at' => now()
            ]);

            // Send rejection notification
            $notificationService = app(NotificationService::class);
            $notificationService->sendClaimStatusNotification(
                $claim,
                $claim->status,
                'rejected_' . strtolower($user->role->name)
            );
        });
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
        Log::info('Starting sendClaimToDatuk process', [
            'claim_id' => $claim->id,
            'user_id' => $claim->user_id,
            'status' => $claim->status
        ]);

        try {
            // Force fresh load of the claim with user relationship
            $claim = Claim::with(['user', 'locations'])->findOrFail($claim->id);

            Log::info('Claim loaded with relationships', [
                'claim_id' => $claim->id,
                'user_loaded' => $claim->relationLoaded('user'),
                'user_exists' => $claim->user !== null,
                'user_details' => $claim->user ? [
                    'id' => $claim->user->id,
                    'name' => $claim->user->first_name . ' ' . $claim->user->second_name
                ] : null
            ]);

            if (!$claim->user) {
                // Check if user_id exists but relationship is broken
                $userExists = DB::table('users')->where('id', $claim->user_id)->exists();
                Log::error('User relationship issue detected', [
                    'claim_id' => $claim->id,
                    'user_id' => $claim->user_id,
                    'user_exists_in_db' => $userExists
                ]);
                throw new \Exception('Claim user relationship is invalid. User ID: ' . $claim->user_id);
            }

            $data = [
                'claim' => $claim,
                'locations' => $claim->locations,
            ];

            Mail::to('ammar@wegrow-global.com')->send(new ClaimActionMail($data));

            Log::info('Claim sent to Datuk successfully', [
                'claim_id' => $claim->id,
                'user_id' => $claim->user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending claim to Datuk', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function handleResubmission(Claim $claim, array $data)
    {
        return DB::transaction(function () use ($claim, $data) {
            // Get the previous non-rejected status
            $previousStatus = $this->getPreviousNonRejectedStatus($claim) ?? Claim::STATUS_SUBMITTED;

            // Update claim with new data
            $claim->update([
                'description' => $data['description'],
                'claim_company' => $data['claim_company'],
                'petrol_amount' => $data['petrol_amount'],
                'toll_amount' => $data['toll_amount'],
                'total_distance' => $data['total_distance'],
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
                'status' => $previousStatus,
                'submitted_at' => now()
            ]);

            // Clear old locations and create new ones
            $claim->locations()->delete();
            $locations = json_decode($data['locations'], true);
            foreach ($locations as $location) {
                $claim->locations()->create($location);
            }

            // Create resubmission review record
            $claim->reviews()->create([
                'reviewer_id' => auth()->id(),
                'remarks' => 'Claim resubmitted after rejection',
                'department' => auth()->user()->role->name,
                'review_order' => $claim->reviews()->count() + 1,
                'status' => $previousStatus,
                'reviewed_at' => now()
            ]);

            // Send notifications
            $this->notifyRelevantUsers($claim, 'resubmitted');

            return true;
        });
    }

    private function notifyRelevantUsers(Claim $claim, string $action)
    {
        $notificationService = app(NotificationService::class);
        $notificationService->sendClaimStatusNotification($claim, $claim->status, $action);
    }

    public function calculateClaimAmount($distance)
    {
        $ratePerKm = SystemConfig::getConfig('claim.rate_per_km', 0.60);
        return $distance * $ratePerKm;
    }
}
