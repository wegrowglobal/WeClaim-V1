<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    //////////////////////////////////////////////////////////////////

    protected $authService;
    protected $auth;
    private const LOGIN_ROUTE = 'login';
    const LOGIN_FAILED_MESSAGE = 'Login failed. Please check your email and password.';

    //////////////////////////////////////////////////////////////////

    public function __construct(AuthService $authService, Auth $auth)
    {
        $this->authService = $authService;
        $this->auth = $auth;
    }

    //////////////////////////////////////////////////////////////////

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if ($this->authService->attemptLogin($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            $request->session()->put('user_role', $user->role->name);
            $request->session()->put('user_department', $user->department->name);
            return redirect()->route('home');
        }

        return back()->withErrors([
            'email' => self::LOGIN_FAILED_MESSAGE,
        ])->withInput($request->only('email'));
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
