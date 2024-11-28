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


// Guest Routes
Route::middleware('guest')->group(function () {
    // Authentication Routes
    Route::get('/login', function () {
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
Route::middleware(['auth'])->group(function () {
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
    Route::prefix('claims')->group(function () {
        // Main Claims Routes
        Route::get('/dashboard', [ClaimController::class, 'dashboard'])->name('claims.dashboard');
        Route::get('/new', [ClaimController::class, 'new'])->name('claims.new');
        Route::get('/approval', [ClaimController::class, 'approval'])->name('claims.approval');

        // Form Steps Routes
        Route::post('/save-step', [ClaimController::class, 'saveStep'])->name('claims.save-step');
        Route::post('/reset-session', [ClaimController::class, 'resetSession'])->name('claims.reset-session');
        Route::get('/get-step/{step}', [ClaimController::class, 'getStep'])->name('claims.get-step');
        Route::get('/get-progress-steps/{step}', [ClaimController::class, 'getProgressSteps'])
            ->name('claims.progress-steps');

        // Claim Actions
        Route::post('/store', [ClaimController::class, 'store'])->name('claims.store');
        Route::get('/{id}/review', [ClaimController::class, 'reviewClaim'])->name('claims.review');
        Route::post('/{claim}/export', [ClaimController::class, 'export'])->name('claims.export');
        Route::put('/{claim}/cancel', [ClaimController::class, 'cancelClaim'])->name('claims.cancel');

        // Document Routes
        Route::get('/{claim}/document/{type}/{filename}', [ClaimController::class, 'viewDocument'])
            ->name('claims.view.document');

        // Email Actions
        Route::post('/send-to-datuk/{id}', [ClaimController::class, 'sendToDatuk'])
            ->name('claims.mail.to.datuk');
        Route::get('/email-action/{id}', [ClaimController::class, 'handleEmailAction'])
            ->name('claims.email.action');

        // Resubmission Routes
        Route::get('/{claim}/resubmit', [ClaimController::class, 'resubmit'])->name('claims.resubmit');
        Route::post('/{claim}/resubmit', [ClaimController::class, 'processResubmission'])
            ->name('claims.process-resubmission');

        // View Claim
        Route::get('/{id}', [ClaimController::class, 'show'])
            ->defaults('view', 'pages.claims.claim')
            ->name('claims.view');

        // Claim Review Actions
        Route::post('/{id}/update', [ClaimController::class, 'updateClaim'])->name('claims.update');
    });

    // Reports & Settings Routes
    Route::view('/report', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');

    // Password Change Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/change-password', [UserController::class, 'showChangePassword'])->name('password.change');
        Route::post('/change-password', [UserController::class, 'changePassword']);
    });

    Route::middleware(['auth'])->group(function () {
        // Admin only routes
        Route::get('/admin/claims', function () {
            if (Auth::user()->role_id !== 5) {
                return redirect()->route('home')->with('error', 'Unauthorized access.');
            }
            return app(ClaimController::class)->adminIndex();
        })->name('claims.admin');

        Route::delete('/claims/{claim}', function (App\Models\Claim $claim) {
            if (Auth::user()->role_id !== 5) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return app(ClaimController::class)->destroy($claim);
        })->name('claims.destroy');
    });
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
