<?php

///////////////////////////////////////////////////////////////////////////////////

use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

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

Route::get('/forgot-password', function () {
    return view('pages.auth.forgot-password');
})->name('forgot-password');

///////////////////////////////////////////////////////////////////////////////////

// Middleware Routes

Route::middleware('auth')->group(function () {

    ///////////////////////////////////////////////////////////////////////////////////

    // Notification

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
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
    ->name('claims.claim');

    Route::put('/claims/{id}', [ClaimController::class, 'updateClaim'])->name('claims.update');

    Route::post('/claims/{id}/approve', [ClaimController::class, 'approveClaim'])->name('claims.approve');

///////////////////////////C;////////////////////////////////////////////////////////

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
