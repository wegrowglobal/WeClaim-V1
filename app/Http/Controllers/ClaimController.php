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
        $action = $request->query('action');

        if (!in_array($action, ['approve', 'reject'])) {
            return view('pages.claims.email-action', [
                'success' => false,
                'message' => 'Invalid action specified.'
            ]);
        }

        try {
            // Get the latest review
            $latestReview = $claim->reviews()
                ->orderBy('created_at', 'desc')
                ->first();

            // Check if this is a valid new review cycle
            $canProcess = false;
            $message = '';

            if (!$latestReview) {
                $canProcess = true;
            } else if ($latestReview->department === 'Email Approval' && $claim->status === Claim::STATUS_APPROVED_DATUK) {
                // Already processed and approved by Datuk
                return view('pages.claims.email-action', [
                    'alreadyProcessed' => true,
                    'claim' => $claim,
                    'message' => 'This claim has already been approved. No further action is required.'
                ]);
            } else if ($latestReview->department === 'Email Approval' && $claim->status === Claim::STATUS_APPROVED_ADMIN) {
                // Already rejected by Datuk but reviewed by Admin again
                $canProcess = true;
            } else if ($latestReview->department === 'Admin' && $claim->status === Claim::STATUS_APPROVED_ADMIN) {
                // Reviewed by Admin after rejection, ready for new Datuk decision
                $canProcess = true;
            }

            if (!$canProcess) {
                return view('pages.claims.email-action', [
                    'alreadyProcessed' => true,
                    'claim' => $claim,
                    'message' => 'This claim is not in the correct state for this action.'
                ]);
            }

            DB::transaction(function () use ($action, $claim) {
                if ($action === 'approve') {
                    $claim->status = Claim::STATUS_APPROVED_DATUK;
                    
                    // Find HR reviewer
                    $hrReviewer = User::whereHas('role', function($query) {
                        $query->where('name', 'HR');
                    })->first();
                    
                    if ($hrReviewer) {
                        $claim->reviewer_id = $hrReviewer->id;
                    }
                } else {
                    $claim->status = Claim::STATUS_SUBMITTED;
                    
                    // Find Admin reviewer
                    $adminReviewer = User::whereHas('role', function($query) {
                        $query->where('name', 'Admin');
                    })->first();
                    
                    if ($adminReviewer) {
                        $claim->reviewer_id = $adminReviewer->id;
                    }
                }

                $claim->save();

                // Create review record
                $reviewData = [
                    'claim_id' => $claim->id,
                    'reviewer_id' => null,
                    'remarks' => 'Action taken via email: ' . ($action === 'approve' ? 'Approved' : 'Rejected by Datuk'),
                    'department' => 'Email Approval',
                    'review_order' => $claim->reviews()->count() + 1,
                    'status' => $claim->status,
                    'reviewed_at' => now()
                ];

                Log::info('Creating review record with data:', $reviewData);
                $review = $claim->reviews()->create($reviewData);
                Log::info('Review record created successfully:', ['review_id' => $review->id]);

                // Notify claim owner
                Notification::send($claim->user, new ClaimStatusNotification(
                    $claim, 
                    $claim->status, 
                    $action === 'approve' ? 'approved_by_datuk' : 'rejected_by_datuk'
                ));

                // Notify next reviewer
                if ($action === 'approve') {
                    // Notify HR team
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
                    // Notify Admin team
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

            return view('pages.claims.email-action', [
                'success' => true,
                'message' => 'Claim has been ' . ($action === 'approve' ? 'approved' : 'rejected') . ' successfully.',
                'claim' => $claim
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing email action: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return view('pages.claims.email-action', [
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ]);
        }
    }

    public function export(Claim $claim)
    {
        try {
            $templatePath = resource_path('templates/claim_template.xlsx');
            $spreadsheet = IOFactory::load($templatePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Define explicit cell mappings with their coordinates and full text patterns
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

            // Debug current values
            foreach ($cellMappings as $coordinate => $mapping) {
                $currentValue = $worksheet->getCell($coordinate)->getValue();
                Log::info("Before replacement - Cell {$coordinate}", [
                    'Current value' => $currentValue,
                    'Search pattern' => $mapping['search'],
                    'Will replace with' => $mapping['replace']
                ]);
            }

            

            // Process each mapped cell
            foreach ($cellMappings as $coordinate => $mapping) {
                $cell = $worksheet->getCell($coordinate);
                $currentValue = $cell->getValue();
                
                if ($currentValue === $mapping['search']) {
                    // Exact match replacement
                    $cell->setValue($mapping['replace']);
                } else {
                    // Partial replacement preserving surrounding text
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

                Log::info("After replacement - Cell {$coordinate}", [
                    'New value' => $cell->getValue()
                ]);
            }

            // Handle locations
            $locations = $claim->locations()->orderBy('order')->get();
            $startRow = 8;
            $locationCount = $locations->count();

            if ($locationCount > 1) {
                // Set the date range in first row
                $dateRange = $claim->date_from->format('d/m/Y') . ' - ' . $claim->date_to->format('d/m/Y');
                $worksheet->setCellValue('A' . $startRow, $dateRange);

                // Create pairs of consecutive locations
                for ($i = 0; $i < $locationCount - 1; $i++) {
                    $currentRow = $startRow + $i;
                    $currentLocation = $locations[$i];
                    $nextLocation = $locations[$i + 1];

                    // If not first row, insert new row and copy formatting
                    if ($i > 0) {
                        $worksheet->insertNewRowBefore($currentRow, 1);
                        $worksheet->duplicateStyle(
                            $worksheet->getStyle('A8:Q8'),
                            'A' . $currentRow . ':Q' . $currentRow
                        );
                    }

                    // Format location text with proper line breaks
                    $locationText = $currentLocation->location . " ->\n" . $nextLocation->location;
                    
                    // Set location journey
                    $worksheet->setCellValue('B' . $currentRow, $locationText);
                    
                    // Set distance for current location pair
                    $worksheet->setCellValue('E' . $currentRow, $currentLocation->distance ?? '');
                    
                    // Set cell properties for location text
                    $worksheet->getStyle('B' . $currentRow)
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    // Adjust row height to accommodate the text
                    $worksheet->getRowDimension($currentRow)->setRowHeight(45);
                }

                // Merge date cell across all location rows
                if ($locationCount > 2) {
                    $lastRow = $startRow + $locationCount - 2;
                    $worksheet->mergeCells('A' . $startRow . ':A' . $lastRow);
                    
                    // Center align the merged date cell
                    $worksheet->getStyle('A' . $startRow . ':A' . $lastRow)
                        ->getAlignment()
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }

                // Set toll amount for first row
                $worksheet->setCellValue('F' . $startRow, $claim->toll_amount ?? '');

                // Merge toll cells if multiple rows
                if ($locationCount > 2) {
                    $worksheet->mergeCells('F' . $startRow . ':F' . $lastRow);
                    
                    // Center align the merged cells
                    $worksheet->getStyle('F' . $startRow . ':F' . $lastRow)
                        ->getAlignment()
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }

                // Update total row position
                $totalRow = $startRow + $locationCount - 1;
                
                // Set totals
                $worksheet->setCellValue('A' . $totalRow, 'TOTAL');
                $worksheet->setCellValue('E' . $totalRow, '=SUM(E' . $startRow . ':E' . ($totalRow-1) . ')');
                $worksheet->setCellValue('F' . $totalRow, $claim->toll_amount ?? 0);

                // Apply borders to the entire range
                $range = 'A' . $startRow . ':Q' . $totalRow;
                $worksheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(
                    \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                );

                // Adjust column widths
                $worksheet->getColumnDimension('A')->setWidth(15);
                $worksheet->getColumnDimension('B')->setWidth(50);
                $worksheet->getColumnDimension('E')->setWidth(12);
                $worksheet->getColumnDimension('F')->setWidth(12);
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
            Log::error('Excel export failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to export claim data: ' . $e->getMessage());
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

}
