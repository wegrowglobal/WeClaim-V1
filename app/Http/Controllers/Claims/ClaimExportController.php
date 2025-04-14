<?php

namespace App\Http\Controllers\Claims;

use App\Http\Controllers\Controller;
use App\Models\Claim\Claim;
use App\Services\ClaimService;
use App\Services\ClaimExcelExportService;
use App\Services\ClaimWordExportService;
use App\Services\ClaimPdfExportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Exception;

class ClaimExportController extends Controller
{
    use AuthorizesRequests;
    protected $claimService;
    protected $excelExportService;
    protected $wordExportService;
    protected $pdfExportService;

    public function __construct(
        ClaimService $claimService,
        ClaimExcelExportService $excelExportService,
        ClaimWordExportService $wordExportService,
        ClaimPdfExportService $pdfExportService
    ) {
        $this->claimService = $claimService;
        $this->excelExportService = $excelExportService;
        $this->wordExportService = $wordExportService;
        $this->pdfExportService = $pdfExportService;
        
        // Apply middleware for all methods
        $this->middleware('auth');
        $this->middleware('track.activity');
        $this->middleware('profile.complete');
        $this->middleware('verified');
    }

    /**
     * Export a claim to different formats
     */
    public function export(Request $request, Claim $claim)
    {
        try {
            Log::info('Claim export requested', [
                'claim_id' => $claim->id,
                'user_id' => Auth::id()
            ]);
            
            // Validate request
            $validated = $request->validate([
                'format' => 'required|in:excel,word,pdf'
            ]);
            
            // Authorization check - only claim owner or admin can export
            $user = Auth::user();
            if ($claim->user_id !== $user->id && $user->role->name !== 'Admin') {
                Log::warning('Unauthorized export attempt', [
                    'claim_id' => $claim->id,
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export this claim'
                ], 403);
            }
            
            // Load related data
            $claim->load(['locations' => function ($query) {
                $query->orderBy('order');
            }, 'user', 'documents']);
            
            $format = $validated['format'];
            $filename = 'claim_' . $claim->id . '_' . date('Ymd');
            
            switch ($format) {
                case 'excel':
                    Log::info('Exporting claim to Excel', ['claim_id' => $claim->id]);
                    return $this->excelExportService->export($claim, $filename);
                    
                case 'word':
                    Log::info('Exporting claim to Word', ['claim_id' => $claim->id]);
                    return $this->wordExportService->export($claim, $filename);
                    
                case 'pdf':
                    Log::info('Exporting claim to PDF', ['claim_id' => $claim->id]);
                    return $this->pdfExportService->export($claim, $filename);
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported export format'
                    ], 400);
            }
        } catch (Exception $e) {
            Log::error('Error exporting claim', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while exporting the claim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export multiple claims as a batch (for admin and managers)
     */
    public function batchExport(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'claim_ids' => 'required|array',
                'claim_ids.*' => 'exists:claims,id',
                'format' => 'required|in:excel,word,pdf'
            ]);
            
            $user = Auth::user();
            
            // Check authorization - only managers and admins can batch export
            if (!in_array($user->role->name, ['Admin', 'HR', 'Finance'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to batch export claims'
                ], 403);
            }
            
            Log::info('Batch export requested', [
                'user_id' => $user->id,
                'claim_count' => count($validated['claim_ids']),
                'format' => $validated['format']
            ]);
            
            // Get claims
            $claims = Claim::with(['locations', 'user', 'documents'])
                ->whereIn('id', $validated['claim_ids'])
                ->get();
            
            $format = $validated['format'];
            $filename = 'claims_batch_' . date('Ymd');
            
            switch ($format) {
                case 'excel':
                    Log::info('Batch exporting claims to Excel', ['count' => $claims->count()]);
                    return $this->excelExportService->batchExport($claims, $filename);
                    
                case 'pdf':
                    Log::info('Batch exporting claims to PDF', ['count' => $claims->count()]);
                    return $this->pdfExportService->batchExport($claims, $filename);
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported export format for batch operations'
                    ], 400);
            }
        } catch (Exception $e) {
            Log::error('Error batch exporting claims', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while batch exporting claims: ' . $e->getMessage()
            ], 500);
        }
    }
} 