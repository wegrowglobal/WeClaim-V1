<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class AuthService
{
    public function attemptLogin(array $credentials, bool $remember = false): bool
    {
        if (Auth::attempt($credentials, $remember)) {
            /** @var User|null $user */
            $user = User::with('role', 'department')->find(Auth::id());
            
            if (!$user) {
                return false;
            }
            
            Auth::setUser($user);
            return true;
        }
        return false;
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
