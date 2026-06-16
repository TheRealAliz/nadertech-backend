<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\ProfileController;

// ========================================== Authenticate ==========================================

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');

    Route::post('/login', [AuthController::class, 'loginWithPassword'])->middleware('throttle:5,1');

    Route::post('/send-otp', [AuthController::class, 'sendOTP'])->middleware('throttle:3,1');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:2,1');
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->middleware('throttle:10,1');

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/verify-forgot-password-code', [AuthController::class, 'verifyForgotPasswordCode'])->middleware('throttle:5,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// ============================================= Profile ============================================

Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
    Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
    Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);

    Route::put('/change-password', [ProfileController::class, 'changePassword']);
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
