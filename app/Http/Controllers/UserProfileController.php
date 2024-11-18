<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\BankingInstitutionService;

class UserProfileController extends Controller
{
    protected $bankingInstitutionService;

    public function __construct(BankingInstitutionService $bankingInstitutionService)
    {
        $this->bankingInstitutionService = $bankingInstitutionService;
    }

    //////////////////////////////////////////////////////////////////

    public function show()
    {
        $banks = $this->bankingInstitutionService->getBankingInstitutions();
        
        return view('pages.user.profile', [
            'banks' => $banks,
            'stateOptions' => [
                'JHR' => 'Johor',
                'KDH' => 'Kedah',
                'KTN' => 'Kelantan',
                'MLK' => 'Melaka',
                'NSN' => 'Negeri Sembilan',
                'PHG' => 'Pahang',
                'PNG' => 'Penang',
                'PRK' => 'Perak',
                'PLS' => 'Perlis',
                'SBH' => 'Sabah',
                'SWK' => 'Sarawak',
                'SGR' => 'Selangor',
                'TRG' => 'Terengganu',
                'KUL' => 'Kuala Lumpur',
                'LBN' => 'Labuan',
                'PJY' => 'Putrajaya'
            ]
        ]);
    }

    //////////////////////////////////////////////////////////////////

    public function update(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'second_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip_code' => 'required|string',
            'country' => 'required|string',
            'bank_name' => 'required|string',
            'account_holder' => 'required|string',
            'account_number' => 'required|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = auth()->user();

        // Handle profile picture
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $validated['profile_picture'] = $path;
        }

        // Update user information (excluding banking details)
        $user->update(collect($validated)->except(['bank_name', 'account_holder', 'account_number'])->toArray());

        // Update or create banking information
        $user->bankingInformation()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'bank_name' => $validated['bank_name'],
                'account_holder' => $validated['account_holder'],
                'account_number' => $validated['account_number']
            ]
        );

        return redirect()->route('profile')->with('success', 'Profile updated successfully');
    }

    //////////////////////////////////////////////////////////////////
}
