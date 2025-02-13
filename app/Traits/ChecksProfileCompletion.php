<?php

namespace App\Traits;

trait ChecksProfileCompletion
{
    protected function isProfileComplete($user)
    {
        return $user->first_name && 
            $user->second_name && 
            $user->phone && 
            $user->address && 
            $user->city && 
            $user->state && 
            $user->zip_code && 
            $user->country && 
            $user->bankingInformation && 
            $user->bankingInformation->bank_name && 
            $user->bankingInformation->account_holder && 
            $user->bankingInformation->account_number;
    }

    protected function checkProfileCompletion()
    {
        $user = auth()->user();
        
        if ($user->role_id === 1 && !$this->isProfileComplete($user)) {
            return redirect()
                ->route('profile')
                ->with('warning', 'Please complete your profile before creating a claim.');
        }

        return null;
    }
} 