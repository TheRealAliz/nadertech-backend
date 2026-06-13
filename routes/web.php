<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});





use App\Http\Controllers\Api\AuthController;

Route::middleware('guest')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::post('/auth/verify-login-otp', [AuthController::class, 'verifyLoginOtp'])
        ->middleware('throttle:10,1');

    Route::post('/auth/resend-login-otp', [AuthController::class, 'resendLoginOtp'])
        ->middleware('throttle:3,1');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
