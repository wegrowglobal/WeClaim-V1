<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Claims\ClaimController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RequestAccountController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Http\Request;
use App\Http\Controllers\FileController;
use App\Http\Controllers\System\SystemConfigController;
use App\Http\Controllers\Admin\BulkEmailController;
use App\Http\Controllers\Signature\SignatureController;
use App\Http\Controllers\MiddlewareTestController;
use App\Http\Controllers\User\UserSecurityController;


// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login.login');
    })->name('login');

    Route::post('login', [UserController::class, 'login'])->name('login');

    // Password Reset Routes
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::get('forgot-password-confirmation', [ForgotPasswordController::class, 'showConfirmation'])
        ->name('password.confirmation');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
    Route::view('password-reset-success', 'auth.password.password-reset-success')
        ->name('password.reset.success');
        
    // Registration Routes
    Route::get('request', [RequestAccountController::class, 'showRegistrationForm'])->name('request.form');
    Route::post('request', [RequestAccountController::class, 'store'])->name('request.store');
    
    // Password Setup Routes
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/set/{token}', [RequestAccountController::class, 'showSetPasswordForm'])->name('set');
        Route::post('/save', [RequestAccountController::class, 'savePassword'])->name('save');
        Route::get('/setup/{token}', [RequestAccountController::class, 'showSetPasswordForm'])->name('setup.form');
        Route::post('/setup/{token}', [RequestAccountController::class, 'setPassword'])->name('setup');
        Route::get('/setup-success', [RequestAccountController::class, 'showPasswordSetupSuccess'])->name('setup.success');
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
Route::middleware('auth', 'track.activity')->group(function () {
    // Home Route
    Route::get('/', [ClaimController::class, 'home'])->name('home');
    
    // Logout Route
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    
    // Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserProfileController::class, 'show'])->name('show');
        Route::put('/', [UserProfileController::class, 'update'])->name('update');
        Route::post('/signature', [SignatureController::class, 'store'])->name('signature.store');
        Route::delete('/signature', [SignatureController::class, 'destroy'])->name('signature.destroy');
    });
    
    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    });
    
    // Routes that require completed profile
    Route::middleware('profile.complete')->group(function () {
        // Claims Routes
        Route::prefix('claims')->name('claims.')->group(function () {
            Route::get('/dashboard', [ClaimController::class, 'dashboard'])->name('dashboard');
            Route::get('/new', [ClaimController::class, 'new'])->name('new');
            Route::post('/store', [ClaimController::class, 'store'])->name('store');
            Route::get('/approval', [ClaimController::class, 'approval'])->name('approval');
            
            // Form Steps Routes
            Route::prefix('steps')->name('steps.')->group(function () {
                Route::post('/save', [ClaimController::class, 'saveStep'])->name('save');
                Route::post('/reset', [ClaimController::class, 'resetSession'])->name('reset');
                Route::get('/{step}', [ClaimController::class, 'getStep'])->name('get');
                Route::get('/{step}/progress', [ClaimController::class, 'getProgressSteps'])->name('progress');
            });
            
            // Individual Claim Routes
            Route::prefix('{claim}')->group(function () {
                Route::get('/', [ClaimController::class, 'show'])->name('show');
                Route::get('/review', [ClaimController::class, 'reviewClaim'])->name('review');
                Route::post('/update', [ClaimController::class, 'updateClaim'])->name('update');
                Route::post('/export', [ClaimController::class, 'export'])->name('export');
                Route::put('/cancel', [ClaimController::class, 'cancelClaim'])->name('cancel');
                Route::get('/resubmit', [ClaimController::class, 'showResubmitForm'])->name('resubmit');
                Route::post('/resubmit', [ClaimController::class, 'processResubmission'])->name('resubmit.process');
                Route::get('/document/{type}/{filename}', [ClaimController::class, 'viewDocument'])->name('document');
            });
        });
    });
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Claims Management
    Route::prefix('claims')->name('claims.')->group(function () {
        Route::get('/', [ClaimController::class, 'adminIndex'])->name('index');
        Route::get('/{claim}/edit', [ClaimController::class, 'edit'])->name('edit');
        Route::put('/{claim}', [ClaimController::class, 'update'])->name('update');
        Route::delete('/{claim}', [ClaimController::class, 'destroy'])->name('delete');
        Route::get('/bulk-email', [BulkEmailController::class, 'index'])->name('bulk-email');
        Route::post('/bulk-email/send', [BulkEmailController::class, 'sendBulkEmail'])->name('bulk-email.send');
    });
    
    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::put('/{id}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('delete');
    });
    
    // System Management
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/failed-logins', [UserSecurityController::class, 'failedLogins'])->name('failed-logins');
        Route::get('/config', [SystemConfigController::class, 'index'])->name('config');
        Route::post('/config', [SystemConfigController::class, 'update'])->name('config.update');
    });
});

// Registration Approval/Rejection Routes - these require a signed URL
Route::middleware('signed')->group(function () {
    Route::get('/register/approve/{token}', [RequestAccountController::class, 'approveRequest'])->name('register.approve');
    Route::get('/register/reject/{token}', [RequestAccountController::class, 'rejectRequest'])->name('register.reject');
});

// Fallback route for unmatched GET requests (optional)
Route::fallback(function () {
    // return view('errors.404'); // Or redirect to a specific page like dashboard or home
    return redirect('/');
});
