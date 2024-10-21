<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\User;
use App\Mail\ClaimActionMail;
use App\Models\ClaimLocation;
use App\Services\ClaimService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;


class ClaimController extends Controller
{

    //////////////////////////////////////////////////////////////////

    protected $claimService;
    use AuthorizesRequests;

    private const ADMIN_EMAIL = 'ammar@wegrow-global.com';
    private const HR_EMAIL = 'ammar@wegrow-global.com';
    private const FINANCE_EMAIL = 'ammar@wegrow-global.com';
    private const TEST_EMAIL = 'ammar@wegrow-global.com';

    //////////////////////////////////////////////////////////////////

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }


    //////////////////////////////////////////////////////////////////

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
            $claims = $this->claimService->getClaimsBasedOnRole($user, $perPage);
        } else {
            abort(404);
        }

        return view($view, [
            'claims' => $claims,
            'claimService' => $this->claimService
        ]);
    }

    //////////////////////////////////////////////////////////////////

    public function show($id, $view)
    {
        $claim = Claim::findOrFail($id);
        return view($view, compact('claim'));
    }

    //////////////////////////////////////////////////////////////////

    public function store(StoreClaimRequest $request)
    {
        try {

            Log::info('Claim submission received', request()->all());

            DB::transaction(function () use ($request) {
                Log::info('Starting claim submission process');

                $validatedData = $request->validated();
                $user = Auth::user();

                if ($user instanceof User) {
                    $claim = $this->claimService->createOrUpdateClaim($validatedData, $user, $request->claim_id);
                    $claim = $this->claimService->handleFileUploadsAndDocuments($claim, $request->file('toll_report'), $request->file('email_report'));

                    /* Mail::to(self::ADMIN_EMAIL)->send(new ClaimActionMail($claim)); */
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

    //////////////////////////////////////////////////////////////////


    public function approvalScreen()
    {
        Log::info('Approval screen accessed by user: ' . Auth::id());

        $user = Auth::user();

        if ($user->role->name === 'Staff') {
            return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
        }

        if ($user instanceof User) {
            $perPage = 30; // Define how many items per page
            $claims = $this->claimService->getClaimsBasedOnRole($user, $perPage);
            return view('claims.approval', [
                'claims' => $claims,
                'claimService' => $this->claimService
            ]);
        } else {
            return redirect()->route('login');
        }
    }


    //////////////////////////////////////////////////////////////////

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

    //////////////////////////////////////////////////////////////////


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
                    $newStatus = $this->determineNewStatus($user, $claim);
                    $this->claimService->approveClaim($user, $claim);
                } else {
                    $this->claimService->rejectClaim($user, $claim);
                }

                $this->claimService->storeRemarks($user, $claim, $request->remarks);
            });

            return redirect()->route('claims.approval')->with('success', 'Claim ' . $request->action . ' successfully.');
        } else {
            return route('login');
        }
    }

    //////////////////////////////////////////////////////////////////

    private function determineNewStatus(User $user, Claim $claim)
{
    switch ($user->role->name) {
        case 'admin':
            return Claim::STATUS_APPROVED_ADMIN;
        case 'datuk':
            return Claim::STATUS_APPROVED_DATUK;
        case 'hr':
            return Claim::STATUS_APPROVED_HR;
        case 'finance':
            return Claim::STATUS_APPROVED_FINANCE;
        default:
            return $claim->status; // Keep current status if role is not recognized
    }
}

    //////////////////////////////////////////////////////////////////


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



    //////////////////////////////////////////////////////////////////
}
