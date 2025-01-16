<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimService;
use App\Notifications\ClaimStatusNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Exceptions\Exception as ExceptionsException;
use Exception;
use Illuminate\Foundation\Configuration\Exceptions;
use App\Services\ClaimTemplateMapper;
use App\Services\ClaimExcelExportService;
use App\Services\ClaimWordExportService;

class ClaimController extends Controller
{
    use AuthorizesRequests;
    protected $claimService;


    private const ADMIN_EMAIL = 'ammar@wegrow-global.com';
    private const HR_EMAIL = 'ammar@wegrow-global.com';
    private const FINANCE_EMAIL = 'ammar@wegrow-global.com';
    private const TEST_EMAIL = 'ammar@wegrow-global.com';

    //////////////////////////////////////////////////////////////////////////////////      

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    public function index($view)
    {
        Log::info('Accessing claims index', ['view' => $view]);

        if (!Auth::check()) {
            Log::warning('Unauthenticated user attempted to access claims index');
            return redirect()->route('login');
        }

        $user = Auth::user();
        Log::info('User accessing claims index', ['user_id' => $user->id, 'role' => $user->role->name]);

        if ($view === 'claims.dashboard') {
            $claims = Claim::with('user')->where('user_id', $user->id)->get();
            Log::info('Retrieved user claims', ['count' => $claims->count()]);
        } elseif ($view === 'claims.approval') {
            $claims = Claim::with('user')->get();
            Log::info('Retrieved all claims for approval', ['count' => $claims->count()]);
        } else {
            Log::error('Invalid view requested', ['view' => $view]);
            abort(404);
        }

        return view($view, [
            'claims' => $claims,
            'claimService' => $this->claimService
        ]);
    }

    public function home()
    {
        $statistics = $this->getHomePageStatistics();
        $user = Auth::user();

        $data = [
            'totalClaims' => $statistics['totalClaims'],
            'approvedClaims' => $statistics['approvedClaims'],
            'pendingClaims' => $statistics['pendingClaims'],
            'rejectedClaims' => $statistics['rejectedClaims'],
            'claimService' => $this->claimService
        ];

        // Add additional statistics for non-staff users
        if ($user && $user->role->name !== 'Staff') {
            $data['pendingReview'] = Claim::where('status', '!=', Claim::STATUS_DONE)
                ->where('status', '!=', Claim::STATUS_REJECTED)
                ->count();
            $data['totalAmount'] = Claim::sum(DB::raw('petrol_amount + toll_amount'));
            $data['pendingClaims'] = Claim::where('status', '!=', Claim::STATUS_DONE)
                ->where('status', '!=', Claim::STATUS_REJECTED)
                ->get();
        }

        return view('pages.home', $data);
    }

    public function show($id, $view)
    {
        Log::info('Showing claim details', ['claim_id' => $id, 'view' => $view]);

        $claim = Claim::with(['locations' => function ($query) {
            $query->orderBy('order');
        }])->findOrFail($id);

        $claims = collect([$claim]);

        // Prepare location data for the map
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

        Log::info('Retrieved claim for viewing', [
            'claim_id' => $claim->id,
            'user_id' => $claim->user_id,
            'status' => $claim->status,
            'locations_count' => count($locationData)
        ]);

        return view('pages.claims.claim', [
            'claim' => $claim,
            'claims' => $claims,
            'claimService' => $this->claimService,
            'locationData' => $locationData
        ]);
    }

