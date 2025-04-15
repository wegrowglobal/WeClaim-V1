<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\BankingInstitutionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    protected $bankingInstitutionService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BankingInstitutionService $bankingInstitutionService)
    {
        $this->bankingInstitutionService = $bankingInstitutionService;
        // Apply authentication middleware to all methods
        $this->middleware('auth');
        $this->middleware('track.activity');
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
                'account_holder_name' => 'required|string',
                'account_number' => 'required|string',
                'signature_path' => 'nullable|string'
            ]);

            Log::info('Starting profile update', [
                'user_id' => $user->id,
                'has_file' => $request->hasFile('profile_picture'),
                'signature_path' => $request->signature_path,
                'all_data' => $request->all()
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
                if ($user->profile_picture && file_exists(public_path($user->profile_picture))) {
                    unlink(public_path($user->profile_picture));
                }

                // Store new profile picture with unique name
                $fileName = 'profile-' . $user->id . '-' . time() . '.' . $file->getClientOriginalExtension();
                $path = 'images/profiles/' . $fileName;
                $file->move(public_path('images/profiles'), $fileName);

                Log::info('New profile picture stored', [
                    'file_name' => $fileName,
                    'full_path' => $path,
                    'exists' => file_exists(public_path($path))
                ]);

                $user->profile_picture = $path;
                $user->save();
            }

            // Update user information including signature path
            $userData = $request->only([
                'first_name',
                'second_name',
                'email',
                'phone',
                'address',
                'city',
                'state',
                'zip_code',
                'country'
            ]);

            // Handle signature path separately to ensure it's not overwritten if empty
            if ($request->filled('signature_path')) {
                $userData['signature_path'] = $request->signature_path;
                Log::info('Updating signature path', [
                    'user_id' => $user->id,
                    'new_path' => $request->signature_path
                ]);
            }

            Log::info('Updating user data', [
                'user_id' => $user->id,
                'data' => $userData
            ]);

            $user->update($userData);

            // Handle banking information
            Log::info('Updating banking information', [
                'user_id' => $user->id,
                'bank_name' => $request->bank_name,
                'account_holder_name' => $request->account_holder_name,
                'account_number' => substr($request->account_number, 0, 4) . '****' // Log only first 4 digits for security
            ]);

            $user->bankingInformation()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bank_name' => $request->bank_name,
                    'account_holder_name' => $request->account_holder_name,
                    'account_number' => $request->account_number
                ]
            );

            Log::info('Banking information updated successfully');

            Log::info('Profile update completed successfully');

            return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            Log::error('Profile update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to update profile.'])->withInput();
        }
    }
}
