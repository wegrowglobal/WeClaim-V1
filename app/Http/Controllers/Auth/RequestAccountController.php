<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreRegistrationRequest;
use App\Models\Auth\RegistrationRequest;
use App\Models\User\Department;
use App\Models\Auth\Role;
use Illuminate\Http\Request; // Keep Request for potential future use, though StoreRegistrationRequest handles validation
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationApprovalRequest; // Assuming this mail exists for notifying admins
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\User\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Mail\RegistrationRequestApproved; // Assuming exists
use App\Mail\RegistrationRequestRejected; // Assuming exists
use App\Mail\PasswordSetupInvitation; // Assuming exists
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Auth\SetPasswordRequest; // Need to create this
use Illuminate\Validation\ValidationException;

class RequestAccountController extends Controller
{
    /**
     * Show the account registration request form.
     */
    public function create(): View
    {
        // Fetch necessary data for the form (e.g., departments, roles)
        $departments = Department::orderBy('name')->pluck('name', 'id');
        // Assuming only certain roles can be requested, adjust if needed
        $roles = Role::whereIn('id', [1, 2, 3, 4])->orderBy('name')->pluck('name', 'id'); 

        return view('auth.request.request', compact('departments', 'roles'));
    }

    /**
     * Store a newly created registration request in storage.
     */
    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $registrationRequest = RegistrationRequest::create([
                'first_name' => $validatedData['first_name'],
                'second_name' => $validatedData['second_name'],
                'email' => $validatedData['email'],
                'role_id' => $validatedData['role_id'],
                'department_id' => $validatedData['department_id'],
                'status' => 'Pending', // Default status from migration
                'token' => Str::random(64),
                'token_expires_at' => Carbon::now()->addHours(24), // Set token expiry (e.g., 24 hours)
            ]);

            // Notify administrators (adjust recipient as needed)
            // Assuming an admin email is configured in .env or config
            $adminEmail = config('mail.admin_address', 'it@wegrow-global.com'); 
            Mail::to($adminEmail)->send(new RegistrationApprovalRequest($registrationRequest));
            Log::info('Registration request submitted and admin notified.', ['email' => $validatedData['email']]);

