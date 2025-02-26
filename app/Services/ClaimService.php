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

        if (empty($locations)) {
            throw new \InvalidArgumentException('Cannot store empty locations');
        }

        // Clear existing locations
        $claim->locations()->delete();

        foreach ($locations as $index => $location) {
            // Ensure we have required fields
            if (empty($location['from_location'])) {
                throw new \InvalidArgumentException('Invalid location data: missing from_location');
            }

            // Get the to_location from the location data or the next location's from_location
            $toLocation = $location['to_location'] ?? 
                         ($locations[$index + 1]['from_location'] ?? $location['from_location']);

            $claim->locations()->create([
                'from_location' => $location['from_location']['address'] ?? $location['from_location'],
                'to_location' => $toLocation['address'] ?? $toLocation,
                'distance' => (float) ($location['distance'] ?? 0),
                'order' => (int) ($location['order'] ?? $index + 1)
            ]);
        }
    }

    private function createAccommodations(Claim $claim, array $accommodations): void
    {
        Log::info('Creating accommodations for claim', [
            'claim_id' => $claim->id,
            'accommodations_count' => count($accommodations)
        ]);

        try {
            // If no accommodations, just return
            if (empty($accommodations)) {
                Log::info('No accommodations to create', ['claim_id' => $claim->id]);
                return;
            }

            // Clear existing accommodations if any
            $claim->accommodations()->delete();

            foreach ($accommodations as $index => $accommodation) {
                Log::info('Processing accommodation entry', [
                    'claim_id' => $claim->id,
                    'index' => $index,
                    'data' => array_diff_key($accommodation, ['receipt' => true])
                ]);

                // Skip if missing required fields
                if (!isset($accommodation['location']) || 
                    !isset($accommodation['check_in']) || 
                    !isset($accommodation['check_out']) || 
                    !isset($accommodation['price'])) {
                    Log::warning('Skipping invalid accommodation - missing required fields', [
                        'claim_id' => $claim->id,
                        'index' => $index,
                        'accommodation' => array_diff_key($accommodation, ['receipt' => true])
                    ]);
                    continue;
                }

                // Validate data types and formats
                try {
                    $location = trim($accommodation['location']);
                    $checkIn = date('Y-m-d', strtotime($accommodation['check_in']));
                    $checkOut = date('Y-m-d', strtotime($accommodation['check_out']));
                    $price = (float) $accommodation['price'];

                    if (empty($location) || $price <= 0) {
                        Log::warning('Skipping invalid accommodation - invalid data', [
                            'claim_id' => $claim->id,
                            'location' => $location,
                            'price' => $price
                        ]);
                        continue;
                    }

                    $accommodationData = [
                        'location' => $location,
                        'price' => $price,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut
                    ];

                    // Handle file upload if a receipt is present
                    if (isset($accommodation['receipt']) && $accommodation['receipt']->isValid()) {
                        try {
                            $receiptPath = Storage::disk('public')->put(
                                'accommodation_receipts',
                                $accommodation['receipt']
                            );
                            
                            if ($receiptPath) {
                                $accommodationData['receipt_path'] = $receiptPath;
                                Log::info('Uploaded accommodation receipt', [
                                    'claim_id' => $claim->id,
                                    'index' => $index,
                                    'receipt_path' => $receiptPath
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error uploading receipt file', [
                                'claim_id' => $claim->id,
                                'index' => $index,
                                'error' => $e->getMessage()
                            ]);
                            // Continue without receipt if upload fails
                        }
                    }

                    // Create accommodation record
                    $created = $claim->accommodations()->create($accommodationData);
                    
                    Log::info('Created accommodation record', [
                        'claim_id' => $claim->id,
                        'accommodation_id' => $created->id,
                        'location' => $location,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'price' => $price,
                        'has_receipt' => isset($accommodationData['receipt_path'])
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error processing accommodation entry', [
                        'claim_id' => $claim->id,
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'accommodation' => array_diff_key($accommodation, ['receipt' => true])
                    ]);
                    continue; // Skip this entry but continue with others
                }
            }
        } catch (\Exception $e) {
            Log::error('Error creating accommodations', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function hasDocuments(array $data): bool
    {
        return isset($data['toll_report']) || isset($data['email_report']);
    }

    private function createDocuments(Claim $claim, array $data): void
    {
        $documents = [];

        if (isset($data['toll_report'])) {
            $tollPath = Storage::disk('public')->put(
                'uploads/claims/toll',
                $data['toll_report']
            );
            $documents['toll_file_path'] = $tollPath;
            $documents['toll_file_name'] = $data['toll_report']->getClientOriginalName();
        }

        if (isset($data['email_report'])) {
            $emailPath = Storage::disk('public')->put(
                'uploads/claims/email',
                $data['email_report']
            );
            $documents['email_file_path'] = $emailPath;
            $documents['email_file_name'] = $data['email_report']->getClientOriginalName();
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
            Claim::STATUS_APPROVED_FINANCE => $roleId === self::ROLE_ID_FINANCE,
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

            // Get remarks from request
            $remarks = request()->input('remarks');

            // Create review record with remarks
            $userRole = $user->role()->first();
            $reviewOrder = $claim->reviews()->count() + 1;
            
            ClaimReview::create([
                'claim_id' => $claim->getKey(),
                'reviewer_id' => $user->getKey(),
                'department' => $userRole ? $userRole->name : 'Unknown',
                'status' => 'Approved',  // Standardize the status
                'remarks' => $remarks ?? 'No remarks provided',
                'review_order' => $reviewOrder,
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

    public function rejectClaim(User $user, Claim $claim, array $rejectionDetails)
    {
        DB::transaction(function () use ($user, $claim, $rejectionDetails) {
            $claim->status = Claim::STATUS_REJECTED;
            $claim->save();

            // Create review record
            $claim->reviews()->create([
                'reviewer_id' => $user->id,
                'status' => Claim::STATUS_REJECTED,
                'remarks' => $rejectionDetails['remarks'],
                'department' => $user->role->name,
                'review_order' => $claim->reviews()->count() + 1,
                'reviewed_at' => now(),
                'requires_basic_info' => $rejectionDetails['requires_basic_info'] ?? false,
                'requires_trip_details' => $rejectionDetails['requires_trip_details'] ?? false,
                'requires_accommodation_details' => $rejectionDetails['requires_accommodation_details'] ?? false,
                'requires_documents' => $rejectionDetails['requires_documents'] ?? false,
                'rejection_details' => $rejectionDetails
            ]);
        });
    }

    //////////////////////////////////////////////////////////////////

    public function storeRemarks(User $user, Claim $claim, string $remarks)
    {
        // This method is now deprecated as remarks are handled in approveClaim and rejectClaim
        Log::warning('storeRemarks method is deprecated. Remarks should be handled in approveClaim or rejectClaim');
        return;
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

    public function sendClaimToDatuk(Claim $claim): bool
    {
        try {
            Log::info('Starting to send claim to Datuk', [
                'claim_id' => $claim->id,
                'current_status' => $claim->status
            ]);

            // Force fresh load of the claim with its relationships
            $claim = Claim::with(['user', 'locations'])->findOrFail($claim->id);

            // Validate claim status
            if (!in_array($claim->status, [Claim::STATUS_APPROVED_HR, Claim::STATUS_PENDING_DATUK])) {
                Log::error('Invalid claim status for sending to Datuk', [
                    'claim_id' => $claim->id,
                    'current_status' => $claim->status,
                    'expected_statuses' => [Claim::STATUS_APPROVED_HR, Claim::STATUS_PENDING_DATUK]
                ]);
                throw new \Exception('Claim must be in Approved HR or Pending Datuk status to be sent to Datuk.');
            }

            // Validate required relationships
            if (!$claim->user || $claim->locations->isEmpty()) {
                Log::error('Missing required claim data', [
                    'claim_id' => $claim->id,
                    'has_user' => (bool)$claim->user,
                    'locations_count' => $claim->locations->count()
                ]);
                throw new \Exception('Claim is missing required data (user or locations).');
            }

            // Generate new approval token if not exists
            if (!$claim->approval_token || !$claim->approval_token_expires_at) {
                $claim->generateApprovalToken();
                Log::info('Generated new approval token', [
                    'claim_id' => $claim->id,
                    'token_expires_at' => $claim->approval_token_expires_at
                ]);
            }

            // Get Datuk's email from config
            $datukEmail = config('mail.datuk_email');
            
            if (!$datukEmail) {
                Log::error('Datuk email not configured', [
                    'claim_id' => $claim->id,
                    'config_value' => config('mail.datuk_email')
                ]);
                throw new \Exception('Datuk email address is not configured.');
            }
            
            Log::info('Using Datuk email configuration', [
                'email' => $datukEmail
            ]);

            // Prepare data for email
            $data = [
                'claim' => $claim,
                'locations' => $claim->locations
            ];

            Log::info('Attempting to send email to Datuk', [
                'claim_id' => $claim->id,
                'datuk_email' => $datukEmail,
                'locations_count' => $claim->locations->count(),
                'token_expires_at' => $claim->approval_token_expires_at,
                'mail_config' => [
                    'mailer' => config('mail.mailer'),
                    'host' => config('mail.host'),
                    'port' => config('mail.port'),
                    'from_address' => config('mail.from.address')
                ]
            ]);

            try {
                // Configure mailer with specific SSL/TLS settings
                config([
                    'mail.mailers.smtp.verify_peer' => false,
                    'mail.mailers.smtp.verify_peer_name' => false,
                    'mail.mailers.smtp.allow_self_signed' => true
                ]);

                // Send email to Datuk with retry mechanism
                $maxRetries = 3;
                $attempt = 1;
                $lastError = null;

                while ($attempt <= $maxRetries) {
                    try {
                        Mail::to($datukEmail)
                            ->send(new ClaimActionMail($data));

                        // If we reach here, email was sent successfully
                        Log::info('Claim email sent to Datuk successfully', [
                            'claim_id' => $claim->id,
                            'datuk_email' => $datukEmail,
                            'attempt' => $attempt
                        ]);

                        // Update claim status to pending Datuk only after successful email
                        $claim->status = Claim::STATUS_PENDING_DATUK;
                        $claim->save();

                        return true;

                    } catch (\Exception $e) {
                        $lastError = $e->getMessage();
                        Log::warning('Email send attempt failed', [
                            'claim_id' => $claim->id,
                            'attempt' => $attempt,
                            'error' => $lastError
                        ]);

                        if ($attempt < $maxRetries) {
                            sleep(2 * $attempt); // Exponential backoff
                        }
                        $attempt++;
                    }
                }

                Log::error('All email send attempts failed', [
                    'claim_id' => $claim->id,
                    'datuk_email' => $datukEmail,
                    'attempts' => $maxRetries,
                    'last_error' => $lastError
                ]);
                throw new \Exception('Failed to send email after ' . $maxRetries . ' attempts: ' . $lastError);
            } catch (\Exception $e) {
                Log::error('Mail sending failed', [
                    'claim_id' => $claim->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send claim to Datuk', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function handleDatukAction(Claim $claim, string $action, ?string $token): bool
    {
        if (!$claim->isApprovalTokenValid($token)) {
            Log::warning('Invalid or expired approval token', [
                'claim_id' => $claim->id,
                'action' => $action
            ]);
            return false;
        }

        if (!$claim->canBeApprovedByDatuk()) {
            Log::warning('Claim cannot be approved by Datuk - invalid status', [
                'claim_id' => $claim->id,
                'current_status' => $claim->status
            ]);
            return false;
        }

        DB::beginTransaction();
        try {
            // Update claim status
            $claim->status = $action === 'approve' ? 
                Claim::STATUS_APPROVED_DATUK : 
                Claim::STATUS_REJECTED;
            
            // Create review record
            $claim->reviews()->create([
                'reviewer_id' => null, // Email action, no specific reviewer
                'status' => $claim->status,
                'remarks' => $action === 'approve' ? 
                    'Approved via email by Datuk' : 
                    'Rejected via email by Datuk',
                'department' => 'Management',
                'review_order' => $claim->reviews()->count() + 1,
                'reviewed_at' => now()
            ]);

            // Invalidate the token after use
            $claim->invalidateApprovalToken();
            
            DB::commit();

            Log::info('Datuk action processed successfully', [
                'claim_id' => $claim->id,
                'action' => $action,
                'new_status' => $claim->status
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process Datuk action', [
                'claim_id' => $claim->id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function handleResubmission(Claim $claim, array $data, array $sectionsToRevise)
    {
        return DB::transaction(function () use ($claim, $data, $sectionsToRevise) {
            // Get the previous non-rejected status
            $previousStatus = $this->getPreviousNonRejectedStatus($claim) ?? Claim::STATUS_SUBMITTED;

            // Update only the sections that need revision
            $updateData = [];

            if ($sectionsToRevise['basic_info'] ?? false) {
                $updateData += [
                    'description' => $data['description'],
                    'claim_company' => $data['claim_company'],
                    'date_from' => $data['date_from'],
                    'date_to' => $data['date_to']
                ];
            }

            if ($sectionsToRevise['trip_details'] ?? false) {
                $updateData += [
                    'petrol_amount' => $data['petrol_amount'],
                    'total_distance' => $data['total_distance']
                ];

                // Update locations if provided
                if (isset($data['locations'])) {
                    $claim->locations()->delete();
                    $locations = is_array($data['locations']) ? $data['locations'] : json_decode($data['locations'], true);
                    
                    foreach ($locations as $index => $location) {
                        $claim->locations()->create([
                            'from_location' => $location,
                            'to_location' => $locations[$index + 1] ?? '',
                            'distance' => $distances[$index] ?? 0,
                            'order' => $index + 1
                        ]);
                    }
                }
            }

            if ($sectionsToRevise['documents'] ?? false) {
                $updateData['toll_amount'] = $data['toll_amount'];

                // Handle document uploads
                if (isset($data['toll_file']) || isset($data['email_file'])) {
                    $this->handleFileUploadsAndDocuments($claim, $data['toll_file'] ?? null, $data['email_file'] ?? null);
                }
            }

            if ($sectionsToRevise['accommodation_details'] ?? false) {
                if (!empty($data['accommodations'])) {
                $claim->accommodations()->delete();
                $this->createAccommodations($claim, $data['accommodations']);
                } else {
                    // Completely optional - no action needed if not provided
                    Log::info('No accommodations provided, preserving existing if any', ['claim_id' => $claim->id]);
                }
            }

            // Update claim with revised data
            $updateData['status'] = $previousStatus;
            $updateData['submitted_at'] = now();
            $claim->update($updateData);

            // Create resubmission review record
            $claim->reviews()->create([
                'reviewer_id' => Auth::id(),
                'remarks' => 'Claim resubmitted after rejection',
                'department' => Auth::user()->role->name,
                'review_order' => $claim->reviews()->count() + 1,
                'status' => $previousStatus,
                'reviewed_at' => now()
            ]);

            // Send notifications
            $this->notifyRelevantUsers($claim, 'resubmitted');

            // Add to handleResubmission
            Log::debug('Accommodation data received', [
                'has_accommodations' => !empty($data['accommodations']),
                'count' => !empty($data['accommodations']) ? count($data['accommodations']) : 0,
                'data_sample' => !empty($data['accommodations']) ? $data['accommodations'][0] : null
            ]);

            return true;
        });
    }

    private function notifyRelevantUsers(Claim $claim, string $action)
    {
        // Implementation of notifyRelevantUsers method
    }

    public function calculateClaimAmount($distance)
    {
        $ratePerKm = SystemConfig::getConfig('claim.rate_per_km', 0.60);
        return $distance * $ratePerKm;
    }
}
