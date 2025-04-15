<?php

namespace App\Http\Controllers\Claims;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim\Claim;
use App\Models\User\User;
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
use Illuminate\Validation\ValidationException;
use App\Services\ClaimPdfExportService;
use App\Traits\ChecksProfileCompletion;

class ClaimController extends Controller
{
    use AuthorizesRequests, ChecksProfileCompletion;
    protected $claimService;

    private function getAdminEmail()
    {
        return config('mail.addresses.admin');
    }

    private function getHrEmail()
    {
        return config('mail.addresses.hr');
    }

    private function getFinanceEmail()
    {
        return config('mail.addresses.finance');
    }

    private function getDatukEmail()
    {
        return config('mail.datuk_email');
    }

    //////////////////////////////////////////////////////////////////////////////////      

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
        
        // Apply middleware for all methods
        $this->middleware('auth');
        $this->middleware('track.activity');
        
        // Apply profile completion middleware for claim actions
        $this->middleware('profile.complete')->except([
            'home', 'show', 'viewDocument', 'handleEmailAction'
        ]);
        
        // Apply admin middleware for admin-only actions
        $this->middleware('admin')->only([
            'adminIndex', 'edit', 'update', 'destroy'
        ]);
    }

    public function index($view)
    {
        Log::info('Accessing claims index', ['view' => $view]);

        if (!Auth::check()) {
            Log::warning('Unauthenticated user attempted to access claims index');
            return redirect()->route('login.form');
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

    public function show($id)
    {
        try {
            $claim = Claim::with([
                'locations' => function ($query) {
                    $query->orderBy('order');
                },
                'accommodations',
                'documents',
                'user',
                'reviews'
            ])->findOrFail($id);

            // Return the review view directly instead of redirecting
            return view('pages.claims.actions.review', compact('claim')); 
        } catch (ModelNotFoundException $e) {
            Log::error('Claim not found in show method', ['claim_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('claims.dashboard')->with('error', 'Claim not found');
        } catch (\Exception $e) {
            Log::error('Error in show method', ['claim_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('claims.dashboard')->with('error', 'An error occurred while viewing the claim.');
        }
    }

    public function store(StoreClaimRequest $request): JsonResponse
    {
        try {
        $user = Auth::user();
            
            // Only allow Staff (role_id = 1) and Admin (role_id = 5) to create claims
            if (!in_array($user->role_id, [1, 5])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create claims.'
                ], 403);
            }

            $claim = $this->claimService->createClaim(
                $request->validated(),
                Auth::id()
            );

            // Clear session data
            $request->session()->forget([
                'claim_draft',
                'claim_data',
                'current_step',
                'map_data'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim submitted successfully',
                'redirect' => route('claims.show', $claim->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Claim submission failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit claim: ' . $e->getMessage()
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
            return redirect()->route('login.form');
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
            return view('pages.claims.actions.approval', [
            'claims' => $claims,
            'claimService' => $this->claimService
        ]);
        } else {
            Log::error('Invalid user instance when accessing approval screen');
            return redirect()->route('login.form');
        }
    }

    public function reviewClaim($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $claim = Claim::findOrFail($id);
            
            if (!$this->claimService->canReviewClaim(Auth::user(), $claim)) {
                return redirect()->route('claims.dashboard')
                    ->with('error', 'You do not have permission to review this claim');
            }

            return view('pages.claims.review', compact('claim'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('claims.dashboard')
                ->with('error', 'Claim not found');
        }
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
                'rejection_details' => 'required_if:action,reject|array',
                'rejection_details.requires_basic_info' => 'boolean',
                'rejection_details.requires_trip_details' => 'boolean',
                'rejection_details.requires_accommodation_details' => 'boolean',
                'rejection_details.requires_documents' => 'boolean',
            ]);

            DB::transaction(function () use ($request, $user, $claim) {
                if ($request->action === 'approve') {
                    $this->claimService->approveClaim($user, $claim);
                } else {
                    $this->claimService->rejectClaim($user, $claim, $request->rejection_details);
                }

                $claim->refresh();
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
            ], 500);
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

        return view('pages.claims.actions.approval', [
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

        return view('pages.claims.actions.dashboard', [
            'claims' => $claims,
            'statistics' => $statistics,
            'claimService' => $this->claimService,
        ]);
    }

    public function sendToDatuk(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role_id !== 3) { // Only HR can send to Datuk
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only HR can send claims to Datuk.'
                ], 403);
            }

            $claim = Claim::findOrFail($id);

            if ($claim->status !== Claim::STATUS_APPROVED_HR) {
            return response()->json([
                'success' => false,
                    'message' => 'Claim must be approved by HR before sending to Datuk.'
            ], 400);
        }
        
            // Update claim status to Pending Datuk
            $claim->status = Claim::STATUS_PENDING_DATUK;
            $claim->save();
        
            $this->claimService->sendClaimToDatuk($claim);
        
        return response()->json([
            'success' => true,
                'message' => 'Email sent to Datuk successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending claim to Datuk', [
                'claim_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email to Datuk: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleEmailAction(Request $request, $id, $action)
    {
        try {
            Log::info('Processing email action', [
                'claim_id' => $id,
                'action' => $action
            ]);

            $claim = Claim::findOrFail($id);
            $token = $request->query('token');

            if (!in_array($action, ['approve', 'reject'])) {
                Log::warning('Invalid action received', [
                    'claim_id' => $id,
                    'action' => $action
                ]);
                return view('pages.claims.actions.email-action', [
                    'success' => false,
                    'message' => 'Invalid action specified'
                ]);
            }

            if ($claim->status === Claim::STATUS_APPROVED_DATUK || $claim->status === Claim::STATUS_REJECTED) {
                Log::warning('Claim already processed', [
                    'claim_id' => $id,
                    'current_status' => $claim->status
                ]);
                return view('pages.claims.actions.email-action', [
                    'success' => false,
                    'message' => 'This claim is no longer awaiting approval or rejection'
                ]);
            }

            $success = $this->claimService->handleDatukAction($claim, $action, $token);

            if (!$success) {
                return view('pages.claims.actions.email-action', [
                    'success' => false,
                    'message' => 'Claim not found'
                ]);
            }

            $message = $action === 'approve' ? 
                'Claim status updated successfully' : 
                'Claim status updated successfully';

            Log::info('Email action processed successfully', [
                'claim_id' => $id,
                'action' => $action,
                'new_status' => $claim->status
            ]);

            return view('pages.claims.actions.email-action', [
                'success' => true,
                'message' => $message
            ]);

        } catch (ModelNotFoundException $e) {
            Log::error('Claim not found for email action', [
                'claim_id' => $id,
                'error' => $e->getMessage()
            ]);
            return view('pages.claims.actions.email-action', [
                'success' => false,
                'message' => 'Claim not found'
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing email action', [
                'claim_id' => $id,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return view('pages.claims.actions.email-action', [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function export(Request $request, Claim $claim)
    {
        try {
            if (!in_array($claim->status, [Claim::STATUS_APPROVED_FINANCE, Claim::STATUS_DONE])) {
            return response()->json([
                'success' => false,
                    'message' => 'Only claims with status "Approved Finance" or "Done" can be exported'
                ], 403);
            }

            $format = $request->input('format', 'excel');
            $mapper = new ClaimTemplateMapper();

            if ($format === 'pdf') {
                $pdfService = new ClaimPdfExportService($mapper, $claim);
                return $pdfService->exportToPdf();
            } else {
                $excelService = new ClaimExcelExportService($mapper, $claim);
                return $excelService->exportToExcel();
            }
        } catch (\Exception $e) {
            Log::error('Export failed:', [
                'claim_id' => $claim->id,
                'format' => $format,
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
                'approvedClaims' => $claims->whereIn('status', [
                    Claim::STATUS_APPROVED_FINANCE,
                    Claim::STATUS_DONE
                ])->count(),
                'pendingClaims' => $claims->whereNotIn('status', [
                    Claim::STATUS_APPROVED_FINANCE,
                    Claim::STATUS_REJECTED,
                    Claim::STATUS_DONE,
                    Claim::STATUS_CANCELLED
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
        // Check profile completion first
        if ($redirect = $this->checkProfileCompletion()) {
            return $redirect;
        }
        
        $user = Auth::user();
        
        // Only allow Staff (role_id = 1) and Admin (role_id = 5) to access claim creation
        if (!in_array($user->role_id, [1, 5])) {
            return redirect()->route('home')->with('error', 'You do not have permission to create claims.');
        }

        $step = $request->query('step', 1);
        $draftData = Session::get('claim_draft', []);

        // Validate step range
        if (!in_array($step, [1, 2, 3])) {
            return redirect()->route('claims.new', ['step' => 1]);
        }

        return view('pages.claims.process.new', [
            'currentStep' => (int) $step,
            'draftData' => $draftData
        ]);
    }

    public function getStep(Request $request, $step)
    {
        try {
            $draftData = Session::get('claim_draft', []);
           
            // Handle accommodations data specially when loading step 3
            if ($step == 3 && isset($draftData['accommodations'])) {
                // Ensure accommodations is properly decoded if it's a string
                if (is_string($draftData['accommodations'])) {
                    $draftData['accommodations'] = json_decode($draftData['accommodations'], true);
                }
                
                Log::info('Loading step 3 with accommodations', [
                    'accommodations_count' => count($draftData['accommodations']),
                    'accommodations' => $draftData['accommodations']
                ]);
            }

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

            Log::info('Saving step data', [
                'incoming_data' => $data,
                'step' => $data['current_step'] ?? null,
                'accommodations' => $data['accommodations'] ?? null
            ]);

            // Handle locations data specially
            if (isset($data['locations'])) {
                $data['locations'] = is_string($data['locations'])
                    ? json_decode($data['locations'], true)
                    : $data['locations'];
            }

            // Handle accommodations data specially
            if (isset($data['accommodations'])) {
                $data['accommodations'] = is_string($data['accommodations'])
                    ? json_decode($data['accommodations'], true)
                    : $data['accommodations'];
               
                // Ensure the accommodations data is preserved in the session
                $currentDraft['accommodations'] = $data['accommodations'];
            }
            // Don't remove accommodations data if it's not in the current request
            elseif (isset($currentDraft['accommodations'])) {
                $data['accommodations'] = $currentDraft['accommodations'];
            }

            // Ensure we're not overwriting existing data with null values
            $data = array_filter($data, function ($value) {
                return $value !== null && $value !== '';
            });

            // Merge new data with existing draft
            $updatedDraft = array_merge($currentDraft, $data);

            session(['claim_draft' => $updatedDraft]);

            Log::info('Updated draft data', [
                'draft' => $updatedDraft,
                'has_accommodations' => isset($updatedDraft['accommodations']),
                'accommodations_count' => isset($updatedDraft['accommodations']) ? count($updatedDraft['accommodations']) : 0
            ]);

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

    /**
     * Show the resubmit form for a rejected claim.
     */
    public function resubmit(Claim $claim)
    {
        // Validate if user can resubmit
        if ($claim->status !== Claim::STATUS_REJECTED || $claim->user_id !== Auth::id()) {
            return redirect()->route('claims.dashboard')
                ->with('error', 'You cannot resubmit this claim.');
        }

        // Get the latest rejection review to determine which sections need revision
        $latestRejection = $claim->reviews()
            ->where('status', Claim::STATUS_REJECTED)
            ->latest()
            ->first();

        return view('pages.claims.process.resubmit', [
            'claim' => $claim,
            'latestRejection' => $latestRejection
        ]);
    }

    /**
     * Process the resubmission of a claim.
     */
    public function processResubmission(Request $request, Claim $claim)
    {
        try {
            $latestRejection = $claim->reviews()
                ->where('status', Claim::STATUS_REJECTED)
                ->latest()
                ->first();

            $validationRules = [
            ];

            // Conditionally add validation rules based on required revisions
            if ($latestRejection->requires_basic_info) {
                $validationRules = array_merge($validationRules, [
                    'description' => 'required|string|max:500',
                    'date_from' => 'required|date',
                    'date_to' => 'required|date|after_or_equal:date_from',
                    'claim_company' => 'required|in:WGG,WGE,WGS',
                ]);
            }

            if ($latestRejection->requires_trip_details) {
                $validationRules = array_merge($validationRules, [
                    'distances' => 'required|array',
                    'distances.*' => 'required|numeric|min:0',
                    'locations' => 'required|array|min:2',
                    'locations.*' => 'required|string|max:255',
                'petrol_amount' => 'required|numeric|min:0',
                    'total_distance' => 'required|numeric|min:0',
                ]);
            }

            if ($latestRejection->requires_documents) {
                $validationRules = array_merge($validationRules, [
                    'has_toll' => 'required|boolean',
                    'toll_amount' => 'required_if:has_toll,true|nullable|numeric|min:0',
                    'toll_receipt' => 'required_if:has_toll,true|nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                    'email_approval' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
                ]);
                
                // Add conditional required if no existing documents
                if (!$claim->documents()->exists()) {
                    $validationRules['toll_receipt'] = 'required_if:has_toll,true|nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
                    $validationRules['email_approval'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
                }
            }

            // In the processResubmission method, modify the validation rules and accommodation handling:
            if ($latestRejection->requires_accommodation_details) {
                $validationRules = array_merge($validationRules, [
                    'accommodations' => 'sometimes|array|min:1',
                    'accommodations.*.location' => 'required_with:accommodations|string|max:255',
                    'accommodations.*.check_in' => 'required_with:accommodations|date',
                    'accommodations.*.check_out' => 'required_with:accommodations|date|after_or_equal:accommodations.*.check_in',
                    'accommodations.*.price' => 'required_with:accommodations|numeric|min:0',
                    'accommodations.*.receipt' => 'sometimes|file|mimes:pdf,jpg,jpeg,png|max:2048',
                ]);
            }

            $validated = $request->validate($validationRules);
            
            DB::beginTransaction();

            // Base update data
            $updateData = [
                'status' => Claim::STATUS_SUBMITTED,
                'submitted_at' => now()
            ];

            // Handle Basic Info revisions
            if ($latestRejection->requires_basic_info) {
                $updateData = array_merge($updateData, [
                    'description' => $validated['description'],
                    'date_from' => $validated['date_from'],
                    'date_to' => $validated['date_to'],
                    'claim_company' => $validated['claim_company']
                ]);
            }

            // Handle Trip Details revisions
            if ($latestRejection->requires_trip_details) {
                $updateData = array_merge($updateData, [
                    'petrol_amount' => $validated['petrol_amount'],
                    'total_distance' => $validated['total_distance']
                ]);

                $claim->locations()->delete();
                $locations = $validated['locations'];
                $distances = $validated['distances'];

                // Create location pairs for all consecutive locations
                for ($i = 0; $i < count($locations) - 1; $i++) {
                    $claim->locations()->create([
                        'from_location' => $locations[$i],
                        'to_location' => $locations[$i + 1],
                        'distance' => $distances[$i],
                        'order' => $i + 1,
                    ]);
                }

                // Add explicit check for last location pair
                if (count($locations) >= 2) {
                    $totalDistance = array_sum($distances);
                    $updateData['total_distance'] = $totalDistance;
                    $updateData['petrol_amount'] = $totalDistance * config('claims.rate_per_km', 0.60);
                }
            }

            // Handle Documents revisions
            if ($latestRejection->requires_documents) {
                // Handle document updates
                $tollFile = $request->file('toll_receipt');
                $emailFile = $request->file('email_approval');
                
                $claimDocument = $claim->documents()->firstOrNew();
                
                if ($tollFile) {
                    $tollPath = $tollFile->store('uploads/claims/toll', 'public');
                    $claimDocument->toll_file_name = $tollFile->getClientOriginalName();
                    $claimDocument->toll_file_path = $tollPath;
                }
                
                if ($emailFile) {
                    $emailPath = $emailFile->store('uploads/claims/email', 'public');
                    $claimDocument->email_file_name = $emailFile->getClientOriginalName();
                    $claimDocument->email_file_path = $emailPath;
                }
                
                $claimDocument->save();
                
                // Update toll amount from validated data
                $claim->toll_amount = $validated['toll_amount'];
                $claim->save();
            }

            // Handle accommodations revisions
            if ($latestRejection->requires_accommodation_details) {
                // Handle accommodation removal
                if ($request->input('remove_all_accommodations') == '1') {
                    $claim->accommodations()->delete();
                    Log::info('All accommodations removed per user request', ['claim_id' => $claim->id]);
                }
                // Handle new accommodations
                elseif ($request->has('accommodations')) {
                    $claim->accommodations()->delete();
                    foreach ($validated['accommodations'] as $accomData) {
                        $newAccommodation = $claim->accommodations()->create([
                            'location' => $accomData['location'],
                            'check_in' => $accomData['check_in'],
                            'check_out' => $accomData['check_out'],
                            'price' => $accomData['price'],
                        ]);

                        // Handle receipt upload if provided
                        if (isset($accomData['receipt']) && $accomData['receipt'] instanceof \Illuminate\Http\UploadedFile) {
                            $receiptPath = $accomData['receipt']->store('uploads/claims/accommodations', 'public');
                            $newAccommodation->update([
                                'receipt_path' => $receiptPath,
                                'receipt_name' => $accomData['receipt']->getClientOriginalName()
                            ]);
                        }
                    }
                }
                // Preserve existing if neither removal nor new accommodations
                else {
                    Log::info('Preserving existing accommodations', ['claim_id' => $claim->id]);
                }
            }

            // Update the claim with accumulated data
            $claim->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => route('claims.dashboard'),
                'message' => 'Claim resubmitted successfully'
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resubmission error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Resubmission failed'
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
        $claims = Claim::with('user')->orderBy('created_at', 'desc')->get();
        return view('pages.claims.admin', [
            'claims' => $claims,
            'claimService' => $this->claimService
        ]);
    }

    public function edit(Claim $claim)
    {
        try {
            $claimData = [
                'id' => $claim->id,
                'claim_company' => $claim->claim_company,
                'date_from' => $claim->date_from->format('Y-m-d'),
                'date_to' => $claim->date_to->format('Y-m-d'),
                'total_distance' => $claim->total_distance,
                'total_amount' => $claim->total_amount,
                'remarks' => $claim->remarks,
                'status' => $claim->status,
                'description' => $claim->description
            ];

            return response()->json($claimData);
        } catch (\Exception $e) {
            Log::error('Error fetching claim for edit:', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading claim details'
            ], 500);
        }
    }

    public function update(Claim $claim, Request $request)
    {
        try {
            $validatedData = $request->validate([
                'claim_company' => 'required|string|in:WGG,WGE,WGS',
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'total_distance' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'remarks' => 'nullable|string',
                'status' => 'required|string|in:submitted,approved_admin,approved_datuk,approved_hr,approved_finance,rejected,done'
            ]);

            $claim->update($validatedData);

            // Log the update
            Log::info('Claim updated successfully', [
                'claim_id' => $claim->id,
                'updated_by' => Auth::id(),
                'changes' => $validatedData
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Claim updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating claim:', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating claim'
            ], 500);
        }
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

    public function loadStep(Request $request, $step)
    {
        try {
            $draftData = session('claim_draft', []);
            Log::info('Loading step', [
                'step' => $step,
                'draft_data' => $draftData,
                'has_accommodations' => isset($draftData['accommodations']),
                'accommodations_count' => isset($draftData['accommodations']) ? count($draftData['accommodations']) : 0
            ]);

            return view('components.forms.claim.step-' . $step, [
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

    public function showResubmitForm(Claim $claim)
    {
        // Validate if user can resubmit
        if ($claim->status !== Claim::STATUS_REJECTED || $claim->user_id !== Auth::id()) {
            return redirect()->route('claims.dashboard')
                ->with('error', 'You cannot resubmit this claim.');
        }

        // Get the latest rejection review
        $latestRejection = $claim->reviews()
            ->where('status', Claim::STATUS_REJECTED)
            ->latest()
            ->first();

        // Load accommodations relationship
        $claim->load('accommodations');

        return view('pages.claims.process.resubmit', [
            'claim' => $claim,
            'latestRejection' => $latestRejection,
            'accommodations' => $claim->accommodations
        ]);
    }
}
