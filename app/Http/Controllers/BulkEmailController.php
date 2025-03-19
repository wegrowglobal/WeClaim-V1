<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Services\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BulkEmailController extends Controller
{
    protected $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user || $user->role_id !== 3) { // Only HR can access
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        $claims = Claim::with(['user', 'locations'])
            ->where(function($query) {
                $query->where('status', Claim::STATUS_APPROVED_HR)
                      ->orWhere('status', Claim::STATUS_PENDING_DATUK);
            })
            ->orderBy('submitted_at', 'desc')
            ->get();

        return view('pages.claims.bulk-email', compact('claims'));
    }

    public function sendBulkEmail(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role_id !== 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only HR can send claims to Datuk.'
                ], 403);
            }

            $claimIds = $request->input('claims', []);
            if (empty($claimIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one claim to send.'
                ], 400);
            }

            Log::info('Starting bulk email process', [
                'user_id' => $user->id,
                'claim_ids' => $claimIds
            ]);

            $claims = Claim::with(['user', 'locations'])
                ->whereIn('id', $claimIds)
                ->where(function($query) {
                    $query->where('status', Claim::STATUS_APPROVED_HR)
                          ->orWhere('status', Claim::STATUS_PENDING_DATUK);
                })
                ->get();

            if ($claims->isEmpty()) {
                Log::warning('No valid claims found for bulk email', [
                    'requested_ids' => $claimIds
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No valid claims found to send.'
                ], 400);
            }

            $successCount = 0;
            $failedClaims = [];
            $errors = [];

            foreach ($claims as $claim) {
                try {
                    Log::info('Processing claim for bulk email', [
                        'claim_id' => $claim->id,
                        'current_status' => $claim->status
                    ]);

                    // First try to send the email
                    $emailSent = $this->claimService->sendClaimToDatuk($claim);

                    if ($emailSent) {
                        Log::info('Successfully processed claim', [
                            'claim_id' => $claim->id,
                            'token_expires_at' => $claim->approval_token_expires_at
                        ]);
                        
                        $successCount++;
                    } else {
                        $failedClaims[] = [
                            'id' => $claim->id,
                            'error' => 'Email sending failed'
                        ];
                        $errors[] = "Failed to send email for claim #{$claim->id}";
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to process claim', [
                        'claim_id' => $claim->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $failedClaims[] = [
                        'id' => $claim->id,
                        'error' => $e->getMessage()
                    ];
                    $errors[] = "Error processing claim #{$claim->id}: {$e->getMessage()}";
                }
            }

            // Determine response based on success/failure ratio
            if ($successCount === 0) {
                $message = 'Failed to send any claims. ' . implode(' ', $errors);
                $statusCode = 500;
                $success = false;
            } else if ($successCount === count($claims)) {
                $message = $successCount === 1 
                    ? 'Claim sent to Datuk successfully.' 
                    : "{$successCount} claims sent to Datuk successfully.";
                $statusCode = 200;
                $success = true;
            } else {
                $message = "{$successCount} out of " . count($claims) . " claims sent successfully. " . implode(' ', $errors);
                $statusCode = 207; // Multi-Status
                $success = true;
            }

            return response()->json([
                'success' => $success,
                'message' => $message,
                'failed_claims' => $failedClaims,
                'success_count' => $successCount,
                'total_claims' => count($claims)
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error('Error sending bulk claims to Datuk', [
                'claim_ids' => $claimIds ?? [],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending claims to Datuk: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateResponseMessage(int $successCount, array $failedClaims): string
    {
        if (empty($failedClaims)) {
            return $successCount > 1 
                ? "{$successCount} claims sent to Datuk successfully." 
                : "Claim sent to Datuk successfully.";
        }

        $failedCount = count($failedClaims);
        return "{$successCount} claim(s) sent successfully, {$failedCount} failed. Failed claim IDs: " . implode(', ', $failedClaims);
    }
} 