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
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\MiddlewareTestController;


// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('pages.auth.login');
    })->name('login.form');

    // Get published changelogs for login page
    Route::get('/changelogs/published', [ChangelogController::class, 'getPublishedChangelogs'])
        ->name('changelogs.published');

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
        
    // Registration Routes
    Route::prefix('register')->name('register.')->group(function () {
        Route::get('/', [RegistrationRequestController::class, 'showRegistrationForm'])->name('form');
        Route::post('/', [RegistrationRequestController::class, 'submitRequest'])->name('request');
        Route::get('/confirmation', [RegistrationRequestController::class, 'showConfirmation'])->name('confirmation');
        Route::get('/success', function () {
            return view('pages.auth.register-success');
        })->name('success');
    });
    
    // Password Setup Routes
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/set/{token}', [RegistrationRequestController::class, 'showSetPasswordForm'])->name('set');
        Route::post('/save', [RegistrationRequestController::class, 'savePassword'])->name('save');
        Route::get('/setup/{token}', [RegistrationRequestController::class, 'showSetPasswordForm'])->name('setup.form');
        Route::post('/setup/{token}', [RegistrationRequestController::class, 'setPassword'])->name('setup');
        Route::get('/setup-success', [RegistrationRequestController::class, 'showPasswordSetupSuccess'])->name('setup.success');
    });
});

// Email Action Route - No authentication required for Datuk approval/rejection via email
Route::get('/claims/email-action/{id}/{action}', [ClaimController::class, 'handleEmailAction'])
    ->middleware('signed')
    ->name('claims.email.action');

// Public Routes
Route::get('/coming-soon', function () {
    return view('pages.coming-soon');
})->name('coming-soon');

// Routes for all authenticated users
Route::middleware(['auth', 'track.activity'])->group(function () {
    // Home Route
    Route::get('/', [ClaimController::class, 'home'])->name('home');
    
    // Logout Route
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    
    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserProfileController::class, 'show'])->name('profile');
        Route::put('/', [UserProfileController::class, 'update'])->name('profile.update');
    });

    // Signature Routes
    Route::prefix('signature')->group(function () {
        Route::post('/', [SignatureController::class, 'store'])->name('signature.store');
        Route::delete('/', [SignatureController::class, 'destroy'])->name('signature.destroy');
    });
    
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
    
    // Password Change Routes
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/change', [UserController::class, 'showChangePassword'])->name('change');
        Route::post('/change', [UserController::class, 'changePassword'])
            ->middleware(['verified'])
            ->name('change.submit');
    });
    
    // Routes that require completed profile
    Route::middleware(['profile.complete'])->group(function () {
        // Claims Routes
        Route::prefix('claims')->name('claims.')->group(function () {
            // Main Claims Routes
            Route::get('/dashboard', [ClaimController::class, 'dashboard'])->name('dashboard');
            Route::get('/approval', [ClaimController::class, 'approval'])->name('approval');
            
            // View Claim
            Route::get('/{id}', [ClaimController::class, 'show'])
                ->defaults('view', 'pages.claims.claim')
                ->name('view');
            
            // Document Routes
            Route::get('/{claim}/document/{type}/{filename}', [ClaimController::class, 'viewDocument'])
                ->name('view.document');
                
            // Claims that require verified email
            Route::middleware(['verified'])->group(function () {
                Route::get('/new', [ClaimController::class, 'new'])->name('new');
                
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
                
                // Email Actions
                Route::post('/send-to-datuk/{id}', [ClaimController::class, 'sendToDatuk'])
                    ->name('mail.to.datuk');
                    
                // Resubmit Routes
                Route::get('/resubmit/{claim}', [ClaimController::class, 'showResubmitForm'])->name('resubmit');
                Route::post('/resubmit/{claim}', [ClaimController::class, 'processResubmission'])->name('resubmit.process');
                
                // Claim Review Actions
                Route::post('/{id}/update', [ClaimController::class, 'updateClaim'])->name('update');
            });
        });
    });
    
    // Reports & Settings Routes - require verification
    Route::middleware(['verified'])->group(function () {
        Route::view('/report', 'pages.reports')->name('reports');
        Route::view('/settings', 'pages.settings')->name('settings');
        
        // Routes that need role check for staff (1) and/or admin (5)
        Route::middleware(['role:1,5'])->group(function () {
            // Bulk Email Routes
            Route::prefix('claims')->name('claims.')->group(function () {
                Route::get('/bulk-email', [BulkEmailController::class, 'index'])->name('bulk-email');
                Route::post('/bulk-email/send', [BulkEmailController::class, 'sendBulkEmail'])->name('bulk-email.send');
            });
            
            // Registration request management
            Route::post('/registration-requests/{id}/approve', [RegistrationRequestController::class, 'approveFromDashboard'])
                ->name('registration-requests.approve-dashboard');
            Route::post('/registration-requests/{id}/reject', [RegistrationRequestController::class, 'rejectFromDashboard'])
                ->name('registration-requests.reject-dashboard');
        });
    });
    
    // Success Page Route
    Route::get('/claims/success', function () {
        return view('pages.claims.success');
    })
    ->name('claims.success.page');
});

// Admin only routes
Route::middleware(['admin', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Admin claims management
    Route::get('/claims', [ClaimController::class, 'adminIndex'])->name('claims');
    
    // Claims crud operations
    Route::get('/claims/{claim}/edit', [ClaimController::class, 'edit'])->name('claims.edit');
    Route::put('/claims/{claim}', [ClaimController::class, 'update'])->name('claims.update');
    Route::delete('/claims/{claim}', [ClaimController::class, 'destroy'])->name('claims.destroy');
    
    // User management
    Route::get('/users', [UserManagementController::class, 'index'])->name('users');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    
    // Changelog management
    Route::get('/changelogs', [ChangelogController::class, 'index'])->name('changelogs');
});

// Registration Approval/Rejection Routes - these require a signed URL
Route::middleware(['signed'])->group(function () {
    Route::get('/register/approve/{token}', [RegistrationRequestController::class, 'approveRequest'])->name('register.approve');
    Route::get('/register/reject/{token}', [RegistrationRequestController::class, 'rejectRequest'])->name('register.reject');
});
