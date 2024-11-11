<?php

namespace App\Http\Controllers;

use App\Mail\ClaimActionMail;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\User;
use App\Models\ClaimLocation;
use App\Models\Role;
use App\Models\Department;
use App\Services\ClaimService;
use App\Notifications\ClaimStatusNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
        Log::info('ClaimController instantiated', ['service' => get_class($claimService)]);
        $this->claimService = $claimService;
    }


    //////////////////////////////////////////////////////////////////////////////////  

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

    //////////////////////////////////////////////////////////////////////////////////  

    public function show($id, $view)
    {
        Log::info('Showing claim details', ['claim_id' => $id, 'view' => $view]);

        $claim = Claim::findOrFail($id);
        $claims = collect([$claim]);

        Log::info('Retrieved claim for viewing', [
            'claim_id' => $claim->id,
            'user_id' => $claim->user_id,
            'status' => $claim->status
        ]);

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
            Log::info('Starting claim submission', [
                'user_id' => Auth::id(),
                'claim_id' => $request->claim_id,
                'has_toll_report' => $request->hasFile('toll_report'),
                'has_email_report' => $request->hasFile('email_report')
            ]);

            DB::transaction(function () use ($request) {
                $validatedData = $request->validated();
                $user = Auth::user();

                if ($user instanceof User) {
                    Log::info('Creating/updating claim', [
                        'user_id' => $user->id,
                        'claim_id' => $request->claim_id
                    ]);

                    $claim = $this->claimService->createOrUpdateClaim($validatedData, $user, $request->claim_id);
                    
                    Log::info('Handling file uploads', [
                        'claim_id' => $claim->id
                    ]);
                    
                    $claim = $this->claimService->handleFileUploadsAndDocuments($claim, $request->file('toll_report'), $request->file('email_report'));

                    $claim->refresh();

                    $actionType = $request->claim_id ? 'resubmitted' : 'submitted';
                    
                    Log::info('Sending notifications', [
                        'claim_id' => $claim->id,
                        'action_type' => $actionType,
                        'status' => $claim->status
                    ]);

                    Notification::send($claim->user, new ClaimStatusNotification($claim, $claim->status, $actionType));

                    $this->notifyRoles($claim, $actionType);
                } else {
                    Log::error('Invalid user instance during claim submission');
                    return route('login');
                }
            });

            Log::info('Claim submission completed successfully');
            return redirect()->route('claims.dashboard')->with('success', 'Claim submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Error submitting claim', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'An error occurred while submitting the claim. Please try again.');
        }
    }


    //////////////////////////////////////////////////////////////////////////////////  


    public function approvalScreen()
    {
        $userId = Auth::id();
        Log::info('Accessing approval screen', ['user_id' => $userId]);
    
        $user = Auth::user();
    
        if (!$user) {
            Log::warning('Unauthenticated user attempted to access approval screen');
            return redirect()->route('login');
        }
    
        if ($user->role->name === 'Staff') {
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


    //////////////////////////////////////////////////////////////////////////////////  

    public function reviewClaim($id)
    {
        Log::info('Accessing claim review', ['claim_id' => $id]);

        $user = Auth::user();
        $claim = Claim::with(['locations' => function($query) {
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
            
            return view('pages.claims.review', compact('claim', 'reviews'));
        }

        Log::error('Invalid user instance during claim review');
        return route('login');
    }

    //////////////////////////////////////////////////////////////////////////////////      


    public function updateClaim(Request $request, $id)
    {
        Log::info('Starting claim update process', [
            'claim_id' => $id,
            'action' => $request->action
        ]);

        $claim = Claim::findOrFail($id);
        $user = Auth::user();

        if ($user instanceof User) {
            if (!$this->claimService->canReviewClaim($user, $claim)) {
                Log::warning('Unauthorized claim update attempt', [
                    'user_id' => $user->id,
                    'claim_id' => $id,
                    'user_role' => $user->role->name
                ]);
                return redirect()->route('claims.approval')->with('error', 'You do not have permission to review this claim.');
            }

            $request->validate([
                'remarks' => 'required|string',
                'action' => 'required|in:approve,reject',
            ]);

            DB::transaction(function () use ($request, $user, $claim) {
                Log::info('Processing claim update', [
                    'claim_id' => $claim->id,
                    'action' => $request->action,
                    'reviewer_id' => $user->id
                ]);

                if ($request->action === 'approve') {
                    $this->claimService->approveClaim($user, $claim);
                    $actionType = 'approved';
                } else {
                    $this->claimService->rejectClaim($user, $claim);
                    $actionType = 'rejected';
                }

                $this->claimService->storeRemarks($user, $claim, $request->remarks);

                $claim->refresh();

                Log::info('Sending claim update notifications', [
                    'claim_id' => $claim->id,
                    'action_type' => $actionType,
                    'status' => $claim->status
                ]);

                Notification::send($claim->user, new ClaimStatusNotification($claim, $claim->status, $actionType));

                $this->notifyRoles($claim, $actionType);
            });

            Log::info('Claim update completed successfully', [
                'claim_id' => $claim->id,
                'final_status' => $claim->status
            ]);

            return redirect()->route('claims.approval')->with('success', 'Claim ' . $request->action . ' successfully.');
        } else {
            Log::error('Invalid user instance during claim update');
            return route('login');
        }
    }

    //////////////////////////////////////////////////////////////////////////////////

    private function notifyRoles(Claim $claim, string $actionType)
    {
        Log::info('Starting role notification process', [
            'claim_id' => $claim->id,
            'action_type' => $actionType
        ]);

        $rolesToNotify = [];

        switch ($actionType) {
            case 'submitted':
            case 'approved':
                $nextRole = $this->claimService->getNextApproverRole($claim->status);
                if ($nextRole) {
                    $rolesToNotify[] = $nextRole;
                    Log::info('Next approver role identified', [
                        'action_type' => $actionType,
                        'role' => $nextRole,
                        'claim_id' => $claim->id
                    ]);
                }
                break;

            case 'rejected':
                Log::info('No roles to notify for rejection');
                break;

            case 'resubmitted':
                $previousRejectorRole = $this->claimService->getPreviousRejectorRole($claim);
                if ($previousRejectorRole) {
                    $rolesToNotify[] = $previousRejectorRole;
                    Log::info('Previous rejector role identified', [
                        'role' => $previousRejectorRole,
                        'claim_id' => $claim->id
                    ]);
                }
                break;

            default:
                Log::warning('Unknown action type for notifications', [
                    'action_type' => $actionType,
                    'claim_id' => $claim->id
                ]);
                break;
        }

        if (!empty($rolesToNotify)) {
            $usersToNotify = User::whereIn('role_id', function ($query) use ($rolesToNotify) {
                $query->select('id')->from('roles')->whereIn('name', $rolesToNotify);
            })->get();

            Log::info('Sending notifications to users', [
                'roles' => $rolesToNotify,
                'users_count' => $usersToNotify->count()
            ]);

            foreach ($usersToNotify as $userToNotify) {
                Notification::send($userToNotify, new ClaimStatusNotification($claim, $claim->status, $actionType, false));
            }
        }
    }


    //////////////////////////////////////////////////////////////////////////////////      


    public function approveClaim($id)
    {
        Log::info('Direct claim approval initiated', ['claim_id' => $id]);

        $user = Auth::user();
        $claim = Claim::findOrFail($id);

        if ($user instanceof User) {
            if (!$this->claimService->canReviewClaim($user, $claim)) {
                Log::warning('Unauthorized direct claim approval attempt', [
                    'user_id' => $user->id,
                    'claim_id' => $id,
                    'user_role' => $user->role->name
                ]);
                return redirect()->route('claims.approval')->with('error', 'You do not have permission to approve this claim.');
            }

            $updatedClaim = $this->claimService->approveClaim($user, $claim);
            Log::info('Claim approved successfully', [
                'claim_id' => $id,
                'new_status' => $updatedClaim->status
            ]);

            return redirect()->route('claims.approval')->with('success', 'Claim approved successfully.');
        } else {
            Log::error('Invalid user instance during direct claim approval');
            return route('login');
        }
    }

    //////////////////////////////////////////////////////////////////////////////////  

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

    //////////////////////////////////////////////////////////////////////////////////

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

    //////////////////////////////////////////////////////////////////////////////////  

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

    //////////////////////////////////////////////////////////////////////////////////  
    
    public function sendToDatuk($id)
    {
        Log::info('Initiating send to Datuk process', ['claim_id' => $id]);

        $claim = Claim::findOrFail($id);
        $user = Auth::user();

        if ($user->role->name !== 'Admin') {
            Log::warning('Unauthorized attempt to send claim to Datuk', [
                'user_id' => $user->id,
                'user_role' => $user->role->name,
                'claim_id' => $id
            ]);
            return redirect()->route('claims.approval')->with('error', 'You do not have permission to send this claim to Datuk.');
        }

        try {
            Log::info('Sending claim to Datuk', [
                'claim_id' => $id,
                'sender_id' => $user->id
            ]);
            $this->claimService->sendClaimToDatuk($claim);

            Log::info('Claim sent to Datuk successfully', ['claim_id' => $id]);
            return redirect()->route('claims.approval')->with('success', 'Claim sent to Datuk successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to send claim to Datuk', [
                'claim_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('claims.approval')->with('error', 'Failed to send claim to Datuk.');
        }
    }

    ////////////////////////////////////////////////////////////////////////////////// 

    public function handleEmailAction(Request $request, $id)
    {
        Log::info('Processing email action', [
            'claim_id' => $id,
            'action' => $request->query('action')
        ]);

        $claim = Claim::findOrFail($id);
        $action = $request->query('action');

        if (!in_array($action, ['approve', 'reject'])) {
            Log::warning('Invalid email action attempted', [
                'claim_id' => $id,
                'action' => $action
            ]);
            return view('pages.claims.email-action', [
                'success' => false,
                'message' => 'Invalid action specified.'
            ]);
        }

        try {
            $latestReview = $claim->reviews()
                ->orderBy('created_at', 'desc')
                ->first();

            Log::info('Checking claim state for email action', [
                'claim_id' => $id,
                'current_status' => $claim->status,
                'latest_review' => $latestReview ? [
                    'department' => $latestReview->department,
                    'created_at' => $latestReview->created_at
                ] : null
            ]);

            $canProcess = false;
            $message = '';

            if (!$latestReview) {
                $canProcess = true;
            } else if ($latestReview->department === 'Email Approval' && $claim->status === Claim::STATUS_APPROVED_DATUK) {
                Log::info('Claim already processed by Datuk', ['claim_id' => $id]);
                return view('pages.claims.email-action', [
                    'alreadyProcessed' => true,
                    'claim' => $claim,
                    'message' => 'This claim has already been approved. No further action is required.'
                ]);
            } else if ($latestReview->department === 'Email Approval' && $claim->status === Claim::STATUS_APPROVED_ADMIN) {
                $canProcess = true;
            } else if ($latestReview->department === 'Admin' && $claim->status === Claim::STATUS_APPROVED_ADMIN) {
                $canProcess = true;
            }

            if (!$canProcess) {
                Log::warning('Invalid claim state for email action', [
                    'claim_id' => $id,
                    'current_status' => $claim->status,
                    'latest_review_department' => $latestReview ? $latestReview->department : null
                ]);
                return view('pages.claims.email-action', [
                    'alreadyProcessed' => true,
                    'claim' => $claim,
                    'message' => 'This claim is not in the correct state for this action.'
                ]);
            }

            DB::transaction(function () use ($action, $claim) {
                Log::info('Processing email action transaction', [
                    'claim_id' => $claim->id,
                    'action' => $action
                ]);

                if ($action === 'approve') {
                    $claim->status = Claim::STATUS_APPROVED_DATUK;
                    
                    $hrReviewer = User::whereHas('role', function($query) {
                        $query->where('name', 'HR');
                    })->first();
                    
                    if ($hrReviewer) {
                        $claim->reviewer_id = $hrReviewer->id;
                    }
                } else {
                    $claim->status = Claim::STATUS_SUBMITTED;
                    
                    $adminReviewer = User::whereHas('role', function($query) {
                        $query->where('name', 'Admin');
                    })->first();
                    
                    if ($adminReviewer) {
                        $claim->reviewer_id = $adminReviewer->id;
                    }
                }

                $claim->save();

                $reviewData = [
                    'claim_id' => $claim->id,
                    'reviewer_id' => null,
                    'remarks' => 'Action taken via email: ' . ($action === 'approve' ? 'Approved' : 'Rejected by Datuk'),
                    'department' => 'Email Approval',
                    'review_order' => $claim->reviews()->count() + 1,
                    'status' => $claim->status,
                    'reviewed_at' => now()
                ];

                Log::info('Creating review record', $reviewData);
                $review = $claim->reviews()->create($reviewData);
                Log::info('Review record created', ['review_id' => $review->id]);

                Notification::send($claim->user, new ClaimStatusNotification(
                    $claim, 
                    $claim->status, 
                    $action === 'approve' ? 'approved_by_datuk' : 'rejected_by_datuk'
                ));

                if ($action === 'approve') {
                    User::whereHas('role', function($query) {
                        $query->where('name', 'HR');
                    })->get()->each(function($user) use ($claim) {
                        Notification::send($user, new ClaimStatusNotification(
                            $claim, 
                            $claim->status, 
                            'pending_review', 
                            false
                        ));
                    });
                } else {
                    User::whereHas('role', function($query) {
                        $query->where('name', 'Admin');
                    })->get()->each(function($user) use ($claim) {
                        Notification::send($user, new ClaimStatusNotification(
                            $claim, 
                            $claim->status, 
                            'returned_from_datuk', 
                            false
                        ));
                    });
                }
            });

            Log::info('Email action processed successfully', [
                'claim_id' => $claim->id,
                'action' => $action,
                'final_status' => $claim->status
            ]);

            return view('pages.claims.email-action', [
                'success' => true,
                'message' => 'Claim has been ' . ($action === 'approve' ? 'approved' : 'rejected') . ' successfully.',
                'claim' => $claim
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing email action', [
                'claim_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return view('pages.claims.email-action', [
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ]);
        }
    }

    public function export(Claim $claim)
    {
        try {
            Log::info('Starting claim export', ['claim_id' => $claim->id]);

            $templatePath = resource_path('templates/claim_template.xlsx');
            $spreadsheet = IOFactory::load($templatePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $cellMappings = [
                'A1' => [
                    'search' => 'STAFF CLAIM FOR EXPENSES INCURRED ON OFFICIAL DUTIES FORM - ${claim_company}',
                    'replace' => 'STAFF CLAIM FOR EXPENSES INCURRED ON OFFICIAL DUTIES FORM - ' . $claim->claim_company
                ],
                'A3' => [
                    'search' => 'NAME: ${first_name} ${second_name}',
                    'replace' => 'NAME: ' . $claim->user->first_name . ' ' . $claim->user->second_name
                ],
                'M3' => [
                    'search' => 'MONTH: ${claim_month}',
                    'replace' => 'MONTH: ' . $claim->submitted_at->format('m/Y')
                ],
                'G5' => [
                    'search' => 'POSITION: ${user_department_name}',
                    'replace' => 'POSITION: ' . ($claim->user->department_id ? Department::find($claim->user->department_id)->name : '')
                ],
                'E8' => [
                    'search' => '${total_distance}',
                    'replace' => number_format($claim->total_distance, 2)
                ],
                'F8' => [
                    'search' => '${toll_amount}',
                    'replace' => number_format($claim->toll_amount, 2)
                ],
                'Q8' => [
                    'search' => '${claim_description}',
                    'replace' => strval($claim->description ?? '')
                ]
            ];

            foreach ($cellMappings as $coordinate => $mapping) {
                $cell = $worksheet->getCell($coordinate);
                $currentValue = $cell->getValue();
                if ($currentValue === $mapping['search']) {
                    $cell->setValue($mapping['replace']);
                } else {
                    $newValue = str_replace(
                        [
                            '${first_name}',
                            '${second_name}',
                            '${claim_month}',
                            '${user_role_name}',
                            '${total_distance}',
                            '${toll_amount}',
                            '${claim_description}'
                        ],
                        [
                            $claim->user->first_name,
                            $claim->user->second_name,
                            $claim->submitted_at->format('m/Y'),
                            $claim->user->role->name,
                            number_format($claim->total_distance, 2),
                            number_format($claim->toll_amount, 2),
                            strval($claim->description ?? '')
                        ],
                        $currentValue
                    );
                    $cell->setValue($newValue);
                }
            }

            // Get locations ordered by sequence
            $locations = $claim->locations()->orderBy('order')->get();
            
            $startRow = 8;
            if ($locations->count() > 0) {
                // Set date range for the entire claim
                $dateRange = $claim->date_from->format('d/m/Y') . ' - ' . $claim->date_to->format('d/m/Y');
                
                // Process each location pair
                foreach ($locations as $index => $locationPair) {
                    $currentRow = $startRow + $index;
                    
                    // Insert new row if not first row
                    if ($index > 0) {
                        $worksheet->insertNewRowBefore($currentRow, 1);
                        $worksheet->duplicateStyle(
                            $worksheet->getStyle('A8:Q8'),
                            'A' . $currentRow . ':Q' . $currentRow
                        );
                    }

                    // Set date range only in first row
                    if ($index === 0) {
                        $worksheet->setCellValue('A' . $currentRow, $dateRange);
                    }

                    // Format location text as "From -> To"
                    $locationText = $locationPair->from_location . " ->\n" . $locationPair->to_location;
                    
                    // Set values in the worksheet
                    $worksheet->setCellValue('B' . $currentRow, $locationText);
                    $worksheet->setCellValue('E' . $currentRow, $locationPair->distance ?? '');
                    
                    // Set toll amount only in first row
                    if ($index === 0) {
                        $worksheet->setCellValue('F' . $currentRow, $claim->toll_amount ?? '');
                    }

                    // Configure cell styling
                    $worksheet->getStyle('B' . $currentRow)
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                    
                    $worksheet->getRowDimension($currentRow)->setRowHeight(45);
                }

                // Merge date cells if multiple locations
                if ($locations->count() > 1) {
                    $lastRow = $startRow + $locations->count() - 1;
                    $worksheet->mergeCells('A' . $startRow . ':A' . $lastRow);
                    $worksheet->getStyle('A' . $startRow . ':A' . $lastRow)
                        ->getAlignment()
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
            }

            // Create response
            $writer = new Xlsx($spreadsheet);
            $filename = "claim_{$claim->id}_export.xlsx";
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Error exporting claim', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // Helper function to debug template placeholders
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

    /**
     * Get dashboard statistics for the home page
     */
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

    /**
     * Display the home page with statistics
     */
    public function home()
    {
        $statistics = $this->getHomePageStatistics();

        return view('pages.home', [
            'totalClaims' => $statistics['totalClaims'],
            'approvedClaims' => $statistics['approvedClaims'],
            'pendingClaims' => $statistics['pendingClaims'],
            'rejectedClaims' => $statistics['rejectedClaims']
        ]);
    }

}
