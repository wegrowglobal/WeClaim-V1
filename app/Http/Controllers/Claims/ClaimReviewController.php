<?php

namespace App\Http\Controllers\Claims;

use App\Http\Controllers\Controller;
use App\Models\Claim\Claim;
use App\Models\User\User;
use App\Services\ClaimService;
use App\Notifications\ClaimStatusNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificationService;
use App\Mail\ClaimApprovalRequest;
use Exception;

class ClaimReviewController extends Controller
{
    use AuthorizesRequests;
    protected $claimService;
    protected $notificationService;

    public function __construct(ClaimService $claimService, NotificationService $notificationService)
    {
        $this->claimService = $claimService;
        $this->notificationService = $notificationService;
        
        // Apply middleware for all methods
        $this->middleware('auth')->except(['handleEmailAction']);
        $this->middleware('track.activity')->except(['handleEmailAction']);
        
        // Apply profile completion middleware for claim actions
        $this->middleware('profile.complete');
        
        // Apply email verification middleware for reviewing claims
        $this->middleware('verified');
    }

    /**
     * Display the claim review form.
     */
    public function reviewClaim($id)
    {
        Log::info('Accessing claim review form', ['claim_id' => $id]);

        try {
            $claim = Claim::with(['locations' => function ($query) {
                $query->orderBy('order');
            }, 'user', 'documents', 'reviews'])->findOrFail($id);
            
            $user = Auth::user();
            
            // Check if user has permission to review this claim
            if (!$this->claimService->canReviewClaim($user, $claim)) {
                Log::warning('Unauthorized attempt to review claim', [
                    'user_id' => $user->id,
                    'claim_id' => $claim->id
                ]);
                
                return redirect()->route('home')->with('error', 'You do not have permission to review this claim');
            }
            
            $locationData = $claim->locations->map(function ($location) {
                return [
                    'order' => $location->order,
                    'from' => [
                        'name' => $location->from_location,
                        'lat' => $location->from_latitude,
                        'lng' => $location->from_longitude
                    ],
                    'to' => [
                        'name' => $location->to_location,
                        'lat' => $location->to_latitude,
                        'lng' => $location->to_longitude
                    ],
                    'distance' => $location->distance
                ];
            })->values()->all();
            
            // Get the previous reviews for this claim
            $previousReviews = $claim->reviews()->with('reviewer')->get();
            
            Log::info('Rendering claim review form', [
                'claim_id' => $claim->id,
                'user_id' => $user->id,
                'has_previous_reviews' => $previousReviews->count() > 0
            ]);
            
            return view('pages.claims.review', [
                'claim' => $claim,
                'previousReviews' => $previousReviews,
                'claimService' => $this->claimService,
                'locationData' => $locationData
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Claim not found for review', ['claim_id' => $id]);
            return redirect()->route('home')->with('error', 'Claim not found');
        } catch (Exception $e) {
            Log::error('Error accessing claim review', [
                'claim_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('home')->with('error', 'An error occurred while accessing the claim review');
        }
    }

    /**
     * Update a claim based on review.
     */
    public function updateClaim(Request $request, $id)
    {
        Log::info('Processing claim review submission', ['claim_id' => $id]);
        
        try {
            $claim = Claim::findOrFail($id);
            $user = Auth::user();
            
            // Validate input
            $validated = $request->validate([
                'decision' => 'required|in:approve,reject,request_changes',
                'comments' => 'nullable|string|max:1000',
                'approval_level' => 'required|in:hr,finance,datuk'
            ]);
            
            // Check permission based on approval level
            if (!$this->claimService->canApproveAtLevel($user, $validated['approval_level'])) {
                Log::warning('Unauthorized review attempt', [
                    'user_id' => $user->id,
                    'claim_id' => $claim->id,
                    'approval_level' => $validated['approval_level']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to review this claim at this level'
                ], 403);
            }
            
            // Process the review
            $result = $this->claimService->processClaimReview(
                $claim,
                $user->id,
                $validated['decision'],
                $validated['comments'],
                $validated['approval_level']
            );
            
            if ($result['success']) {
                // Send notification to the claim owner
                $this->notificationService->sendClaimStatusNotification($claim);
                
                Log::info('Claim review processed successfully', [
                    'claim_id' => $claim->id,
                    'reviewer_id' => $user->id,
                    'decision' => $validated['decision'],
                    'new_status' => $claim->status
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect' => route('claims.approval')
                ]);
            } else {
                Log::error('Error processing claim review', [
                    'claim_id' => $claim->id,
                    'error' => $result['message']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (ModelNotFoundException $e) {
            Log::error('Claim not found for review update', ['claim_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Claim not found'
            ], 404);
        } catch (ValidationException $e) {
            Log::error('Validation error during claim review', [
                'claim_id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error updating claim review', [
                'claim_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the review'
            ], 500);
        }
    }

    /**
     * Send a claim to Datuk for final approval
     */
    public function sendToDatuk(Request $request, $id)
    {
        Log::info('Sending claim to Datuk for approval', ['claim_id' => $id]);
        
        try {
            $claim = Claim::with('user')->findOrFail($id);
            $user = Auth::user();
            
            // Check if the user has permission to send to Datuk
            if (!$this->claimService->canSendToDatuk($user)) {
                Log::warning('Unauthorized attempt to send claim to Datuk', [
                    'user_id' => $user->id,
                    'claim_id' => $claim->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to send this claim to Datuk for approval'
                ], 403);
            }
            
            // Check if claim is in the correct state
            if ($claim->status !== Claim::STATUS_IN_REVIEW) {
                Log::warning('Attempt to send claim to Datuk in incorrect state', [
                    'claim_id' => $claim->id,
                    'current_status' => $claim->status
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'This claim cannot be sent to Datuk in its current state'
                ], 400);
            }
            
            // Generate signed URLs for approval and rejection
            $approveUrl = URL::temporarySignedRoute(
                'claims.email.action',
                now()->addDays(5),
                ['id' => $claim->id, 'action' => 'approve']
            );
            
            $rejectUrl = URL::temporarySignedRoute(
                'claims.email.action',
                now()->addDays(5),
                ['id' => $claim->id, 'action' => 'reject']
            );
            
            // Send email to Datuk
            Mail::to(config('mail.datuk_email'))
                ->send(new ClaimApprovalRequest($claim, $approveUrl, $rejectUrl));
            
            // Update claim status to indicate it's with Datuk
            $claim->datuk_sent_at = now();
            $claim->save();
            
            Log::info('Claim sent to Datuk successfully', [
                'claim_id' => $claim->id,
                'sender_id' => $user->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Claim has been sent to Datuk for final approval'
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Claim not found for sending to Datuk', ['claim_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Claim not found'
            ], 404);
        } catch (Exception $e) {
            Log::error('Error sending claim to Datuk', [
                'claim_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the claim to Datuk'
            ], 500);
        }
    }

    /**
     * Handle email-based approval/rejection actions
     */
    public function handleEmailAction(Request $request, $id, $action)
    {
        Log::info('Processing email-based claim action', [
            'claim_id' => $id,
            'action' => $action
        ]);
        
        try {
            // Verify the action
            if (!in_array($action, ['approve', 'reject'])) {
                Log::error('Invalid email action requested', [
                    'claim_id' => $id,
                    'action' => $action
                ]);
                
                return view('pages.claims.email-action-result', [
                    'success' => false,
                    'message' => 'Invalid action specified'
                ]);
            }
            
            $claim = Claim::with('user')->findOrFail($id);
            
            // Check if claim is in a valid state
            if ($claim->status !== Claim::STATUS_IN_REVIEW) {
                Log::warning('Email action attempted on claim in invalid state', [
                    'claim_id' => $id,
                    'action' => $action,
                    'current_status' => $claim->status
                ]);
                
                return view('pages.claims.email-action-result', [
                    'success' => false,
                    'message' => 'This claim is no longer awaiting approval'
                ]);
            }
            
            // Process the action
            if ($action === 'approve') {
                // Set to approved
                $claim->status = Claim::STATUS_APPROVED;
                $claim->approved_at = now();
                $claim->save();
                
                // Log and create review record
                $this->claimService->createReviewRecord(
                    $claim->id,
                    null, // No authenticated user for email actions
                    'approve',
                    'Approved via email by Datuk',
                    'datuk'
                );
                
                Log::info('Claim approved via email', ['claim_id' => $id]);
                
                // Notify claim owner
                $this->notificationService->sendClaimStatusNotification($claim);
                
                return view('pages.claims.email-action-result', [
                    'success' => true,
                    'message' => 'The claim has been approved successfully',
                    'claim' => $claim
                ]);
            } else {
                // Set to rejected
                $claim->status = Claim::STATUS_REJECTED;
                $claim->rejected_at = now();
                $claim->save();
                
                // Log and create review record
                $this->claimService->createReviewRecord(
                    $claim->id,
                    null, // No authenticated user for email actions
                    'reject',
                    'Rejected via email by Datuk',
                    'datuk'
                );
                
                Log::info('Claim rejected via email', ['claim_id' => $id]);
                
                // Notify claim owner
                $this->notificationService->sendClaimStatusNotification($claim);
                
                return view('pages.claims.email-action-result', [
                    'success' => true,
                    'message' => 'The claim has been rejected',
                    'claim' => $claim
                ]);
            }
        } catch (ModelNotFoundException $e) {
            Log::error('Claim not found for email action', ['claim_id' => $id]);
            return view('pages.claims.email-action-result', [
                'success' => false,
                'message' => 'Claim not found'
            ]);
        } catch (Exception $e) {
            Log::error('Error processing email action', [
                'claim_id' => $id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return view('pages.claims.email-action-result', [
                'success' => false,
                'message' => 'An error occurred while processing the action: ' . $e->getMessage()
            ]);
        }
    }
} 