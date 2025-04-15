<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\Auth\Role;
use App\Models\User\Department;
use App\Models\Auth\RegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Users must be authenticated, verified admins to access any method in this controller
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('admin');
    }

    public function index()
    {
        $users = User::query()
            ->with(['role', 'department'])
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('second_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(request('role'), function ($query, $role) {
                $query->where('role_id', $role);
            })
            ->when(request('department'), function ($query, $department) {
                $query->where('department_id', $department);
            })
            ->paginate(10);

        $pendingRequests = RegistrationRequest::query()
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('SQL Query: ' . RegistrationRequest::query()->orderBy('created_at', 'desc')->toSql());
        Log::info('Total Records: ' . $pendingRequests->count());

        return view('pages.admin.users.index', [
            'users' => $users,
            'roles' => Role::all(),
            'departments' => Department::all(),
            'pendingRequests' => $pendingRequests,
            'filters' => [
                'search' => request('search'),
                'role' => request('role'),
                'department' => request('department'),
                'sort' => request('sort'),
                'direction' => request('direction')
            ]
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role_id !== 5) {
            abort(403);
        }

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'second_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
            'phone' => 'nullable|string|max:20',
        ]);

        $tempPassword = Str::random(12);

        try {
            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'second_name' => $validatedData['second_name'],
                'email' => $validatedData['email'],
                'role_id' => $validatedData['role_id'],
                'department_id' => $validatedData['department_id'],
                'phone' => $validatedData['phone'] ?? null,
                'password' => Hash::make($tempPassword),
                'email_verified_at' => now(),
                'password_setup_token' => Str::random(64)
            ]);

            // Optional: Send email with temporary password
            // Mail::to($user->email)->send(new UserCreatedMail($user, $tempPassword));

            Log::info('User created by SU', [
                'created_user_id' => $user->id,
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role_id !== 5) {
            abort(403);
        }

        $user = User::findOrFail($id);

        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'second_name' => 'required|string|max:255',
                'role_id' => 'required|exists:roles,id',
                'department_id' => 'required|exists:departments,id',
                'phone' => 'nullable|string|max:20'
            ]);

            $user->update($validatedData);

            Log::info('User updated by SU', [
                'updated_user_id' => $user->id,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user->fresh()->load(['role', 'department'])
            ]);
        } catch (\Exception $e) {
            Log::error('User update failed', [
                'error' => $e->getMessage(),
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->role_id !== 5) {
            abort(403);
        }

        $user = User::findOrFail($id);

        try {
            // Prevent deleting the last SU
            $suCount = User::where('role_id', 5)->count();
            if ($suCount <= 1 && $user->role_id === 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the last Super User'
                ], 400);
            }

            $user->delete();

            Log::info('User deleted by SU', [
                'deleted_user_id' => $id,
                'deleted_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('User deletion failed', [
                'error' => $e->getMessage(),
                'deleted_by' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
