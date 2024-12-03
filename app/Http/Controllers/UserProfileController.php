<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\BankingInstitutionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    protected $bankingInstitutionService;

    public function __construct(BankingInstitutionService $bankingInstitutionService)
    {
        $this->bankingInstitutionService = $bankingInstitutionService;
    }

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

    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'first_name' => 'required|string|max:255',
                'second_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'zip_code' => 'required|string',
                'country' => 'required|string',
                'bank_name' => 'required|string',
                'account_holder' => 'required|string',
                'account_number' => 'required|string'
            ]);

            Log::info('Starting profile update', [
                'user_id' => $user->id,
                'has_file' => $request->hasFile('profile_picture')
            ]);

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');

                Log::info('Profile picture upload started', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'user_id' => $user->id
                ]);

                // Delete old profile picture if exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Log::info('Deleting old profile picture', [
                        'path' => $user->profile_picture
                    ]);
                    Storage::disk('public')->delete($user->profile_picture);
                }

                // Store new profile picture with unique name
                $fileName = 'profile-' . $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('profile-pictures', $fileName, 'public');

                Log::info('New profile picture stored', [
                    'file_name' => $fileName,
                    'full_path' => $path,
                    'exists' => Storage::disk('public')->exists($path)
                ]);

                $user->profile_picture = $path;
                $user->save();
            }

            // Update other user information
            $user->update($request->only([
                'first_name',
                'second_name',
                'email',
                'phone',
                'address',
                'city',
                'state',
                'zip_code',
                'country'
            ]));

            // Handle banking information
            $user->bankingInformation()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bank_name' => $request->bank_name,
                    'account_holder' => $request->account_holder,
                    'account_number' => $request->account_number
                ]
            );

            Log::info('Profile update completed successfully');

            return redirect()->route('profile')->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            Log::error('Profile update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to update profile.'])->withInput();
        }
    }
}
