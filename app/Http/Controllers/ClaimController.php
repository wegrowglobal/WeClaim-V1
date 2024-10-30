<?php

namespace App\Http\Controllers;

use App\Mail\ClaimActionMail;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\User;
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
    
        if ($view === 'claims.dashboard') {
            $claims = Claim::with('user')->where('user_id', $user->id)->get(); 
        } elseif ($view === 'claims.approval') {
            $claims = Claim::with('user')->get();
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
        $claims = collect([$claim]);

        return view('pages.claims.claim', [
            'claim' => $claim,
            'claims' => $claims,
            'claimService' => $this->claimService,
        ]);
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

                    $claim->refresh();

                    $actionType = $request->claim_id ? 'resubmitted' : 'submitted';

                    Notification::send($claim->user, new ClaimStatusNotification($claim, $claim->status, $actionType));

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
            $claims = Claim::with('user')->get();
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
            return view('pages.claims.review', compact('claim', 'reviews'));
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

                $claim->refresh();

                Notification::send($claim->user, new ClaimStatusNotification($claim, $claim->status, $actionType));

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
        $rolesToNotify = [];

        switch ($actionType) {
            case 'approved':
                $nextRole = $this->claimService->getNextApproverRole($claim->status);
                if ($nextRole) {
                    $rolesToNotify[] = $nextRole;
                }
                break;

            case 'rejected':
                break;

            case 'resubmitted':
                $previousRejectorRole = $this->claimService->getPreviousRejectorRole($claim);
                if ($previousRejectorRole) {
                    $rolesToNotify[] = $previousRejectorRole;
                }
                break;

            default:
                break;
        }

        if (!empty($rolesToNotify)) {
            $usersToNotify = User::whereIn('role_id', function ($query) use ($rolesToNotify) {
                $query->select('id')->from('roles')->whereIn('name', $rolesToNotify);
            })->get();

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

    public function viewDocument(Claim $claim, $type, $filename)
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
        $claims = Claim::all();
        $statistics = [
            'totalClaims' => Claim::count(),
            'pendingReview' => Claim::where('status', '!=', Claim::STATUS_DONE)->count(),
            'approvedClaims' => Claim::where('status', Claim::STATUS_APPROVED_FINANCE)->count(),
            'totalAmount' => Claim::sum('petrol_amount') + Claim::sum('toll_amount'),
        ];

        return view('pages.claims.approval', [
            'claims' => $claims,
            'statistics' => $statistics,
            'claimService' => $this->claimService,
        ]);
    }

    //////////////////////////////////////////////////////////////////////////////////  

    public function dashboard()
    {
        $user = Auth::user();
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

        return view('pages.claims.dashboard', [
            'claims' => $claims,
            'statistics' => $statistics,
            'claimService' => $this->claimService,
        ]);
    }

    //////////////////////////////////////////////////////////////////////////////////  
    
    public function sendToDatuk($id)
    {
        $claim = Claim::findOrFail($id);
        $user = Auth::user();

        if ($user->role->name !== 'Admin') {
            return redirect()->route('claims.approval')->with('error', 'You do not have permission to send this claim to Datuk.');
        }

        // Send email to Datuk
        $this->claimService->sendClaimToDatuk($claim);

        return redirect()->route('claims.approval')->with('success', 'Claim sent to Datuk successfully.');
    }

    ////////////////////////////////////////////////////////////////////////////////// 

    public function handleEmailAction(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        $user = Auth::user();

        if ($user instanceof User) {
            if (!$this->claimService->canReviewClaim($user, $claim)) {
                return redirect()->route('claims.approval')->with('error', 'You do not have permission to review this claim.');
            }

            $action = $request->query('action');

            if (!in_array($action, ['approve', 'reject'])) {
                return redirect()->route('claims.approval')->with('error', 'Invalid action.');
            }

            DB::transaction(function () use ($action, $user, $claim) {
                if ($action === 'approve') {
                    $this->claimService->approveClaim($user, $claim);
                    $actionType = 'approved';
                } else {
                    $this->claimService->rejectClaim($user, $claim);
                    $actionType = 'rejected';
                }

                $this->claimService->storeRemarks($user, $claim, 'Action taken via email link.');

                $claim->refresh();

                Notification::send($claim->user, new ClaimStatusNotification($claim, $claim->status, $actionType));

                $this->notifyRoles($claim, $actionType);
            });

            return redirect()->route('claims.success.page')->with('success', 'Claim ' . $action . ' successfully.');
        } else {
            return route('login');
        }
    }

}
