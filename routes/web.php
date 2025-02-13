<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\RegistrationRequestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserManagementController;
use Illuminate\Http\Request;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SystemConfigController;
use App\Http\Controllers\BulkEmailController;


// Guest Routes
Route::group([], function () {
    Route::get('/login', function () {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('pages.auth.login');
    })->name('login.form');

    Route::post('/login', [UserController::class, 'login'])->name('login');

    // Password Reset Routes
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::get('forgot-password/confirmation', [ForgotPasswordController::class, 'showConfirmation'])
        ->name('password.confirmation');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
    Route::view('password-reset-success', 'pages.auth.password-reset-success')
        ->name('password.reset.success');
});

// Authenticated Routes
Route::group([], function () {
    // Add these inside the authenticated routes group
    // Add inside the authenticated routes group
    Route::post('/registration-requests/{id}/approve', [RegistrationRequestController::class, 'approveFromDashboard'])
        ->name('registration-requests.approve-dashboard');

    Route::post('/registration-requests/{id}/reject', [RegistrationRequestController::class, 'rejectFromDashboard'])
        ->name('registration-requests.reject-dashboard');
    // Home Route
    Route::get('/', [ClaimController::class, 'home'])->name('home');

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.mark-all-as-read');
        Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.mark-as-read');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])
            ->name('notifications.unread-count');
    });

    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'show'])->name('profile');
        Route::put('/', [UserProfileController::class, 'update'])->name('profile.update');
    });

    // Claims Routes
    Route::middleware(['auth'])->prefix('claims')->name('claims.')->group(function () {
        // Main Claims Routes
        Route::get('/dashboard', [ClaimController::class, 'dashboard'])->name('dashboard');
        Route::get('/new', [ClaimController::class, 'new'])->name('new');
        Route::get('/approval', [ClaimController::class, 'approval'])->name('approval');

        // Bulk Email Routes
        Route::get('/bulk-email', [BulkEmailController::class, 'index'])->name('bulk-email');
        Route::post('/bulk-email/send', [BulkEmailController::class, 'sendBulkEmail'])->name('bulk-email.send');

        // Form Steps Routes
        Route::post('/save-step', [ClaimController::class, 'saveStep'])->name('save-step');
        Route::post('/reset-session', [ClaimController::class, 'resetSession'])->name('reset-session');
        Route::get('/get-step/{step}', [ClaimController::class, 'getStep'])->name('get-step');
        Route::get('/get-progress-steps/{step}', [ClaimController::class, 'getProgressSteps'])
            ->name('progress-steps');

        // Claim Actions
        Route::post('/store', [ClaimController::class, 'store'])->name('store');
        Route::get('/{id}/review', [ClaimController::class, 'reviewClaim'])->name('review');
        Route::post('/{claim}/export', [ClaimController::class, 'export'])->name('export');
        Route::put('/{claim}/cancel', [ClaimController::class, 'cancelClaim'])->name('cancel');

        // Document Routes
        Route::get('/{claim}/document/{type}/{filename}', [ClaimController::class, 'viewDocument'])
            ->name('view.document');

        // Email Actions
        Route::post('/send-to-datuk/{id}', [ClaimController::class, 'sendToDatuk'])
            ->name('mail.to.datuk');
        Route::get('/email-action/{id}/{action}', [ClaimController::class, 'handleEmailAction'])
            ->name('email.action');

        // Resubmit Routes
        Route::get('/resubmit/{claim}', [ClaimController::class, 'showResubmitForm'])->name('resubmit');
        Route::post('/resubmit/{claim}', [ClaimController::class, 'processResubmission'])->name('resubmit.process');

        // View Claim
        Route::get('/{id}', [ClaimController::class, 'show'])
            ->defaults('view', 'pages.claims.claim')
            ->name('view');

        // Claim Review Actions
        Route::post('/{id}/update', [ClaimController::class, 'updateClaim'])->name('update');
    });

    // Reports & Settings Routes
    Route::view('/report', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');

    // Password Change Routes
    Route::group([], function () {
        Route::get('/change-password', [UserController::class, 'showChangePassword'])->name('password.change');
        Route::post('/change-password', [UserController::class, 'changePassword']);
    });

    // Admin only routes
    Route::get('/admin/claims', function () {
        if (Auth::user()->role_id !== 5) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }
        return app(ClaimController::class)->adminIndex();
    })->name('claims.admin');

    Route::get('/claims/{claim}/edit', function (App\Models\Claim $claim) {
        if (Auth::user()->role_id !== 5) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        return app(ClaimController::class)->edit($claim);
    })->name('claims.edit');

    Route::put('/claims/{claim}', function (App\Models\Claim $claim, Request $request) {
        if (Auth::user()->role_id !== 5) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        return app(ClaimController::class)->update($claim, $request);
    })->name('claims.update');

    Route::delete('/claims/{claim}', function (App\Models\Claim $claim) {
        if (Auth::user()->role_id !== 5) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        return app(ClaimController::class)->destroy($claim);
    })->name('claims.destroy');

    // User Management Routes
    Route::get('/admin/users', function () {
        if (Auth::user()->role_id !== 5) {
            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }
        return app(UserManagementController::class)->index();
    })->name('users.management');

    Route::post(
        '/users',
        function (Request $request) {
            if (Auth::user()->role_id !== 5) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return app(UserManagementController::class)->store($request);
        }
    )->name('users.store');

    Route::put('/users/{id}', function (Request $request, $id) {
        if (Auth::user()->role_id !== 5) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        return app(UserManagementController::class)->update($request, $id);
    })->name('users.update');

    Route::delete('/users/{id}', function ($id) {
        if (Auth::user()->role_id !== 5) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        return app(UserManagementController::class)->destroy($id);
    })->name('users.destroy');
});

// Logout Route
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Success Page Route
Route::get('/claims/success', function () {
    return view('pages.claims.success');
})->name('claims.success.page');

Route::get('/register', [RegistrationRequestController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegistrationRequestController::class, 'submitRequest'])->name('register.request');
Route::get('/register/confirmation', [RegistrationRequestController::class, 'showConfirmation'])->name('register.confirmation');
Route::get('/register/approve/{token}', [RegistrationRequestController::class, 'approveRequest'])->name('register.approve');
Route::get('/password/set/{token}', [RegistrationRequestController::class, 'showSetPasswordForm'])->name('password.set');
Route::post('/password/save', [RegistrationRequestController::class, 'savePassword'])->name('password.save');
Route::get('/register/reject/{token}', [RegistrationRequestController::class, 'rejectRequest'])->name('register.reject');
Route::get('/register/success', function () {
    return view('pages.auth.register-success');
})->name('register.success');

Route::get('/set-password/{token}', [RegistrationRequestController::class, 'showSetPasswordForm'])
    ->name('password.setup.form');

Route::post('/set-password/{token}', [RegistrationRequestController::class, 'setPassword'])
    ->name('password.setup');

Route::get('/password/setup-success', [RegistrationRequestController::class, 'showPasswordSetupSuccess'])
    ->name('password.setup.success');

Route::get('/coming-soon', function () {
    return view('pages.coming-soon');
})->name('coming-soon');
