<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function attemptLogin(array $credentials, bool $remember = false)
    {
        if (Auth::attempt($credentials, $remember)) {
            $user = User::with('role', 'department')->find(Auth::id());
            Auth::setUser($user);
            return true;
        }
        return false;
    }

    public function logout()
    {
        Auth::logout();
    }
}
