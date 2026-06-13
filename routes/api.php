<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LotteryController;

// ========================================== Authenticate ==========================================

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'loginWithPassword']);

    Route::post('/send-otp', [AuthController::class, 'sendOTP']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-forgot-password-code', [AuthController::class, 'verifyForgotPasswordCode']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/resend-otp', [AuthController::class, 'resendLoginOtp']);
    });
});

// ============================================ Lotteries ===========================================

Route::get('/lotteries', [LotteryController::class, 'index']);
Route::get('/lotteries/{lottery}', [LotteryController::class, 'show']);

Route::middleware('api.token')->group(function () {
    Route::post('/lotteries/{lottery}/register', [LotteryController::class, 'register']);
    Route::get('/lotteries/{lottery}/my-status', [LotteryController::class, 'myStatus']);
    Route::get('/my/lotteries', [LotteryController::class, 'myLotteries']);
});

Route::prefix('admin')->middleware('api.token')->group(function () {
    Route::post('/lotteries', [LotteryController::class, 'store']);
    Route::put('/lotteries/{lottery}', [LotteryController::class, 'update']);
    Route::get('/lotteries/{lottery}/entries', [LotteryController::class, 'entries']);
    Route::post('/lotteries/{lottery}/draw', [LotteryController::class, 'draw']);
    Route::get('/lotteries/{lottery}/winners', [LotteryController::class, 'winners']);
});
