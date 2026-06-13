<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LaporanAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/verify-otp', [AuthController::class, 'showOtp'])->name('otp.show');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('otp.resend');

    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
    Route::get('/auth/google/complete', [AuthController::class, 'showCompleteGoogle'])->name('google.complete');
    Route::post('/auth/google/complete', [AuthController::class, 'completeGoogleRegister'])->name('google.store');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetOtp'])->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('home');
    Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    Route::get('/report/{id}', [ReportController::class, 'show'])->name('report.show');
    Route::delete('/report/{id}', [ReportController::class, 'destroy'])->name('report.destroy');
    Route::get('/report/edit/{id}', [ReportController::class, 'edit'])->name('report.edit');
    Route::put('/report/{id}', [ReportController::class, 'update'])->name('report.update');
    Route::get('/laporan', [ReportController::class, 'myReports'])->name('laporan');
    Route::post('/report/{id}/toggle-status', [ReportController::class, 'toggleStatus'])->name('report.toggleStatus');

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('admin')
    ->middleware(['auth', 'isAdmin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/laporan', [LaporanAdminController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/{id}', [LaporanAdminController::class, 'show'])->name('laporan.show');
        Route::patch('/laporan/{id}/status', [LaporanAdminController::class, 'updateStatus'])->name('laporan.updateStatus');
        Route::delete('/laporan/{id}', [LaporanAdminController::class, 'destroy'])->name('laporan.destroy');

        Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
        Route::patch('/users/{id}/ban', [UserAdminController::class, 'ban'])->name('users.ban');
        Route::delete('/users/{id}', [UserAdminController::class, 'destroy'])->name('users.destroy');
    });
