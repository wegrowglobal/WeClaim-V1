<?php

///////////////////////////////////////////////////////////////////////////////////

use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ForgotPasswordController;

///////////////////////////////////////////////////////////////////////////////////

// All Static Routes

Route::get('/', function () {
    return view('pages.home');
})->name('home');

///////////////////////////////////////////////////////////////////////////////////

// Authentication Routes

Route::get('/login', function () {
    return view('pages.auth.login');
})->name('login.form');

Route::post('/login', [UserController::class, 'login'])->name('login');

Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->middleware('guest')
    ->name('password.request');

Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');

Route::get('forgot-password/confirmation', [ForgotPasswordController::class, 'showConfirmation'])
    ->middleware('guest')
    ->name('password.confirmation');

Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('reset-password', [ResetPasswordController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

///////////////////////////////////////////////////////////////////////////////////

// Middleware Routes

Route::middleware('auth')->group(function () {

    ///////////////////////////////////////////////////////////////////////////////////

    // Notification

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');

    ///////////////////////////////////////////////////////////////////////////////////

    // User Profile Routes

    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');

    ///////////////////////////////////////////////////////////////////////////////////

    // Claims Routes

    Route::get('claims/dashboard', [ClaimController::class, 'dashboard'])->name('claims.dashboard');

    Route::get('claims/new', function () {
        return view('pages.claims.new');
    })->name('claims.new');

    Route::post('claims/new', [ClaimController::class, 'store'])->name('claims.store');

    Route::get('claims/approval', [ClaimController::class, 'approval'])->name('claims.approval');

    Route::get('claims/{claim}/document/{type}/{filename}', [ClaimController::class, 'viewDocument'])
    ->name('claims.view.document');

    Route::get('/claims/{id}/review', [ClaimController::class, 'reviewClaim'])->name('claims.review');

    Route::get('claims/{id}', [ClaimController::class, 'show'])
    ->defaults('view', 'pages.claims.claim')
    ->name('claims.view');

    Route::post('/claims/{id}', [ClaimController::class, 'updateClaim'])->name('claims.update');

    // Route to send claim to Datuk
    Route::post('/claims/send-to-datuk/{id}', [ClaimController::class, 'sendToDatuk'])
        ->name('claims.mail.to.datuk')
        ->middleware('auth'); // Ensure the user is authenticated

    Route::get('/claims/email-action/{id}', [ClaimController::class, 'handleEmailAction'])
        ->name('claims.email.action')
        ->middleware('auth'); // Ensure the user is authenticated

/////////////////////////////////////////////////////////////////////////////////////

    // Reports Routes   

    Route::get('/report', function () {
    return view();
    })->name('reports');

    ///////////////////////////////////////////////////////////////////////////////////

    // Settings Routes  

    Route::get('/settings', function () {
        return view();
    })->name('settings');

    ///////////////////////////////////////////////////////////////////////////////////
    

});

Route::get('/claims/success', function () {
    return view('pages.claims.success');
})->name('claims.success.page');