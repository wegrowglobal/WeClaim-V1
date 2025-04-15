<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\User\LoginActivity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LoginController extends Controller
{
    use AuthorizesRequests, ThrottlesLogins;

    /**
     * Maximum number of login attempts allowed.
     */
    protected $maxAttempts = 5;

    /**
     * Number of minutes to lock the login for.
     */
    protected $decayMinutes = 5;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        Log::info('Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        try {
            // Validate login data
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string'
            ]);
            
            // Check if the user has exceeded maximum login attempts
            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                Log::warning('Too many login attempts', ['email' => $request->email]);
                
                // Log the failed attempt with lockout
                $this->logLoginAttempt($request, false, true);
                
                return $this->sendLockoutResponse($request);
            }
            
            // Find the user by email
            $user = User::where('email', $credentials['email'])->first();
            
            // Check if user exists and is active
            if (!$user) {
                Log::warning('Login attempt with non-existent email', ['email' => $request->email]);
                $this->logLoginAttempt($request, false);
                $this->incrementLoginAttempts($request);
                
                throw ValidationException::withMessages([
                    'email' => 'These credentials do not match our records.'
                ]);
            }
            
            // Check if user is active
            if (!$user->is_active) {
                Log::warning('Login attempt with inactive account', ['email' => $request->email]);
                $this->logLoginAttempt($request, false, false, 'Account is inactive');
                
                throw ValidationException::withMessages([
                    'email' => 'Your account has been deactivated. Please contact the administrator.'
                ]);
            }
            
            // Attempt to authenticate
            $remember = $request->filled('remember');
            
            if (Auth::attempt($credentials, $remember)) {
                // Reset login attempts
                $this->clearLoginAttempts($request);
                
                // Log successful login
                $this->logLoginAttempt($request, true);
                
                // Update last login time
                $user->last_login_at = now();
                $user->save();
                
                Log::info('Login successful', ['user_id' => $user->id, 'email' => $user->email]);
                
                return redirect()->intended('/');
            }
            
            // Authentication failed
            Log::warning('Login failed - invalid credentials', ['email' => $request->email]);
            $this->logLoginAttempt($request, false, false, 'Invalid credentials');
            $this->incrementLoginAttempts($request);
            
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.'
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->except('password'));
        } catch (\Exception $e) {
            Log::error('Login error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['email' => 'An error occurred during login. Please try again.'])
                ->withInput($request->except('password'));
        }
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        Log::info('User logged out', [
            'user_id' => $user ? $user->id : null,
            'email' => $user ? $user->email : null
        ]);
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login.form');
    }

    /**
     * Get the login username (email) to be used by the controller.
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Log a login attempt in the database.
     */
    private function logLoginAttempt(Request $request, bool $successful, bool $locked = false, ?string $reason = null)
    {
        try {
            // Find user if email exists
            $user = User::where('email', $request->email)->first();
            
            $activity = new LoginActivity([
                'user_id' => $user ? $user->id : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => $successful,
                'locked' => $locked,
                'reason' => $reason
            ]);
            
            $activity->save();
            
            Log::info('Login activity logged', [
                'email' => $request->email,
                'successful' => $successful,
                'locked' => $locked
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging login activity', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
        }
    }
} 