<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim\Claim;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaimsManagementController extends Controller
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
        $status = $request->input('status', 'all');
        
        $query = Claim::with(['user', 'documents']);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $claims = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.claims.index', compact('claims', 'status'));
    }

    /**
     * Show the specified claim.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $claim = Claim::with(['user', 'documents', 'activities'])->findOrFail($id);
        
        $this->logService->log('admin_claim_view', [
            'admin_id' => Auth::id(),
            'claim_id' => $id
        ]);
        
        return view('admin.claims.show', compact('claim'));
    }

    /**
     * Update the status of the specified claim.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        $newStatus = $request->input('status');
        
        $this->logService->log('admin_claim_status_update_attempt', [
            'admin_id' => Auth::id(),
            'claim_id' => $id,
            'from_status' => $claim->status,
            'to_status' => $newStatus
        ]);
        
        // Status update logic to be implemented
        
        $this->logService->log('admin_claim_status_update_success', [
            'admin_id' => Auth::id(),
            'claim_id' => $id,
            'from_status' => $claim->status,
            'to_status' => $newStatus
        ]);
        
        return redirect()->route('admin.claims.show', $id)
            ->with('success', 'Claim status updated successfully');
    }

    /**
     * Add an admin note to the claim.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addNote(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);
        $note = $request->input('note');
        
        $this->logService->log('admin_claim_note_add', [
            'admin_id' => Auth::id(),
            'claim_id' => $id
        ]);
        
        // Note adding logic to be implemented
        
        return redirect()->route('admin.claims.show', $id)
            ->with('success', 'Note added successfully');
    }

    /**
     * Export claims to CSV.
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $this->logService->log('admin_claims_export', [
            'admin_id' => Auth::id(),
            'filters' => $request->all()
        ]);
        
        // Export logic to be implemented
        
        // return Excel::download(new ClaimsExport($request), 'claims.csv');
    }
} 