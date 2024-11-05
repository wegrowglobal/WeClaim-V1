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

final class UserController extends Controller
{
    //////////////////////////////////////////////////////////////////

    protected $authService;
    protected $auth;
    private const LOGIN_ROUTE = 'login';
    private const HOME_ROUTE = 'home';
    private const LOGIN_FAILED_MESSAGE = 'Login failed. Please check your email and password.';

    //////////////////////////////////////////////////////////////////

    public function __construct(AuthService $authService, Auth $auth)
    {
        $this->authService = $authService;
        $this->auth = $auth;
    }

    //////////////////////////////////////////////////////////////////

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
                throw new \RuntimeException('User not found after successful login');
            }

            $request->session()->regenerate();
            $request->session()->put([
                'user_role' => $user->role?->name,
                'user_department' => $user->department?->name
            ]);

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

    //////////////////////////////////////////////////////////////////

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

    //////////////////////////////////////////////////////////////////

}
