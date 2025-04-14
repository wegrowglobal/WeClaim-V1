<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class LoginActivityService
{
    protected $request;
    protected $agent;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->agent = new Agent();
        $this->agent->setUserAgent($request->userAgent());
    }

    /**
     * Log a successful login attempt
     */
    public function logSuccessfulLogin(User $user): void
    {
        $this->logActivity($user, 'success');
    }

    /**
     * Log a failed login attempt
     */
    public function logFailedLogin(string $email): void
    {
        $this->logActivity(null, 'failed', $email);
    }

    /**
     * Log a login activity
     */
    private function logActivity(?User $user = null, string $status = 'failed', ?string $email = null): void
    {
        $ipAddress = $this->request->ip();
        $email = $email ?? ($user ? $user->email : null);
        
        // Get location data (this is optional and can be removed if not needed)
        $location = null;
        try {
            $response = Http::get("https://ipinfo.io/{$ipAddress}/json");
            if ($response->successful()) {
                $locationData = $response->json();
                $location = isset($locationData['city']) && isset($locationData['country']) 
                    ? $locationData['city'] . ', ' . $locationData['country'] 
                    : null;
            }
        } catch (\Exception $e) {
            // Silently fail - location is not critical
        }

        LoginActivity::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $this->request->userAgent(),
            'device' => $this->agent->device(),
            'browser' => $this->agent->browser(),
            'platform' => $this->agent->platform(),
            'status' => $status,
            'location' => $location,
        ]);
    }
} 