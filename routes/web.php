<?php

use App\Http\Controllers\ClaimController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// All Static Routes

Route::get('/', function () {
    return view('posts.home');
})->name('home');


// All Authentication Routes

Route::get('/login', function () {
    return view('authentication.login');
})->name('login');

Route::post('/login', [App\Http\Controllers\UserController::class, 'login'])->name('login');

Route::post('/logout', [App\Http\Controllers\UserController::class, 'logout'])->name('logout');

Route::get('/forgot-password', function () {
    return view('authentication.forgot_password');
})->name('forgot-password');

// Other Routes

Route::get('/report', function () {
    return view();
})->name('reports');

Route::get('/profile', function () {
    return view();
})->name('profile');

Route::get('/notifications', function () {
    return view();
})->name('notifications');

Route::get('/settings', function () {
    return view();
})->name('settings');

// All Claims Routes

Route::put('/claims/{id}', [ClaimController::class, 'updateClaim'])->name('claims.update');


Route::get('claims', function () {
    return app('App\Http\Controllers\ClaimController')->index('claims.dashboard');
})->name('claims.dashboard');

Route::get('claims/new', function () {
    return view('claims.new');
})->name('claims.new');

Route::post('claims/new', [ClaimController::class, 'store'])->name('claims.new');

Route::get('/claims/approval', [App\Http\Controllers\ClaimController::class, 'approvalScreen'])->name('claims.approval');

Route::get('claims/{id}', function ($id) {
    return app('App\Http\Controllers\ClaimController')->show($id, 'claims.claim');
})->name('claims.claim');

Route::get('/claims/{id}/review', [App\Http\Controllers\ClaimController::class, 'reviewClaim'])->name('claims.review');

Route::post('/claims/{id}/approve', [App\Http\Controllers\ClaimController::class, 'approveClaim'])->name('claims.approve');

// All Review Routes
Route::get('claims/{claim}/document/{type}/{filename}', [ClaimController::class, 'viewDocument'])
    ->name('claims.view.document');
