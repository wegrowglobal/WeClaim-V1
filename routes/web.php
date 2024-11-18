<?php

use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

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
            ->name('notifications.markAsRead');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])
            ->name('notifications.unreadCount');
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
        Route::post('/{id}', [ClaimController::class, 'updateClaim'])->name('claims.update');
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
            
        // View Claim
        Route::get('/{id}', [ClaimController::class, 'show'])
            ->defaults('view', 'pages.claims.claim')
            ->name('claims.view');

        // Resubmission Routes
        Route::get('/{id}/resubmit', [ClaimController::class, 'resubmit'])->name('claims.resubmit');
        Route::put('/{id}/resubmit', [ClaimController::class, 'processResubmission'])->name('claims.process-resubmission');
    });

    // Reports & Settings Routes
    Route::view('/report', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');
});

// Logout Route
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Success Page Route
Route::get('/claims/success', function () {
    return view('pages.claims.success');
})->name('claims.success.page');
