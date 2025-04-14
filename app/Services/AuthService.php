<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class AuthService
{
    private $loginActivityService;

    public function __construct(LoginActivityService $loginActivityService)
    {
        $this->loginActivityService = $loginActivityService;
    }
    
    public function attemptLogin(array $credentials, bool $remember = false): bool
    {
        // Record the attempt regardless of outcome
        $email = $credentials['email'] ?? null;
        
        if (!$email) {
            return false;
        }
        
        if (Auth::attempt($credentials, $remember)) {
            /** @var User|null $user */
            $user = User::with('role', 'department')->find(Auth::id());
            
            if (!$user) {
                $this->loginActivityService->logFailedLogin($email);
                return false;
            }
            
            Auth::setUser($user);
            
            // Log successful login
            $this->loginActivityService->logSuccessfulLogin($user);
            
            return true;
        }
        
        // Log failed login attempt
        $this->loginActivityService->logFailedLogin($email);
        
        return false;
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
