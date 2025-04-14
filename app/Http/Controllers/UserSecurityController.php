<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserSecurityController extends Controller
{
    /**
     * Display the user's login activity history
     */
    public function loginActivity(): View
    {
        $user = Auth::user();
        $activities = LoginActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('pages.user.login-activity', [
            'activities' => $activities
        ]);
    }
    
    /**
     * Display failed login attempts for admins
     */
    public function failedLogins(): View
    {
        $this->authorize('view-failed-logins');
        
        $failedLogins = LoginActivity::where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('pages.admin.failed-logins', [
            'failedLogins' => $failedLogins
        ]);
    }

    /**
     * Check the remember me token status
     */
    public function checkRememberToken(): View
    {
        $user = Auth::user();
        $hasRememberToken = !empty($user->remember_token);
        $sessionLifetime = config('session.lifetime');
        $isRemembered = Auth::viaRemember();
        
        return view('pages.user.remember-token-status', [
            'hasRememberToken' => $hasRememberToken,
            'sessionLifetime' => $sessionLifetime,
            'isRemembered' => $isRemembered
        ]);
    }
} 