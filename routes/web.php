<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});





use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResumeController;
use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Faq;
use App\Models\ProjectRequest;
use App\Models\ProjectRequestType;
use App\Models\Resume;
use Illuminate\Support\Facades\Artisan;

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

Route::get('api/migrate/status', function () {
    Artisan::call('migrate:status');

    dd(Artisan::output());
});

Route::get('api/migrate', function () {
    Artisan::call('migrate', ['--force' => true]);

    dd(Artisan::output());
});

Route::get('api/migrate/fresh', function () {
    Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);

    dd(Artisan::output());
});

Route::get('api/test2/faq', function () {
    dd(ArticleView::all());
});

Route::get('api/cache/optimize', function () {
    Artisan::call('optimize');

    dd(Artisan::output());
});

Route::get('api/migrate/rollback', function () {
    Artisan::call('migrate:rollback', ['--force' => true]);

    dd(Artisan::output());
});

Route::Get('api/seed/{seeder}', function ($seeder) {
    Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);

    dd(Artisan::output());
});

Route::get('api/cache', function () {
    Artisan::call('optimize:clear');

    dd(Artisan::output());
});

Route::get('api/type', function () {
    ProjectRequestType::create(
        [
            'title' => 'مشاوره رایگان',
            'title_en' => 'Free Consultation'
        ]
    );
});
