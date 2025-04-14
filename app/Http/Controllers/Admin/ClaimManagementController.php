<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim\Claim;
use App\Models\User\User;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClaimManagementController extends Controller
{
    /**
     * @var LogService
     */
    protected $logService;

    /**
     * Create a new controller instance.
     *
     * @param LogService $logService
     * @return void
     */
    public function __construct(LogService $logService)
    {
        $this->middleware(['auth', 'activity', 'admin']);
        $this->logService = $logService;
    }

    /**
     * Display a listing of claims.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Claim::with('user');
        
        // Apply filters
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('user', function($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        
        if ($request->has('amount_min')) {
            $query->where('amount', '>=', $request->input('amount_min'));
        }
        
        if ($request->has('amount_max')) {
            $query->where('amount', '<=', $request->input('amount_max'));
        }
        
        $claims = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $statusCounts = [
            'total' => Claim::count(),
            'pending' => Claim::where('status', 'pending')->count(),
            'in_review' => Claim::where('status', 'in_review')->count(),
            'approved' => Claim::where('status', 'approved')->count(),
            'rejected' => Claim::where('status', 'rejected')->count(),
        ];
        
        $this->logService->log(
            'admin', 
            'viewed_claims_list', 
            'Admin viewed claims list'
        );
        
        return view('admin.claims.index', compact('claims', 'statusCounts'));
    }

    /**
     * Display the specified claim.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $claim = Claim::with(['user', 'documents', 'notes', 'activities'])->findOrFail($id);
        
        $this->logService->log(
            'admin', 
            'viewed_claim_details', 
            'Admin viewed claim details',
            ['claim_id' => $claim->id]
        );
        
        return view('admin.claims.show', compact('claim'));
    }

    /**
     * Show the form for editing the specified claim.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $claim = Claim::with('user')->findOrFail($id);
        
        return view('admin.claims.edit', compact('claim'));
    }

    /**
     * Update the specified claim in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'incident_date' => 'required|date',
            'category' => 'required|string|max:100',
            'status' => 'required|in:pending,in_review,approved,rejected',
        ]);
        
        $oldStatus = $claim->status;
        $newStatus = $validated['status'];
        
        $claim->update($validated);
        
        // Log status change if it occurred
        if ($oldStatus !== $newStatus) {
            $this->logService->log(
                'admin', 
                'claim_status_updated', 
                'Admin updated claim status from ' . $oldStatus . ' to ' . $newStatus,
                ['claim_id' => $claim->id]
            );
            
            // Add a note about the status change
            $claim->notes()->create([
                'user_id' => auth()->id(),
                'content' => 'Status changed from ' . $oldStatus . ' to ' . $newStatus,
                'is_system' => true,
            ]);
        }
        
        $this->logService->log(
            'admin', 
            'updated_claim', 
            'Admin updated claim details',
            ['claim_id' => $claim->id]
        );
        
        return redirect()->route('admin.claims.show', $claim->id)
            ->with('success', 'Claim updated successfully.');
    }

    /**
     * Update the claim status.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_review,approved,rejected',
            'note' => 'nullable|string',
        ]);
        
        $claim = Claim::findOrFail($id);
        $oldStatus = $claim->status;
        $newStatus = $request->status;
        
        $this->logService->log(
            'admin', 
            'claim_status_update_attempt', 
            'Admin attempted to update claim status from ' . $oldStatus . ' to ' . $newStatus,
            ['claim_id' => $claim->id]
        );
        
        $claim->status = $newStatus;
        $claim->save();
        
        // Add a note if provided
        if ($request->filled('note')) {
            $claim->notes()->create([
                'user_id' => auth()->id(),
                'content' => $request->note,
            ]);
        }
        
        // Add a system note about status change
        $claim->notes()->create([
            'user_id' => auth()->id(),
            'content' => 'Status changed from ' . $oldStatus . ' to ' . $newStatus,
            'is_system' => true,
        ]);
        
        $this->logService->log(
            'admin', 
            'claim_status_updated', 
            'Admin updated claim status from ' . $oldStatus . ' to ' . $newStatus,
            ['claim_id' => $claim->id]
        );
        
        // If claim is approved or rejected, notify the user
        if (in_array($newStatus, ['approved', 'rejected'])) {
            // Trigger notification logic would go here
        }
        
        return redirect()->back()
            ->with('success', 'Claim status updated successfully.');
    }

    /**
     * Add a note to the claim.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addNote(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
        
        $claim = Claim::findOrFail($id);
        
        $note = $claim->notes()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);
        
        $this->logService->log(
            'admin', 
            'added_claim_note', 
            'Admin added note to claim',
            ['claim_id' => $claim->id, 'note_id' => $note->id]
        );
        
        return redirect()->back()
            ->with('success', 'Note added successfully.');
    }

    /**
     * Download a claim document.
     *
     * @param  int  $claimId
     * @param  int  $documentId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadDocument($claimId, $documentId)
    {
        $claim = Claim::findOrFail($claimId);
        $document = $claim->documents()->findOrFail($documentId);
        
        $this->logService->log(
            'admin', 
            'downloaded_document', 
            'Admin downloaded document',
            ['claim_id' => $claim->id, 'document_id' => $document->id]
        );
        
        return Storage::download($document->file_path, $document->original_name);
    }

    /**
     * Generate a report of claims based on filters.
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportReport(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf,excel',
            'status' => 'nullable|in:all,pending,in_review,approved,rejected',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);
        
        $query = Claim::with('user');
        
        // Apply filters
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        
        $claims = $query->get();
        
        $this->logService->log(
            'admin', 
            'exported_claims_report', 
            'Admin exported claims report',
            ['format' => $request->format, 'count' => $claims->count()]
        );
        
        // Export handling would go here - implementation depends on export package
        // For now, we'll just return a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Export initiated. ' . $claims->count() . ' claims included.'
        ]);
    }
} 