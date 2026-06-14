<?php

use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');

Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    // Mints short-lived X-OAUTH2 (SASL) tokens — throttle even though auth'd.
    Route::get('/chat/token', [ChatController::class, 'token'])
        ->middleware('throttle:30,1')
        ->name('chat.token');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::post('/users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
    Route::get('/channels', [ChannelController::class, 'index'])->name('channels');

    // Destructive actions require a recent password confirmation (no MFA at the XMPP
    // layer, so the admin session is the keys — this is the compensating control).
    Route::middleware('password.confirm')->group(function () {
        Route::post('/users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
        Route::post('/users/{user}/kick', [AdminUserController::class, 'kick'])->name('users.kick');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset');
        Route::post('/channels', [ChannelController::class, 'store'])->name('channels.store');
        Route::delete('/channels/{channel}', [ChannelController::class, 'destroy'])->name('channels.destroy');
    });
});

require __DIR__.'/auth.php';