    public function store(StoreClaimRequest $request): JsonResponse
    {
        try {
            Log::info('Claim submission started', [
                'user_id' => Auth::id(),
                'data' => $request->except(['toll_file', 'email_file'])
            ]);

            $claim = $this->claimService->createClaim(
                $request->validated(),
                Auth::id()
            );

            Log::info('Claim submitted successfully', ['claim_id' => $claim->id]);

            // Clear all session data related to the claim
            $request->session()->forget([
                'claim_draft',
                'claim_data',
                'current_step',
                'map_data',
                // Add any other claim-related session keys
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim submitted successfully',
                'claim_id' => $claim->id
            ]);
        } catch (\Exception $e) {
            Log::error('Claim submission failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'data' => $request->except(['toll_file', 'email_file'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit claim: ' . $e->getMessage(),
                'debug_message' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function approvalScreen()
    {
        $userId = Auth::id();
        Log::info('Accessing approval screen', ['user_id' => $userId]);

        $user = Auth::user();

        if (!$user) {
            Log::warning('Unauthenticated user attempted to access approval screen');
            return redirect()->route('login');
        }

        if ($user->role_id === 1) {
            Log::warning('Staff member attempted to access approval screen', ['user_id' => $userId]);
            return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
        }

        if ($user instanceof User) {
            $claims = Claim::with('user')->get();
            Log::info('Retrieved claims for approval screen', [
                'claims_count' => $claims->count(),
                'user_role' => $user->role->name
            ]);
            return view('claims.approval', [
                'claims' => $claims,
                'claimService' => $this->claimService
            ]);
        } else {
            Log::error('Invalid user instance when accessing approval screen');
            return redirect()->route('login');
        }
    }

    public function reviewClaim($id)
    {
        Log::info('Accessing claim review', ['claim_id' => $id]);

        $user = Auth::user();
        $claim = Claim::with(['locations' => function ($query) {
            $query->orderBy('order');
        }])->findOrFail($id);

        // Debug the locations data
        Log::info('Claim locations data:', [
            'claim_id' => $id,
            'locations' => $claim->locations->toArray()
        ]);

        if ($user instanceof User) {
            if (!$this->claimService->canReviewClaim($user, $claim)) {
                Log::warning('Unauthorized claim review attempt', [
                    'user_id' => $user->id,
                    'claim_id' => $id,
                    'user_role' => $user->role->name
                ]);
                return redirect()->route('claims.approval')
                    ->with('error', 'You do not have permission to review this claim.');
            }

            $reviews = $claim->reviews()->orderBy('department')->orderBy('review_order')->get();

            // Prepare location data for the map
            $locationData = $claim->locations->map(function ($location) {
                return [
                    'order' => $location->order,
                    'from_location' => $location->from_location,
                    'to_location' => $location->to_location,
                    'distance' => $location->distance,
                    'from_latitude' => $location->from_latitude,
                    'from_longitude' => $location->from_longitude,
                    'to_latitude' => $location->to_latitude,
                    'to_longitude' => $location->to_longitude,
                ];
            })->values()->all();

            return view('pages.claims.review', compact('claim', 'reviews', 'locationData'));
        }

        Log::error('Invalid user instance during claim review');
        return route('login');
    }

    public function updateClaim(Request $request, $id)
    {
        try {
            $claim = Claim::findOrFail($id);
            $user = Auth::user();

            if (!$user instanceof User) {
                throw new Exception('Invalid user instance');
            }

            if (!$this->claimService->canReviewClaim($user, $claim)) {
                throw new Exception('You do not have permission to review this claim.');
            }

            $request->validate([
                'remarks' => 'required|string',
                'action' => 'required|in:approve,reject',
            ]);

            DB::transaction(function () use ($request, $user, $claim) {
                if ($request->action === 'approve') {
                    $this->claimService->approveClaim($user, $claim);
                } else {
                    $this->claimService->rejectClaim($user, $claim);
                    return;
                }

                $this->claimService->storeRemarks($user, $claim, $request->remarks);
                $claim->refresh();

                $notificationService = app(NotificationService::class);
                $notificationService->sendClaimStatusNotification($claim, $claim->status, 'approve');
            });

            return response()->json([
                'success' => true,
                'message' => 'Claim ' . $request->action . 'd successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating claim', [
                'claim_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function viewDocument(Claim $claim, $type, $filename)
    {
        Log::info('Document view request', [
            'claim_id' => $claim->id,
            'document_type' => $type,
            'filename' => $filename
        ]);

        $document = $claim->documents()->first();

        if (!$document) {
            Log::warning('Document not found', [
                'claim_id' => $claim->id,
                'type' => $type
            ]);
            abort(404, 'Document not found');
        }

        $filePath = storage_path('app/public/' . $document->{$type . '_file_path'});

        if (!file_exists($filePath)) {
            Log::error('Physical file missing', [
                'claim_id' => $claim->id,
                'path' => $filePath
            ]);
            abort(404, 'File not found');
        }

        Log::info('Serving document', [
            'claim_id' => $claim->id,
            'file_path' => $filePath
        ]);

        return response()->file($filePath);
    }

    public function approval()
    {
        Log::info('Accessing approval overview page');

        $claims = Claim::all();
        $statistics = [
            'totalClaims' => Claim::count(),
            'pendingReview' => Claim::where('status', '!=', Claim::STATUS_DONE)->count(),
            'approvedClaims' => Claim::where('status', Claim::STATUS_APPROVED_FINANCE)->count(),
            'totalAmount' => Claim::sum('petrol_amount') + Claim::sum('toll_amount'),
        ];

        Log::info('Retrieved approval statistics', $statistics);

        return view('pages.claims.approval', [
            'claims' => $claims,
            'statistics' => $statistics,
            'claimService' => $this->claimService,
        ]);
    }

    public function dashboard()
    {
        $user = Auth::user();
        Log::info('Accessing user dashboard', ['user_id' => $user->id]);

        $claims = Claim::where('user_id', $user->id)->get();
        $statistics = [
            'totalClaims' => Claim::where('user_id', $user->id)->count(),
            'pendingReview' => Claim::where('user_id', $user->id)
                ->where('status', '!=', Claim::STATUS_DONE)
                ->count(),
            'approvedClaims' => Claim::where('user_id', $user->id)
                ->where('status', Claim::STATUS_APPROVED_FINANCE)
                ->count(),
            'totalAmount' => Claim::where('user_id', $user->id)
                ->sum(DB::raw('petrol_amount + toll_amount')),
        ];

        Log::info('Retrieved user dashboard statistics', [
            'user_id' => $user->id,
            'statistics' => $statistics
        ]);

        return view('pages.claims.dashboard', [
            'claims' => $claims,
            'statistics' => $statistics,
            'claimService' => $this->claimService,
        ]);
    }

    public function sendToDatuk(Request $request, $id)
    {
        try {
            Log::info('Attempting to send claim to Datuk', [
                'claim_id' => $id,
                'user_id' => Auth::id()
            ]);

            $claim = Claim::findOrFail($id);

            if (!Auth::user() || Auth::user()->role->name !== 'Admin') {
                throw new \Exception('Unauthorized action.');
            }

            Log::info('Claim found', [
                'claim_id' => $claim->id,
                'user_id' => $claim->user_id,
                'status' => $claim->status
            ]);

            $this->claimService->sendClaimToDatuk($claim);

            return response()->json([
                'success' => true,
                'message' => 'Claim sent to Datuk successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Claim not found', [
                'claim_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Claim not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in sendToDatuk controller', [
                'claim_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function handleEmailAction(Request $request, $id)
    {
        Log::info('Processing email action', [
            'claim_id' => $id,
            'action' => $request->query('action')
        ]);

        $claim = Claim::findOrFail($id);
        $action = $request->query('action');

        // Check if claim has already been processed by Datuk for the current submission
        $latestReview = $claim->reviews()
            ->where('department', 'Email Approval')
            ->where('status', Claim::STATUS_APPROVED_DATUK)
            ->where('created_at', '>', $claim->submitted_at) // Only check reviews after last submission
            ->latest()
            ->first();

        if ($latestReview) {
            return view('pages.claims.email-action', [
                'alreadyProcessed' => true,
                'claim' => $claim,
                'message' => 'This claim has already been processed. No further action is required.',
            ]);
        }

        try {
            DB::transaction(function () use ($action, $claim) {
                if ($action === 'approve') {
                    $claim->status = Claim::STATUS_APPROVED_DATUK;

                    // Get HR as next reviewer
                    $nextReviewer = User::whereHas('role', function ($query) {
                        $query->where('name', 'HR');
                    })->first();

                    if ($nextReviewer) {
                        $claim->reviewer_id = $nextReviewer->id;

                        // Notify HR users
                        $hrUsers = User::whereHas('role', function ($query) {
                            $query->where('name', 'HR');
                        })->get();

                        foreach ($hrUsers as $hrUser) {
                            if ($hrUser) {
                                $hrUser->notify(new ClaimStatusNotification(
                                    $claim,
                                    $claim->status,
                                    'pending_review_hr',
                                    false
                                ));
                            } else {
                                // Handle the case when no HR user is found
                                Log::warning('No HR user found to notify for claim approval', ['claim_id' => $claim->id]);
                            }
                        }
                    }
                } else {
                    $claim->status = Claim::STATUS_APPROVED_ADMIN;

                    // Set reviewer back to Admin
                    $nextReviewer = User::whereHas('role', function ($query) {
                        $query->where('name', 'Admin');
                    })->first();

                    if ($nextReviewer) {
                        $claim->reviewer_id = $nextReviewer->id;
                    }
                }

                $claim->save();

                // Create review record
                $claim->reviews()->create([
                    'reviewer_id' => null,
                    'remarks' => ($action === 'approve' ? 'Approved' : 'Rejected by Datuk'),
                    'department' => 'Email Approval',
                    'review_order' => $claim->reviews()->count() + 1,
                    'status' => $claim->status,
                    'reviewed_at' => now()
                ]);

                // Send notifications
                $notificationService = app(NotificationService::class);
                if ($action === 'approve') {
                    $notificationService->sendClaimStatusNotification(
                        $claim,
                        $claim->status,
                        'approved_datuk'
                    );
                } else {
                    $notificationService->sendClaimStatusNotification(
                        $claim,
                        $claim->status,
                        'rejected_datuk'
                    );
                }
            });

            return view('pages.claims.email-action', [
                'success' => true,
                'claim' => $claim,
                'message' => $action === 'approve'
                    ? 'Claim has been approved successfully.'
                    : 'Claim has been rejected and sent back to Admin.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing email action', [
                'claim_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('pages.claims.email-action', [
                'error' => true,
                'message' => 'An error occurred while processing your request.'
            ]);
        }
    }

    public function export(Claim $claim)
    {
        try {
            if ($claim->status !== Claim::STATUS_DONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only completed claims can be exported'
                ], 403);
            }

            $excelService = new ClaimExcelExportService(
                new ClaimTemplateMapper(),
                $claim
            );

            return $excelService->exportToExcel();
        } catch (\Exception $e) {
            Log::error('Excel Export failed:', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export claim: ' . $e->getMessage()
            ], 500);
        }
    }

    private function debugTemplate($worksheet)
    {
        foreach ($worksheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $value = $cell->getValue();
                if (is_string($value) && (
                    str_contains($value, '${') ||
                    str_contains($value, '}')
                )) {
                    Log::info("Found placeholder in " . $cell->getCoordinate(), [
                        'Value' => $value
                    ]);
                }
            }
        }
    }

    public function getHomePageStatistics()
    {
        Log::info('Retrieving home page statistics');

        $user = Auth::user();

        if (!$user) {
            Log::info('No authenticated user, returning empty statistics');
            return [
                'totalClaims' => 0,
                'approvedClaims' => 0,
                'pendingClaims' => 0,
                'rejectedClaims' => 0
            ];
        }

        try {
            // For staff, show only their claims
            if ($user->role->name === 'Staff') {
                $claims = Claim::where('user_id', $user->id);
            } else {
                // For admin/managers, show all claims
                $claims = new Claim;
            }

            $statistics = [
                'totalClaims' => $claims->count(),
                'approvedClaims' => $claims->where('status', Claim::STATUS_APPROVED_FINANCE)->count(),
                'pendingClaims' => $claims->whereNotIn('status', [
                    Claim::STATUS_APPROVED_FINANCE,
                    Claim::STATUS_REJECTED,
                    Claim::STATUS_DONE
                ])->count(),
                'rejectedClaims' => $claims->where('status', Claim::STATUS_REJECTED)->count()
            ];

            Log::info('Home page statistics retrieved successfully', [
                'user_id' => $user->id,
                'role' => $user->role->name,
                'statistics' => $statistics
            ]);

            return $statistics;
        } catch (\Exception $e) {
            Log::error('Error retrieving home page statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'totalClaims' => 0,
                'approvedClaims' => 0,
                'pendingClaims' => 0,
                'rejectedClaims' => 0
            ];
        }
    }

    public function new(Request $request)
    {
        $step = $request->query('step', 1);
        $draftData = Session::get('claim_draft', []);

        // Validate step range
        if (!in_array($step, [1, 2, 3])) {
            return redirect()->route('claims.new', ['step' => 1]);
        }

        return view('pages.claims.new', [
            'currentStep' => (int) $step,
            'draftData' => $draftData
        ]);
    }

    public function getStep(Request $request, $step)
    {
        try {
            $draftData = Session::get('claim_draft', []);

            return view("components.forms.claim.step-{$step}", [
                'draftData' => $draftData,
                'currentStep' => (int) $step
            ])->render();
        } catch (\Exception $e) {
            Log::error('Error loading step', [
                'step' => $step,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error loading step'
            ], 500);
        }
    }

    public function saveStep(Request $request)
    {
        try {
            $currentDraft = session('claim_draft', []);
            $data = $request->all();

            Log::info('Saving step data', ['incoming_data' => $data]); // Debug log

            // Handle locations data specially
            if (isset($data['locations'])) {
                $data['locations'] = is_string($data['locations'])
                    ? json_decode($data['locations'], true)
                    : $data['locations'];
            }

            // Ensure we're not overwriting existing data with null values
            $data = array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });

            // Merge new data with existing draft
            $updatedDraft = array_merge($currentDraft, $data);

            session(['claim_draft' => $updatedDraft]);

            Log::info('Updated draft data', ['draft' => $updatedDraft]); // Debug log

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error saving claim step', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft data'
            ], 500);
        }
    }

    public function resetSession(Request $request)
    {
        try {
            Session::forget(['claim_draft', 'current_step']);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('claims.new', ['step' => 1]);
        } catch (\Exception $e) {
            Log::error('Error resetting session', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error resetting form'
            ], 500);
        }
    }

    public function getProgressSteps($step)
    {
        return view('components.forms.progress-steps', [
            'currentStep' => (int) $step
        ])->render();
    }

    public function resubmit(Claim $claim)
    {
        // Validate if user can resubmit
        if ($claim->status !== Claim::STATUS_REJECTED || $claim->user_id !== Auth::id()) {
            return redirect()->route('claims.dashboard')
                ->with('error', 'You cannot resubmit this claim.');
        }

        return view('pages.claims.resubmit', [
            'claim' => $claim,
            'rejectionReason' => $claim->reviews()
                ->where('status', Claim::STATUS_REJECTED)
                ->latest()
                ->first()?->remarks
        ]);
    }

    public function processResubmission(Request $request, Claim $claim)
    {
        try {
            $this->claimService->handleResubmission($claim, [
                'description' => $request->description,
                'claim_company' => $request->claim_company,
                'petrol_amount' => $request->petrol_amount,
                'toll_amount' => $request->toll_amount,
                'total_distance' => $request->total_distance,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'locations' => $request->locations
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim resubmitted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Claim resubmission failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'claim_id' => $claim->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resubmit claim: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelClaim(Claim $claim)
    {
        // Check if the authenticated user is the owner of the claim
        if (Auth::id() !== $claim->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Update the claim status to "Cancelled"
        $claim->status = Claim::STATUS_CANCELLED;
        $claim->save();

        return redirect()->route('claims.dashboard')->with('success', 'Claim has been cancelled.');
    }

    public function adminIndex()
    {
        if (Auth::user()->role_id !== 5) {
            abort(403);
        }

        $claims = Claim::with(['user', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.claims.admin', [
            'claims' => $claims,
            'claimService' => $this->claimService
        ]);
    }

    public function destroy(Claim $claim)
    {
        if (Auth::user()->role_id !== 5) {
            abort(403);
        }

        try {
            $claim->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting claim: ' . $e->getMessage()
            ], 500);
        }
    }
}
