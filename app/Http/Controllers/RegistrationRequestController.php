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

class RegistrationRequestController extends Controller
{
    public function showRegistrationForm()
    {
        return view('pages.auth.register');
    }

    public function submitRequest(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
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
                'redirect' => route('register.success')
            ]);
        }

        return redirect()->route('register.success');
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

            // Get department ID from name
            $departmentId = DB::table('departments')
                ->where('name', $request->department)
                ->value('id');

            // Determine role based on department
            $roleId = match ($request->department) {
                'Human Resources' => Role::where('name', 'HR')->value('id'),
                'Finance and Account' => Role::where('name', 'Finance')->value('id'),
                'All' => Role::where('name', 'Admin')->value('id'),
                default => Role::where('name', 'Staff')->value('id'),
            };

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

            return response()->json([
                'success' => true,
                'message' => 'Registration request approved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Registration approval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve registration request'
            ], 500);
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

            return response()->json([
                'success' => true,
                'message' => 'Registration request rejected successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Registration rejection failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject registration request'
            ], 500);
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

        return redirect()->route('login')
            ->with('status', 'Password set successfully. You can now login.');
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

            // Determine role based on department
            $roleId = match ($request->department) {
                'Human Resources' => Role::where('name', 'HR')->value('id'),
                'Finance and Account' => Role::where('name', 'Finance')->value('id'),
                'All' => Role::where('name', 'Admin')->value('id'),
                default => Role::where('name', 'Staff')->value('id'),
            };

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

            return response()->json([
                'success' => true,
                'message' => 'Registration request approved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Registration approval failed from dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve registration request'
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
