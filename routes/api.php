<?php

use App\Http\Controllers\Api\Admin\AdminRoleController;
use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Api\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Api\Admin\LotteryController as AdminLotteryController;
use App\Http\Controllers\Api\ProjectServiceController;
use App\Http\Controllers\Api\PageItemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Admin\ProjectRequestController as AdminProjectRequestController;
use App\Http\Controllers\Api\Admin\ResumeController as AdminResumeController;
use App\Http\Controllers\Api\Admin\RolePermissionController;
use App\Http\Controllers\Api\ProjectRequestController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\BannerController;

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

// ======================================== Project Services ========================================

Route::prefix('services')->group(function () {
    Route::get('/', [ProjectServiceController::class, 'index']);
    Route::get('/tree', [ProjectServiceController::class, 'tree']);
    Route::get('/{service:slug}', [ProjectServiceController::class, 'show']);
});

// ======================================== Project Requests ========================================

Route::prefix('requests')->group(function () {
    Route::get('/store', [ProjectRequestController::class, 'store']);
});

// ============================================ Lotteries ===========================================

Route::prefix('lotteris')->group(function () {
    Route::get('/', [LotteryController::class, 'index']);
    Route::get('/{lottery}', [LotteryController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/{lottery}/register', [LotteryController::class, 'register']);
        Route::get('/{lottery}/my-status', [LotteryController::class, 'myStatus']);
        Route::get('/me', [LotteryController::class, 'myLotteries']);
    });
});

// ======================================== Home Page Banners =========================================

Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
});

// ============================================= Articles =============================================

Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
});

// ============================================ Page Items ============================================

Route::prefix('page')->group(function () {
    Route::get('/{page}', [PageItemController::class, 'index']);
    Route::get('/{page}/{key}', [PageItemController::class, 'show']);
});

// =============================================== FAQs ===============================================

Route::prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index']);
    Route::get('/{faq}', [FaqController::class, 'show']);
});

// ============================================== Admin ===============================================

