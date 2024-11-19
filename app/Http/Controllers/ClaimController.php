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
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
use App\Models\ClaimHistory;



class ClaimController extends Controller
{
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
                'user_id' => auth()->id(),
                'data' => $request->except(['toll_file', 'email_file'])
            ]);

            $claim = $this->claimService->createClaim(
                $request->validated(),
                auth()->id()
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
                'user_id' => auth()->id(),
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
                throw new \Exception('Invalid user instance');
            }

            if (!$this->claimService->canReviewClaim($user, $claim)) {
                throw new \Exception('You do not have permission to review this claim.');
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
            
            // Validate step access
            if (!$this->claimFormService->canAccessStep($step, $draftData)) {
                return response()->json([
                    'error' => 'Cannot access this step yet'
                ], 403);
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
        if ($claim->status !== Claim::STATUS_REJECTED || $claim->user_id !== auth()->id()) {
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
                'user_id' => auth()->id(),
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

}
