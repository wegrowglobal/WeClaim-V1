<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Services\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        $claims = Claim::with('user')
            ->where('status', Claim::STATUS_APPROVED_HR)
            ->get();

        return view('pages.claims.bulk-email', [
            'claims' => $claims
        ]);
    }

    public function send(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role_id !== 3) { // Only HR can send
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only HR can send bulk emails to Datuk.'
                ], 403);
            }

            $claimIds = $request->input('claims', []);
            if (empty($claimIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one claim to send.'
                ], 400);
            }

            $claims = Claim::whereIn('id', $claimIds)
                ->where('status', Claim::STATUS_APPROVED_HR)
                ->get();

            if ($claims->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid claims found to send.'
                ], 400);
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($claims as $claim) {
                try {
                    $this->claimService->sendClaimToDatuk($claim);
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to send claim to Datuk', [
                        'claim_id' => $claim->id,
                        'error' => $e->getMessage()
                    ]);
                    $failedCount++;
                }
            }

            $message = "Successfully sent {$successCount} claim(s) to Datuk.";
            if ($failedCount > 0) {
                $message .= " Failed to send {$failedCount} claim(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk email send', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk emails: ' . $e->getMessage()
            ], 500);
        }
    }
} 