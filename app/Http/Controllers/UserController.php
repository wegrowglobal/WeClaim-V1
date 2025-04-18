<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class UserController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    protected $authService;
    protected $auth;
    private const LOGIN_ROUTE = 'login';
    private const HOME_ROUTE = 'home';
    private const LOGIN_FAILED_MESSAGE = 'Login failed. Please check your email and password.';

    public function __construct(AuthService $authService, Auth $auth)
    {
        $this->authService = $authService;
        $this->auth = $auth;
        
        // Apply middleware selectively in the constructor
        $this->middleware('guest')->only(['login']);
        $this->middleware('auth')->except(['login']);
        $this->middleware('verified')->only(['changePassword']);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $credentials = $request->validated();
            $remember = $request->boolean('remember');

            if (!$this->authService->attemptLogin($credentials, $remember)) {
                Log::warning('Failed login attempt', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip()
                ]);

                throw new AuthenticationException(self::LOGIN_FAILED_MESSAGE);
            }

            $user = Auth::user();
            if (!$user) {
                throw new RuntimeException('User not found after successful login');
            }

            $request->session()->regenerate();
            $request->session()->put([
                'user_role' => $user->role?->name,
                'user_department' => $user->department?->name
            ]);
            /* 
            if ($user->role_id === 1) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('coming-soon')->with('message', 'System is currently under maintenance for staff members.');
            }
 */
            return redirect()->intended(route(self::HOME_ROUTE));
        } catch (AuthenticationException $e) {
            return back()
                ->withErrors([
                    'email' => $e->getMessage(),
                    'auth' => 'Invalid credentials provided.'
                ])
                ->withInput($request->only('email'));
        } catch (\Exception $e) {
            Log::error('Login error', [
                'exception' => $e,
                'email' => $request->email
            ]);

            return back()
                ->withErrors([
                    'auth' => 'An error occurred during login. Please try again.'
                ])
                ->withInput($request->only('email'));
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route(self::LOGIN_ROUTE);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
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
                'password' => ['required', 'string', 'min:8', 'confirmed'],
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
