<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\User\UserSecurity; // Assuming UserSecurity model exists for tracking attempts
use App\Models\User\User; // Correct the User model import

class AuthService
{
    /**
     * Attempt to authenticate the user.
     *
     * @param array $credentials
     * @return bool True if authentication successful, false otherwise.
     * @throws ValidationException If authentication fails.
     */
    public function attemptLogin(array $credentials): bool
    {
        Log::info('Login attempt initiated.', ['email' => $credentials['email']]);

        // Find user by email first
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            // User not found, log attempt and throw generic error
            $this->incrementLoginAttempts(request()); 
            Log::warning('Login failed: User not found.', ['email' => $credentials['email']]);
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')], // Generic message for non-existent email
            ]);
        }

        // User found, now attempt password verification
        if (Auth::attempt($credentials, request()->filled('remember'))) {
            // Password correct
            request()->session()->regenerate();
            $this->clearLoginAttempts(request()); 
            Log::info('Login successful.', ['user_id' => Auth::id()]);
            return true;
        }

        // Password incorrect for the found user
        $this->incrementLoginAttempts(request()); 
        Log::warning('Login failed: Incorrect password.', ['email' => $credentials['email']]);
        throw ValidationException::withMessages([
            'password' => [trans('auth.password')], // Password-specific message
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        $userId = Auth::id();
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Logout successful.', ['user_id' => $userId]);
    }

    /**
     * Increment the login attempts for the user.
     * NOTE: This assumes Laravel's built-in throttling is not used or needs supplementing.
     * Adjust logic based on actual throttling mechanism if different.
     *
     * @param  Request $request
     * @return void
     */
    protected function incrementLoginAttempts(Request $request): void
    {
        // Basic example: Log failed attempt. Replace with actual throttling logic if needed.
        // E.g., using Laravel's ThrottlesLogins trait or a custom DB mechanism.
        try {
            UserSecurity::create([
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempted_at' => now(),
                'successful' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log unsuccessful login attempt.', [
                'error' => $e->getMessage(),
                'email' => $request->input('email')
            ]);
        }
    }

    /**
     * Clear the login attempts for the user.
     * NOTE: Only relevant if custom throttling is implemented here.
     *
     * @param  Request $request
     * @return void
     */
    protected function clearLoginAttempts(Request $request): void
    {
        // Clear custom throttling counters if applicable
        // E.g., remove records from a failed attempts table or cache.
        Log::info('Clearing login attempts (if applicable).', ['email' => $request->input('email')]);

        // Example: If UserSecurity logs were used for locking, maybe mark previous failed as resolved
        // This depends heavily on how locking/throttling is implemented.
    }
} 