Route::middleware(['auth:admin'])->prefix('admin')->group(function () {

    // ========================================= Authenticate =========================================

    Route::withoutMiddleware('auth:admin')->prefix('/auth')->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    // ============================================= Users ============================================

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:admin.users.view');

        Route::get('/{user}', [UserController::class, 'show'])
            ->middleware('permission:admin.users.view.single');
    });

    // ============================================ Admins ============================================

    Route::prefix('admins/{admin}/roles')->group(function () {
        Route::get('/', [AdminRoleController::class, 'show'])
            ->middleware('permission:admin.admins.roles.view');

        Route::put('/', [AdminRoleController::class, 'sync'])
            ->middleware('permission:admin.admins.roles.update');
    });

    Route::prefix('roles/{role}/permissions')->group(function () {
        Route::get('/', [RolePermissionController::class, 'show'])
            ->middleware('permission:admin.admins.roles.permissions.view');

        Route::put('/', [RolePermissionController::class, 'sync'])
            ->middleware('permission:admin.admins.roles.permissions.update');
    });

    // ======================================= Home Page Banners ======================================

    Route::prefix('banners')->group(function () {
        Route::get('/', [AdminBannerController::class, 'index'])
            ->middleware('permission:admin.banners.view');

        Route::post('/', [AdminBannerController::class, 'store'])
            ->middleware('permission:admin.banners.create');

        Route::get('/{banner}', [AdminBannerController::class, 'show'])
            ->middleware('permission:admin.banners.view');

        Route::put('/{banner}', [AdminBannerController::class, 'update'])
            ->middleware('permission:admin.banners.update');

        Route::delete('/{banner}', [AdminBannerController::class, 'destroy'])
            ->middleware('permission:admin.banners.delete');

        Route::put('/{banner}/status', [AdminBannerController::class, 'updateStatus'])
            ->middleware('permission:admin.banners.update.status');

        Route::put('/{banner}/image', [AdminBannerController::class, 'updateImage'])
            ->middleware('permission:admin.banners.update.image');

        Route::put('/reorder', [AdminBannerController::class, 'reorder'])
            ->middleware('permission:admin.banners.reorder');
    });

    // =========================================== Articles ===========================================

    Route::prefix('articles')->group(function () {
        Route::get('/', [AdminArticleController::class, 'index'])
            ->middleware('permission:admin.articles.view');

        Route::post('/', [AdminArticleController::class, 'store'])
            ->middleware('permission:admin.articles.create');

        Route::get('/{article}', [AdminArticleController::class, 'show'])
            ->middleware('permission:admin.articles.view');

        Route::put('/{article}', [AdminArticleController::class, 'update'])
            ->middleware('permission:admin.articles.update');

        Route::delete('/{article}', [AdminArticleController::class, 'destroy'])
            ->middleware('permission:admin.articles.delete');

        Route::get('/archived', [AdminArticleController::class, 'archived'])
            ->middleware('permission:admin.articles.archived.view');

        Route::put('/{article}/status', [AdminArticleController::class, 'updateStatus'])
            ->middleware('permission:admin.articles.update.status');

        Route::put('/{article}/thumbnail', [AdminArticleController::class, 'updateThumbnail'])
            ->middleware('permission:admin.articles.update.thumbnail');
    });

    // ======================================= Project Requests =======================================

    Route::prefix('requests')->group(function () {
        Route::get('/', [AdminProjectRequestController::class, 'index'])
            ->middleware('permission:admin.requests.view');

        Route::get('/{request}', [AdminProjectRequestController::class, 'show'])
            ->middleware('permission:admin.requests.view');
    });

    // ============================================ Resumes ===========================================

    Route::prefix('resume')->group(function () {
        Route::get('/', [AdminResumeController::class, 'index'])
            ->middleware('permission:admin.resumes.view');

        Route::post('/', [AdminResumeController::class, 'store'])
            ->middleware('permission:admin.resumes.create');

        Route::get('/{resume}', [AdminResumeController::class, 'show'])
            ->middleware('permission:admin.resumes.view');

        Route::put('/{resume}', [AdminResumeController::class, 'update'])
            ->middleware('permission:admin.resumes.update');

        Route::delete('/{resume}', [AdminResumeController::class, 'destroy'])
            ->middleware('permission:admin.resumes.delete');

        Route::patch('/{resume}/status', [AdminResumeController::class, 'updateStatus'])
            ->middleware('permission:admin.resumes.update');
    });

    // =========================================== Lotteries ==========================================

    Route::prefix('lotteries')->group(function () {
        Route::get('/', [AdminLotteryController::class, 'index'])
            ->middleware('permission:admin.lotteries.view');

        Route::get('/{lottery}', [AdminLotteryController::class, 'show'])
            ->middleware('permission:admin.lotteries.view');

        Route::post('/', [AdminLotteryController::class, 'store'])
            ->middleware('permission:admin.lotteries.create');

        Route::put('/{lottery}', [AdminLotteryController::class, 'update'])
            ->middleware('permission:admin.lotteries.update');

        Route::post('/{lottery}/draw', [AdminLotteryController::class, 'draw'])
            ->middleware('permission:admin.lotteries.draw');

        Route::get('/{lottery}/entries', [AdminLotteryController::class, 'entries'])
            ->middleware('permission:admin.lotteries.enteries.view');

        Route::get('/{lottery}/winners', [AdminLotteryController::class, 'winners'])
            ->middleware('permission:admin.winners.view');
    });

    // ============================================= FAQs =============================================

    Route::prefix('faqs')->group(function () {
        Route::get('/', [AdminFaqController::class, 'index'])
            ->middleware('permission:admin.faqs.view');

        Route::get('/{faq}', [AdminFaqController::class, 'show'])
            ->middleware('permission:admin.faqs.view');

        Route::post('/', [AdminFaqController::class, 'store'])
            ->middleware('permission:admin.faqs.create');

        Route::put('/{faq}', [AdminFaqController::class, 'update'])
            ->middleware('permission:admin.faqs.update');
    });
});