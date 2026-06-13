<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LotteryController;

Route::middleware('guest')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::post('/auth/verify-login-otp', [AuthController::class, 'verifyLoginOtp'])
        ->middleware('throttle:10,1');

    Route::post('/auth/resend-login-otp', [AuthController::class, 'resendLoginOtp'])
        ->middleware('throttle:3,1');

    Route::post('/auth/register', [AuthController::class, 'register'])
        ->middleware('throttle:3,1');


    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/verify-forgot-password-code', [AuthController::class, 'verifyForgotPasswordCode']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});








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
