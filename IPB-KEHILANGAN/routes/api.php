<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ReportApiController;

// ==========================
// TEST API
// ==========================
Route::get('/test', function () {
    return response()->json([
        'message' => 'API jalan 🚀'
    ]);
});

// ==========================
// AUTH (TANPA LOGIN)
// ==========================
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/verify-otp', [AuthApiController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthApiController::class, 'resendOtp']);
Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthApiController::class, 'resetPassword']);


// ==========================
// PUBLIC ROUTES (UNTUK WEB & SEARCH)
// ==========================
Route::get('/reports', [ReportApiController::class, 'index']);
Route::get('/reports/{id}', [ReportApiController::class, 'show']);


// ==========================
// PROTECTED ROUTES (PAKAI TOKEN)
// ==========================
Route::middleware('api.auth')->group(function () {

    Route::get('/me', [AuthApiController::class, 'me']);
    Route::post('/me', [AuthApiController::class, 'updateProfile']);
    Route::get('/my-reports', [ReportApiController::class, 'mine']);

    Route::post('/reports', [ReportApiController::class, 'store']);
    Route::put('/reports/{id}', [ReportApiController::class, 'update']);
    Route::delete('/reports/{id}', [ReportApiController::class, 'destroy']);

});
