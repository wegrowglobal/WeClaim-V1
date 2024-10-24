<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\User;
use App\Mail\ClaimActionMail;
use App\Models\ClaimLocation;
use App\Services\ClaimService;
use App\Notifications\ClaimStatusNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;


class ClaimController extends Controller
{

    //////////////////////////////////////////////////////////////////////////////////  

    protected $claimService;
    use AuthorizesRequests;

    private const ADMIN_EMAIL = 'ammar@wegrow-global.com';
    private const HR_EMAIL = 'ammar@wegrow-global.com';
    private const FINANCE_EMAIL = 'ammar@wegrow-global.com';
    private const TEST_EMAIL = 'ammar@wegrow-global.com';

    //////////////////////////////////////////////////////////////////////////////////      

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }


    //////////////////////////////////////////////////////////////////////////////////  

    public function index($view)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $perPage = 30;

        if ($view === 'claims.dashboard') {
            $claims = Claim::with('user')->where('user_id', $user->id)->paginate($perPage);
        } elseif ($view === 'claims.approval') {
            $claims = Claim::with('user')->paginate($perPage);
        } else {
            abort(404);
        }

        return view($view, [
            'claims' => $claims,
            'claimService' => $this->claimService
        ]);
    }

    //////////////////////////////////////////////////////////////////////////////////  

    public function show($id, $view)
    {
        $claim = Claim::findOrFail($id);
        return view($view, compact('claim'));
    }

    //////////////////////////////////////////////////////////////////////////////////      

    public function store(StoreClaimRequest $request)
    {
        try {
            Log::info('Claim submission received', $request->all());

            DB::transaction(function () use ($request) {
                Log::info('Starting claim submission process');

                $validatedData = $request->validated();
                $user = Auth::user();

                if ($user instanceof User) {
                    $claim = $this->claimService->createOrUpdateClaim($validatedData, $user, $request->claim_id);
                    $claim = $this->claimService->handleFileUploadsAndDocuments($claim, $request->file('toll_report'), $request->file('email_report'));

                    // Refresh the claim to get the updated status
                    $claim->refresh();

                    // Send a resubmission notification if it's an update
                    $actionType = $request->claim_id ? 'resubmitted' : 'submitted';

                    // Send notification to the claim owner
                    Notification::send($claim->user, new ClaimStatusNotification($claim, $claim->status, $actionType));

                    // Notify roles if necessary
                    $this->notifyRoles($claim, $actionType);
                } else {
                    return route('login');
                }
            });

            return redirect()->route('claims.dashboard')->with('success', 'Claim submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Error submitting claim: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while submitting the claim. Please try again.');
        }
    }


    //////////////////////////////////////////////////////////////////////////////////  


    public function approvalScreen()
    {
        Log::info('Approval screen accessed by user: ' . Auth::id());

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role->name === 'Staff') {
            return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
        }

        if ($user instanceof User) {
            $perPage = 30; // Define how many items per page
            $claims = Claim::with('user')->paginate($perPage);
            return view('claims.approval', [
                'claims' => $claims,
                'claimService' => $this->claimService
            ]);
        } else {
            return redirect()->route('login');
        }
    }


    //////////////////////////////////////////////////////////////////////////////////  

    public function reviewClaim($id)
    {
        $user = Auth::user();
        $claim = Claim::with('locations')->findOrFail($id);

        if ($user instanceof User) {
            if (!$this->claimService->canReviewClaim($user, $claim)) {
                return redirect()->route('claims.approval')->with('error', 'You do not have permission to review this claim.');
            }

            $reviews = $claim->reviews()->orderBy('department')->orderBy('review_order')->get();
            return view('claims.review', compact('claim', 'reviews'));
        } else {
            return route('login');
        }
    }

    //////////////////////////////////////////////////////////////////////////////////      


    public function updateClaim(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        $user = Auth::user();

        if ($user instanceof User) {
            if (!$this->claimService->canReviewClaim($user, $claim)) {
                return redirect()->route('claims.approval')->with('error', 'You do not have permission to review this claim.');
            }

            $request->validate([
                'remarks' => 'required|string',
                'action' => 'required|in:approve,reject',
            ]);

            DB::transaction(function () use ($request, $user, $claim) {
                if ($request->action === 'approve') {
                    $this->claimService->approveClaim($user, $claim);
                    $actionType = 'approved';
                } else {
                    $this->claimService->rejectClaim($user, $claim);
                    $actionType = 'rejected';
                }

                $this->claimService->storeRemarks($user, $claim, $request->remarks);

                // Refresh the claim to get the updated status
                $claim->refresh();

                // Send notification to the claim owner
                Notification::send($claim->user, new ClaimStatusNotification($claim, $claim->status, $actionType));

                // Send notification to specific roles if needed
                $this->notifyRoles($claim, $actionType);
            });

            return redirect()->route('claims.approval')->with('success', 'Claim ' . $request->action . ' successfully.');
        } else {
            return route('login');
        }
    }

    //////////////////////////////////////////////////////////////////////////////////

    private function notifyRoles(Claim $claim, string $actionType)
    {
        // Initialize an empty array for roles to notify
        $rolesToNotify = [];

        switch ($actionType) {
            case 'approved':
                // Notify next approver based on the workflow
                $nextRole = $this->claimService->getNextApproverRole($claim->status);
                if ($nextRole) {
                    $rolesToNotify[] = $nextRole;
                }
                break;

            case 'rejected':
                // Do not notify other roles when a claim is rejected
                // Only the claim owner is notified elsewhere
                break;

            case 'resubmitted':
                // Notify the role that previously rejected the claim
                $previousRejectorRole = $this->claimService->getPreviousRejectorRole($claim);
                if ($previousRejectorRole) {
                    $rolesToNotify[] = $previousRejectorRole;
                }
                break;

            default:
                break;
        }

        if (!empty($rolesToNotify)) {
            // Fetch users with the specified roles
            $usersToNotify = User::whereIn('role_id', function ($query) use ($rolesToNotify) {
                $query->select('id')->from('roles')->whereIn('name', $rolesToNotify);
            })->get();

            // Send notifications to these users
            foreach ($usersToNotify as $userToNotify) {
                Notification::send($userToNotify, new ClaimStatusNotification($claim, $claim->status, $actionType, false));
            }
        }
    }


    //////////////////////////////////////////////////////////////////////////////////      


    public function approveClaim($id)
    {
        $user = Auth::user();
        $claim = Claim::findOrFail($id);

        if ($user instanceof User) {
            if (!$this->claimService->canReviewClaim($user, $claim)) {
                return redirect()->route('claims.approval')->with('error', 'You do not have permission to approve this claim.');
            }

            $updatedClaim = $this->claimService->approveClaim($user, $claim);

            return redirect()->route('claims.approval')->with('success', 'Claim approved successfully.');
        } else {
            return route('login');
        }
    }

    //////////////////////////////////////////////////////////////////////////////////  

    public function viewDocument(Claim $claim, $type)
    {

        $document = $claim->documents()->first();

        if (!$document) {
            abort(404, 'Document not found');
        }

        $filePath = storage_path('app/public/' . $document->{$type . '_file_path'});

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->file($filePath);
    }

    //////////////////////////////////////////////////////////////////////////////////

    public function approval()
    {
        $claims = Claim::paginate(10); // Adjust pagination as needed
        $statistics = [
            'totalClaims' => Claim::count(),
            'pendingReview' => Claim::where('status', '!=', Claim::STATUS_DONE)->count(),
            'approvedClaims' => Claim::where('status', Claim::STATUS_APPROVED_FINANCE)->count(),
            'totalAmount' => Claim::sum('petrol_amount') + Claim::sum('toll_amount'),
        ];

        return view('pages.claims.approval', compact('claims', 'statistics'));
    }

    //////////////////////////////////////////////////////////////////////////////////  

    public function dashboard()
    {
        $claims = Claim::paginate(10); // Adjust pagination as needed
        $statistics = [
            'totalClaims' => Claim::count(),
            'pendingReview' => Claim::where('status', '!=', Claim::STATUS_DONE)->count(),
            'approvedClaims' => Claim::where('status', Claim::STATUS_APPROVED_FINANCE)->count(),
            'totalAmount' => Claim::sum('petrol_amount') + Claim::sum('toll_amount'),
        ];

        return view('pages.claims.dashboard', compact('claims', 'statistics'));
    }

    //////////////////////////////////////////////////////////////////////////////////  
}
