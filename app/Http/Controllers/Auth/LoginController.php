<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    protected AuthService $authService;

    /**
     * Create a new controller instance.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->middleware('guest')->except('logout');
        $this->authService = $authService;
    }

    /**
     * Show the application's login form.
     *
     * @return View
     */
    public function showLoginForm(): View
    {
        return view('auth.login.login'); // Matches the existing route definition
    }

    /**
     * Handle a login request to the application.
     *
     * @param  LoginRequest  $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            $this->authService->attemptLogin($request->validated());

            // Authentication was successful
            $request->session()->regenerate();
            Log::info('User logged in successfully after session regeneration.', ['user_id' => Auth::id()]);

            // Redirect to intended location or default home
            // Note: Laravel's Redirector automatically handles intended URL
            return redirect()->intended(route('home'));

        } catch (ValidationException $e) {
            Log::warning('Login validation failed.', [
                'email' => $request->input('email'),
                'errors' => $e->errors()
            ]);
            // The AuthService already threw the ValidationException with appropriate messages
            // Laravel will automatically redirect back with errors.
            // We just re-throw it here to stop execution.
            throw $e;
        } catch (\Exception $e) {
            // Catch unexpected errors during login attempt
            Log::error('Unexpected error during login.', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                         ->withInput($request->only('email', 'remember'))
                         ->withErrors(['email' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect('/'); // Redirect to home or login page after logout
    }
} 