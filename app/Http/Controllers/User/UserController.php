<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\User\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    protected $authService;
    protected $auth;

    public function __construct(AuthService $authService, Auth $auth)
    {
        $this->authService = $authService;
        $this->auth = $auth;
        
        // Apply middleware selectively in the constructor
        $this->middleware('verified')->only(['changePassword']);
    }

    public function profile()
    {
        $user = Auth::user();
        $this->logService->log('user', 'viewed_profile', 'User viewed their profile');
        
        return view('pages.user.profile', compact('user'));
    }

    public function showChangePassword(): View
    {
        return view('pages.user.change-password');
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', new \App\Rules\StrongPassword(), 'confirmed'],
                'password_confirmation' => ['required']
            ]);

            $user = Auth::user();
            if (!$user) {
                Log::error('User not found during password change');
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Add debugging for password check
            Log::info('Password change attempt', [
                'user_id' => $user->id,
                'current_password_length' => strlen($request->current_password),
                'new_password_length' => strlen($request->password)
            ]);

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                Log::warning('Current password mismatch', [
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'message' => 'The provided password does not match your current password.'
                ], 422);
            }

            // Update password using direct database update
            $updated = DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($request->password)
                ]);

            if (!$updated) {
                throw new \RuntimeException('Failed to update password in database');
            }

            Log::info('Password changed successfully', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'message' => 'Password has been updated successfully!'
            ]);
        } catch (ValidationException $e) {
            Log::error('Password change validation error', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Password change error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update password.'
            ], 500);
        }
    }

    /**
     * Update the user's profile picture.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $user = Auth::user();
        
        // Delete old profile picture if exists
        if ($user->profile_picture && Storage::exists('public/' . $user->profile_picture)) {
            Storage::delete('public/' . $user->profile_picture);
        }
        
        // Store new profile picture
        $imagePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        
        // Update user with new picture path
        $user->profile_picture = $imagePath;
        $user->save();
        
        return redirect()->back()->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Update the user's banking information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateBankingInfo(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:20',
            'account_holder_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        
        $user->bankingInformation()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name
            ]
        );
        
        return redirect()->back()->with('success', 'Banking information updated successfully.');
    }
}
