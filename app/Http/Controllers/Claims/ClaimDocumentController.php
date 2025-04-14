<?php

namespace App\Http\Controllers\Claims;

use App\Http\Controllers\Controller;
use App\Models\Claim\Claim;
use App\Models\Claim\ClaimDocument;
use App\Services\ClaimService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class ClaimDocumentController extends Controller
{
    use AuthorizesRequests;
    protected $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
        
        // Apply middleware for all methods
        $this->middleware('auth');
        $this->middleware('track.activity');
        
        // Apply profile completion middleware for document uploads
        $this->middleware('profile.complete')->except(['viewDocument']);
        
        // Apply email verification middleware for document uploads
        $this->middleware('verified')->only(['upload', 'delete']);
    }

    /**
     * View/download a claim document
     */
    public function viewDocument(Claim $claim, $type, $filename)
    {
        Log::info('Document view requested', [
            'claim_id' => $claim->id,
            'type' => $type,
            'filename' => $filename,
            'user_id' => Auth::id()
        ]);
        
        try {
            $user = Auth::user();
            
            // Check if user has access to this document
            $canAccess = ($claim->user_id === $user->id) || 
                         $this->claimService->canReviewClaim($user, $claim);
                         
            if (!$canAccess) {
                Log::warning('Unauthorized document access attempt', [
                    'user_id' => $user->id,
                    'claim_id' => $claim->id,
                    'document' => $filename
                ]);
                
                abort(403, 'You do not have permission to view this document');
            }
            
            // Find the document in the database
            $document = ClaimDocument::where('claim_id', $claim->id)
                ->where('type', $type)
                ->where('filename', $filename)
                ->first();
                
            if (!$document) {
                Log::error('Document not found', [
                    'claim_id' => $claim->id,
                    'type' => $type,
                    'filename' => $filename
                ]);
                
                abort(404, 'Document not found');
            }
            
            // Get file path
            $path = 'claims/' . $claim->id . '/' . $type . '/' . $filename;
            
            // Check if file exists in storage
            if (!Storage::exists($path)) {
                Log::error('Document file missing from storage', [
                    'claim_id' => $claim->id,
                    'path' => $path
                ]);
                
                abort(404, 'Document file not found in storage');
            }
            
            Log::info('Serving document', [
                'claim_id' => $claim->id,
                'document_id' => $document->id,
                'path' => $path
            ]);
            
            // Determine content type
            $contentType = $this->getContentTypeFromFilename($filename);
            
            // Return file response
            return new Response(Storage::get($path), 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);
        } catch (Exception $e) {
            Log::error('Error serving document', [
                'claim_id' => $claim->id,
                'type' => $type,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Error serving document: ' . $e->getMessage());
        }
    }

    /**
     * Upload a document for a claim
     */
    public function upload(Request $request, Claim $claim)
    {
        try {
            Log::info('Document upload requested', [
                'claim_id' => $claim->id,
                'user_id' => Auth::id()
            ]);
            
            // Check authorization
            $user = Auth::user();
            if ($claim->user_id !== $user->id && $user->role->name !== 'Admin') {
                Log::warning('Unauthorized document upload attempt', [
                    'user_id' => $user->id,
                    'claim_id' => $claim->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to upload documents for this claim'
                ], 403);
            }
            
            // Validate request
            $validated = $request->validate([
                'document' => 'required|file|max:10240', // 10MB max
                'type' => 'required|in:toll,invoice,receipt,email,other'
            ]);
            
            $file = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            // Sanitize filename
            $safeName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            $uniqueName = $safeName . '_' . time() . '.' . $extension;
            
            // Store the file
            $path = $file->storeAs(
                'claims/' . $claim->id . '/' . $validated['type'],
                $uniqueName
            );
            
            if (!$path) {
                Log::error('Failed to store document', [
                    'claim_id' => $claim->id,
                    'original_name' => $originalName
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store document'
                ], 500);
            }
            
            // Create document record in database
            $document = new ClaimDocument([
                'claim_id' => $claim->id,
                'type' => $validated['type'],
                'filename' => $uniqueName,
                'original_filename' => $originalName,
                'uploaded_by' => Auth::id()
            ]);
            
            $document->save();
            
            Log::info('Document uploaded successfully', [
                'claim_id' => $claim->id,
                'document_id' => $document->id,
                'path' => $path
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => [
                    'id' => $document->id,
                    'filename' => $uniqueName,
                    'original_filename' => $originalName,
                    'type' => $validated['type'],
                    'url' => route('claims.view.document', [
                        'claim' => $claim->id,
                        'type' => $validated['type'],
                        'filename' => $uniqueName
                    ])
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error uploading document', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error uploading document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a document
     */
    public function delete(Request $request, Claim $claim, ClaimDocument $document)
    {
        try {
            Log::info('Document deletion requested', [
                'claim_id' => $claim->id,
                'document_id' => $document->id,
                'user_id' => Auth::id()
            ]);
            
            // Check authorization
            $user = Auth::user();
            $isOwner = $claim->user_id === $user->id;
            $isAdmin = $user->role->name === 'Admin';
            
            if (!$isOwner && !$isAdmin) {
                Log::warning('Unauthorized document deletion attempt', [
                    'user_id' => $user->id,
                    'claim_id' => $claim->id,
                    'document_id' => $document->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this document'
                ], 403);
            }
            
            // Check if document belongs to the claim
            if ($document->claim_id !== $claim->id) {
                Log::error('Document does not belong to the claim', [
                    'claim_id' => $claim->id,
                    'document_id' => $document->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Document does not belong to this claim'
                ], 400);
            }
            
            // Delete file from storage
            $path = 'claims/' . $claim->id . '/' . $document->type . '/' . $document->filename;
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
            
            // Delete document record
            $document->delete();
            
            Log::info('Document deleted successfully', [
                'claim_id' => $claim->id,
                'document_id' => $document->id,
                'path' => $path
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting document', [
                'claim_id' => $claim->id,
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to determine content type from filename
     */
    private function getContentTypeFromFilename($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $contentTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain'
        ];
        
        return $contentTypes[$extension] ?? 'application/octet-stream';
    }
} 