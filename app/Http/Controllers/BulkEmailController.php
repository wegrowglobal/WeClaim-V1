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
            $claims = Claim::with(['user', 'locations'])
                ->whereIn('id', $claimIds)
                ->where(function($query) {
                    $query->where('status', Claim::STATUS_APPROVED_HR)
                          ->orWhere('status', Claim::STATUS_PENDING_DATUK);
                })
                ->get();

            if ($claims->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid claims found to send.'
                ], 400);
            }

            DB::beginTransaction();
            try {
                foreach ($claims as $claim) {
                    // Always update to PENDING_DATUK status and reset timer
                    $claim->status = Claim::STATUS_PENDING_DATUK;
                    $claim->updated_at = now();
                    $claim->save();
                    
                    $this->claimService->sendClaimToDatuk($claim);
                }
                DB::commit();

                $message = $claims->count() > 1 ? 'Claims sent to Datuk successfully.' : 'Claim sent to Datuk successfully.';
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error sending bulk claims to Datuk', [
                'claim_ids' => $claimIds ?? [],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending claims to Datuk.'
            ], 500);
        }
    }
} 