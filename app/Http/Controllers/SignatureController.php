<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|max:1024', // 1MB Max
        ]);

        try {
            // Delete old signature if exists
            if ($request->user()->signature_path) {
                Storage::disk('public')->delete($request->user()->signature_path);
            }

            // Store new signature
            $path = $request->file('signature')->store('signatures', 'public');
            
            // Update user's signature path
            $request->user()->update([
                'signature_path' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Signature uploaded successfully',
                'path' => $path
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload signature'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            if ($request->user()->signature_path) {
                Storage::disk('public')->delete($request->user()->signature_path);
                
                $request->user()->update([
                    'signature_path' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Signature deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete signature'
            ], 500);
        }
    }
} 