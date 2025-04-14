<?php

namespace App\Http\Controllers\Signature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // All methods in this controller require authentication
        $this->middleware('auth');
    }

    /**
     * Store a newly created signature in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            
            // Convert base64 image data to a file
            $signatureData = $request->input('signature');
            $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
            $signatureData = str_replace(' ', '+', $signatureData);
            
            $fileName = 'signature-' . $user->id . '-' . time() . '.png';
            $filePath = 'signatures/' . $fileName;
            
            // Delete existing signature if exists
            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }
            
            // Store the new signature
            Storage::disk('public')->put($filePath, base64_decode($signatureData));
            
            // Update the user's signature path
            $user->signature_path = $filePath;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Signature saved successfully',
                'path' => Storage::disk('public')->url($filePath)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save signature', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save signature: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified signature from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $user = Auth::user();
            
            if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
                Storage::disk('public')->delete($user->signature_path);
            }
            
            $user->signature_path = null;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Signature removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete signature', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove signature: ' . $e->getMessage()
            ], 500);
        }
    }
} 