            // Redirect to a success/confirmation page
            // Assuming a route named 'register.success' exists for the confirmation page
            return redirect()->route('register.success') 
                         ->with('success', 'Your registration request has been submitted successfully. Please wait for approval.');

        } catch (\Exception $e) {
            Log::error('Failed to store registration request.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->safe()->except(['password', 'password_confirmation']), // Log safe data
            ]);

            // Redirect back with error message
            return back()->withInput()
                       ->with('error', 'An unexpected error occurred while submitting your request. Please try again later.');
        }
    }

    /**
     * Approve a registration request via signed URL.
     */
    public function approve(Request $request, string $token): View
    {
        try {
            // Find the request, ensuring it's pending and token hasn't expired
            $registrationRequest = RegistrationRequest::where('token', $token)
                ->where('status', 'Pending')
                ->firstOrFail();

            if ($registrationRequest->isTokenExpired()) {
                 Log::warning('Attempt to approve request with expired token.', ['token' => $token]);
                 return view('auth.request.request-error', ['message' => 'This approval link has expired.']);
            }

            // Use a transaction to ensure atomicity
            DB::beginTransaction();

            // 1. Create the User
            $passwordSetupToken = Str::random(64); // Token for setting the initial password
            $user = User::create([
                'first_name' => $registrationRequest->first_name,
                'second_name' => $registrationRequest->second_name,
                'email' => $registrationRequest->email,
                'role_id' => $registrationRequest->role_id,
                'department_id' => $registrationRequest->department_id,
                'password' => Hash::make(Str::random(40)), // Set a temporary strong random password
                'password_setup_token' => $passwordSetupToken,
                'password_setup_expires_at' => Carbon::now()->addHours(48), // Link expiry
                'email_verified_at' => now(), // Mark email as verified since it came through request
                // Add other necessary user fields if any, e.g., is_active = true
            ]);

            // 2. Update Registration Request Status
            $registrationRequest->status = 'Approved';
            // Optionally nullify the token after use
            // $registrationRequest->token = null; 
            // $registrationRequest->token_expires_at = null;
            $registrationRequest->save();

            // 3. Send Password Setup Email to User
            Mail::to($user->email)->send(new PasswordSetupInvitation($user, $passwordSetupToken));

            DB::commit();

            Log::info('Registration request approved and user created.', ['request_id' => $registrationRequest->id, 'user_id' => $user->id, 'email' => $user->email]);
            return view('auth.request.request-approved'); // Show an approval confirmation view

        } catch (ModelNotFoundException $e) {
            Log::error('Registration approval failed: Request not found or already processed.', ['token' => $token]);
            return view('auth.request.request-error', ['message' => 'Invalid or expired approval link.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve registration request.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'token' => $token,
            ]);
            return view('auth.request.request-error', ['message' => 'An unexpected error occurred during approval. Please contact support.']);
        }
    }

    /**
     * Reject a registration request via signed URL.
     */
    public function reject(Request $request, string $token): View
    {
         try {
            // Find the request, ensuring it's pending
            $registrationRequest = RegistrationRequest::where('token', $token)
                ->where('status', 'Pending')
                ->firstOrFail();
                
             // Optionally check token expiry for rejection as well
             if ($registrationRequest->isTokenExpired()) {
                 Log::warning('Attempt to reject request with expired token.', ['token' => $token]);
                 // Decide if expired token prevents rejection - maybe still allow rejection?
                 // return view('auth.request.request-error', ['message' => 'This rejection link has expired.']);
             }

            $registrationRequest->status = 'Rejected';
            // Optionally nullify the token after use
            // $registrationRequest->token = null; 
            // $registrationRequest->token_expires_at = null;
            $registrationRequest->save();

            // Notify the user of rejection
            Mail::to($registrationRequest->email)->send(new RegistrationRequestRejected($registrationRequest));

            Log::info('Registration request rejected.', ['request_id' => $registrationRequest->id, 'email' => $registrationRequest->email]);
            return view('auth.request.request-rejected'); // Show a rejection confirmation view

        } catch (ModelNotFoundException $e) {
            Log::error('Registration rejection failed: Request not found or already processed.', ['token' => $token]);
            return view('auth.request.request-error', ['message' => 'Invalid or expired rejection link.']);
        } catch (\Exception $e) {
            Log::error('Failed to reject registration request.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'token' => $token,
            ]);
            return view('auth.request.request-error', ['message' => 'An unexpected error occurred during rejection. Please contact support.']);
        }
    }
    
    /**
     * Show the password setup form.
     */
    public function showSetPasswordForm(string $token): View
    {
        try {
            // Find user by the password setup token
            $user = User::where('password_setup_token', $token)->firstOrFail();

            // Check if token is expired
            if (!$user->password_setup_token || !$user->password_setup_expires_at || Carbon::now()->gt($user->password_setup_expires_at)) {
                 Log::warning('Attempt to access password setup with invalid/expired token.', ['token' => $token]);
                return view('auth.password.link-expired'); // Show link expired view
            }
            
            // Check if password has already been set (token should be nullified after setup)
            // If password_setup_token is still present but password is not the temporary one, something might be wrong
            // Or, more simply, check if email_verified_at is set and password_setup_token is null.
            // A more robust check might involve a specific status field or checking if the password hash matches the temp one.
            if ($user->password_setup_token === null) { // Simple check: if token is null, password was set
                 Log::info('Attempt to access password setup form after password already set.', ['user_id' => $user->id]);
                return view('auth.password.already-set'); // Show password already set view
            }

            return view('auth.password.set-password', [
                'token' => $token,
                'email' => $user->email // Pass the email to the view
            ]);

        } catch (ModelNotFoundException $e) {
             Log::error('Password setup form access failed: User not found for token.', ['token' => $token]);
            return view('auth.password.link-invalid'); // Show invalid link view
        }
    }

    /**
     * Set the user's password.
     */
    public function setPassword(SetPasswordRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $token = $validated['token'];

            // Find user by the password setup token
            $user = User::where('password_setup_token', $token)->firstOrFail();

            // Double-check token validity/expiry
            if (!$user->password_setup_token || !$user->password_setup_expires_at || Carbon::now()->gt($user->password_setup_expires_at)) {
                 Log::warning('Attempt to set password with invalid/expired token.', ['token' => $token]);
                 // Redirect back with error or to an expired link page
                 return redirect()->route('password.setup.form', ['token' => $token]) // Redirect back to form maybe?
                                  ->with('error', 'Your password setup link has expired. Please request a new one if needed.');
            }
            
             // Check if password has already been set
            if ($user->password_setup_token === null) {
                 Log::info('Attempt to set password after password already set.', ['user_id' => $user->id]);
                 return redirect()->route('login') // Redirect to login
                                  ->with('info', 'Your password has already been set. Please log in.');
            }

            // Update the password
            $user->password = Hash::make($validated['password']);
            $user->password_setup_token = null; // Nullify token after successful setup
            $user->password_setup_expires_at = null;
            $user->email_verified_at = $user->email_verified_at ?? now(); // Ensure email is marked verified
            // Optionally set user status to active if not already
            // $user->is_active = true;
            $user->save();

            Log::info('User password set successfully.', ['user_id' => $user->id]);

            // Redirect to a success page or login page
            return redirect()->route('password.setup.success') // Redirect to a dedicated success view
                         ->with('success', 'Your password has been set successfully! You can now log in.');

        } catch (ModelNotFoundException $e) {
            Log::error('Password setting failed: User not found for token.', ['token' => $request->input('token')]);
             return redirect()->back()->with('error', 'Invalid password setup link.'); // Or redirect to invalid link page
        } catch (ValidationException $e) {
             return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to set user password.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'token' => $request->input('token') // Ensure token is logged if available
            ]);
             return redirect()->back()->with('error', 'An unexpected error occurred while setting your password. Please try again.');
        }
    }

    /**
     * Show the password setup success page.
     */
    public function showPasswordSetupSuccess(): View
    {
        return view('auth.password.setup-success'); // Simple view confirming success
    }
} 