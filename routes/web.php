<?php

use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// All Static Routes

Route::get('/', function () {
    return view('posts.home');
})->name('home');

// Authentication Routes

Route::get('/login', function () {
    return view('authentication.login');
})->name('login.form');

Route::post('/login', [App\Http\Controllers\UserController::class, 'login'])->name('login');

Route::post('/logout', [App\Http\Controllers\UserController::class, 'logout'])->name('logout');

Route::get('/forgot-password', function () {
    return view('authentication.forgot_password');
})->name('forgot-password');

// Middleware Routes

Route::middleware('auth')->group(function () {

    // Notification

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');


    // User Profile Routes

    Route::get('/profile', [App\Http\Controllers\UserProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');

    // Claims Routes

    Route::get('claims', [App\Http\Controllers\ClaimController::class, 'index'])
    ->defaults('view', 'claims.dashboard')
    ->name('claims.dashboard');

    Route::get('claims/new', function () {
        return view('claims.new');
    })->name('claims.new');

    Route::post('claims/new', [App\Http\Controllers\ClaimController::class, 'store'])->name('claims.store');

    Route::get('claims/approval', [App\Http\Controllers\ClaimController::class, 'index'])
    ->defaults('view', 'claims.approval')
    ->name('claims.approval');

    Route::get('claims/{claim}/document/{type}/{filename}', [ClaimController::class, 'viewDocument'])
        ->name('claims.view.document');

    Route::get('/claims/{id}/review', [App\Http\Controllers\ClaimController::class, 'reviewClaim'])->name('claims.review');

    Route::get('claims/{id}', [App\Http\Controllers\ClaimController::class, 'show'])
    ->defaults('view', 'claims.claim')
    ->name('claims.claim');

    Route::put('/claims/{id}', [ClaimController::class, 'updateClaim'])->name('claims.update');

    Route::post('/claims/{id}/approve', [App\Http\Controllers\ClaimController::class, 'approveClaim'])->name('claims.approve');

    // Reports Routes

    Route::get('/report', function () {
    return view();
    })->name('reports');

    // Settings Routes

    Route::get('/settin//////////////////gs', function () {
        return view();
    })->name('settings');

    Route::get('/chat', [ChatController::class, 'index'])->name('posts.chat');
    Route::post('/chat/message', [ChatController::class, 'sendMessage'])->name('chat.message');

});
