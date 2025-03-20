<?php

namespace App\Http\Controllers;

use App\Models\RegistrationRequest;
use App\Mail\RegistrationApprovalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegistrationRequestRequest;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\AccountCreated;
use Illuminate\Mail\Mailable;
use App\Mail\RegistrationRejected;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use App\Mail\RegistrationRequestSubmitted;
use App\Mail\RegistrationRequestApproved;
use App\Mail\RegistrationRequestRejected;
use App\Mail\PasswordSetupInvitation;

class RegistrationRequestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Apply guest middleware to registration form and submission
        $this->middleware('guest')->only([
            'showRegistrationForm', 
            'submitRequest', 
            'showConfirmation',
            'showSetPasswordForm',
            'setPassword',
            'showPasswordSetupSuccess'
        ]);
        
        // Apply auth and appropriate role middleware to admin actions
        $this->middleware('auth')->only([
            'approveFromDashboard',
            'rejectFromDashboard'
        ]);
        
        $this->middleware('role:1,5')->only([
            'approveFromDashboard',
            'rejectFromDashboard'
        ]);
        
        // Apply signed route middleware to email approval/rejection links
        $this->middleware('signed')->only([
            'approveRequest',
            'rejectRequest'
        ]);
    }

    public function showRegistrationForm()
    {
        return view('pages.auth.register');
    }

    public function submitRequest(Request $request)
    {
        try {
            // Check for existing registration request
            $existingRequest = RegistrationRequest::where('email', $request->email)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingRequest) {
                if ($existingRequest->status === 'pending') {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You already have a pending registration request. Please wait for approval.',
                            'redirect' => route('register.success')
                        ], 422);
                    }
                    return redirect()->route('register.success')
                        ->with('info', 'You already have a pending registration request. Please wait for approval.');
                } else {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'An account with this email already exists.',
                            'redirect' => route('login.form')
                        ], 422);
                    }
                    return redirect()->route('login.form')
                        ->with('info', 'An account with this email already exists.');
                }
            }

            // Check for existing user
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'An account with this email already exists.',
                        'redirect' => route('login.form')
                    ], 422);
                }
                return redirect()->route('login.form')
                    ->with('info', 'An account with this email already exists.');
            }

            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'department' => 'required|string'
            ]);

            $registrationRequest = RegistrationRequest::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'department' => $validated['department'],
                'status' => 'pending',
                'token' => Str::random(64)
            ]);

            Mail::to("it@wegrow-global.com")->send(
                new RegistrationApprovalRequest($registrationRequest)
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration request submitted successfully.',
                    'redirect' => route('register.success')
                ]);
            }

            return redirect()->route('register.success')
                ->with('success', 'Registration request submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Registration request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit registration request. Please try again later.'
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to submit registration request. Please try again later.');
        }
    }

    public function showConfirmation()
    {
        return view('pages.auth.register-confirmation');
    }

    public function approveRequest($token)
    {
        try {
            $request = RegistrationRequest::where('token', $token)
                ->where('status', 'pending')
                ->firstOrFail();

            Log::info('Processing registration approval', [
                'token' => $token,
                'department' => $request->department,
                'email' => $request->email
            ]);

            // Get department ID from name with case-insensitive search
            $departmentId = DB::table('departments')
                ->whereRaw('LOWER(name) = ?', [strtolower($request->department)])
                ->value('id');

            if (!$departmentId) {
                Log::error('Department not found after all attempts', [
                    'requested_department' => $request->department,
                    'available_departments' => DB::table('departments')->pluck('name')
                ]);
                return view('pages.auth.registration-error', [
                    'message' => 'Department not found. Please contact administrator.'
                ]);
            }

            // All new users get role_id 1 (staff)
            $roleId = 1;

            DB::beginTransaction();
            try {
                // Create user with temporary token for password setup
                $passwordToken = Str::random(64);
                $user = User::create([
                    'first_name' => $request->first_name,
                    'second_name' => $request->last_name,
                    'email' => $request->email,
                    'department_id' => $departmentId,
                    'password' => Hash::make(Str::random(32)),
                    'password_setup_token' => $passwordToken,
                    'role_id' => $roleId
                ]);

                $request->update(['status' => 'approved']);

                Mail::to($user->email)->send(new AccountCreated($user, $passwordToken));

                DB::commit();

                return view('pages.auth.registration-approved');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to create user or update request status', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'token' => $token,
                    'email' => $request->email
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Registration approval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'token' => $token
            ]);

            return view('pages.auth.registration-error', [
                'message' => 'Failed to approve registration request. Please try again later.'
            ]);
        }
    }

    public function rejectRequest($token)
    {
        try {
            $request = RegistrationRequest::where('token', $token)
                ->where('status', 'pending')
                ->firstOrFail();

            $request->update(['status' => 'rejected']);

            Mail::to($request->email)->send(new RegistrationRejected($request));

            return view('pages.auth.registration-rejected');
        } catch (\Exception $e) {
            Log::error('Registration rejection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('pages.auth.registration-error', [
                'message' => 'Failed to reject registration request. Please try again later.'
            ]);
        }
    }

    public function showSetPasswordForm($token)
    {
        $user = User::where('password_setup_token', $token)->firstOrFail();

        return view('pages.auth.set-password', [
            'token' => $token,
            'email' => $user->email
        ]);
    }

    public function setPassword(Request $request, $token)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('password_setup_token', $token)->firstOrFail();

        $user->update([
            'password' => Hash::make($request->password),
            'password_setup_token' => null,
            'email_verified_at' => now()
        ]);

        return redirect()->route('password.setup.success');
    }

    public function showPasswordSetupSuccess()
    {
        return view('pages.auth.password-setup-success');
    }

    public function approveFromDashboard($id)
    {
        try {
            $request = RegistrationRequest::findOrFail($id);

            if ($request->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This request has already been processed'
                ], 400);
            }

            // Get department ID from name
            $departmentId = DB::table('departments')
                ->where('name', $request->department)
                ->value('id');

            if (!$departmentId) {
                Log::error('Department not found', ['department' => $request->department]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid department configuration'
                ], 400);
            }

            // All new users get role_id 1 (staff)
            $roleId = 1;

            DB::beginTransaction();
            try {
                // Create user with temporary token for password setup
                $passwordToken = Str::random(64);
                $user = User::create([
                    'first_name' => $request->first_name,
                    'second_name' => $request->last_name,
                    'email' => $request->email,
                    'department_id' => $departmentId,
                    'password' => Hash::make(Str::random(32)),
                    'password_setup_token' => $passwordToken,
                    'role_id' => $roleId
                ]);

                $request->update(['status' => 'approved']);

                Mail::to($user->email)->send(new AccountCreated($user, $passwordToken));

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Registration request approved successfully'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Registration approval failed from dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve registration request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rejectFromDashboard($id)
    {
        try {
            $request = RegistrationRequest::findOrFail($id);

            if ($request->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This request has already been processed'
                ], 400);
            }

            $request->update(['status' => 'rejected']);

            Mail::to($request->email)->send(new RegistrationRejected($request));

            return response()->json([
                'success' => true,
                'message' => 'Registration request rejected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Registration rejection failed from dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject registration request'
            ], 500);
        }
    }
}
