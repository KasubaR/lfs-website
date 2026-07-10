<?php

use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\MembershipApplicationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/create-account', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/create-account', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/password/change', [ChangePasswordController::class, 'create'])
        ->middleware('verified')
        ->name('password.change');

    Route::post('/password/change', [ChangePasswordController::class, 'store'])
        ->middleware('verified')
        ->name('password.change.store');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/membership/apply', [MembershipApplicationController::class, 'create'])->name('membership.apply');
    Route::post('/membership/apply', [MembershipApplicationController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('membership.apply.store');
});

Route::middleware(['auth', 'member', 'verified', 'force.password.change'])->group(function (): void {
    Route::get('/account', [AccountController::class, 'show'])->name('account');
});
