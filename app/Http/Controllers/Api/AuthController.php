<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\VerifyForgotPasswordCodeRequest;
use App\Models\User;
use App\Services\LoginOtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendOTPRequest;
use App\Http\Requests\Auth\LoginWithPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOTPRequest;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use App\Models\PasswordResetCode;
use App\Services\PasswordResetService;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/auth/login',
        tags: ['Auth'],
        summary: 'Traditional login with password',
        description: 'Login using email/mobile/username + password. Returns token directly.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(property: 'login', type: 'string', example: '09123456789', description: 'Email, mobile or username'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: '12345678'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'ورود با موفقیت انجام شد.'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'access_token', type: 'string', example: '1|abc123...'),
                            new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                            new OA\Property(property: 'user', properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'full_name', type: 'string', example: 'علی احمدی'),
                                new OA\Property(property: 'username', type: 'string', example: 'ali_ahmadi'),
                                new OA\Property(property: 'email', type: 'string', example: 'ali@example.com'),
                                new OA\Property(property: 'mobile', type: 'string', example: '09123456789'),
                            ], type: 'object')
                        ], type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(property: 'errors', type: 'object', example: ['login' => ['The login field is required.']])
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'اطلاعات ورود نادرست است.')
                    ]
                )
            )
        ]
    )]
    public function loginWithPassword(LoginWithPasswordRequest $request): JsonResponse
    {
        $user = $this->findUserByLogin($request->login);

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['اطلاعات ورود نادرست است.'],
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'ورود با موفقیت انجام شد.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/auth/send-otp',
        tags: ['Auth'],
        summary: 'Request OTP for login',
        description: 'Step 1: Send OTP to user mobile',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mobile'],
                properties: [
                    new OA\Property(property: 'mobile', type: 'string', example: '09123456789'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OTP sent'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function sendOTP(SendOTPRequest $request, LoginOtpService $otpService): JsonResponse
    {
        $user = $this->findUserByLogin($request->mobile);

        $otp = $otpService->create($user, $request->ip(), $request->userAgent());

        return response()->json([
            'message' => 'کد تأیید برای شماره موبایل ارسال شد.',
            'data' => [
                'login_token' => encrypt($user->id),
                'expires_in' => $otp->getExpiresAtTimestampMs(),
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/auth/verify-otp',
        tags: ['Auth'],
        summary: 'Verify OTP and get token',
        description: 'Step 2: Verify OTP code and receive access token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login_token', 'code'],
                properties: [
                    new OA\Property(property: 'login_token', type: 'string'),
                    new OA\Property(property: 'code', type: 'string', example: '123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OTP verified, token returned'),
            new OA\Response(response: 401, description: 'Invalid or expired OTP'),
        ]
    )]
    public function verifyOTP(VerifyOTPRequest $request, LoginOtpService $otpService): JsonResponse
    {
        try {
            $userId = decrypt($request->login_token);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'توکن ورودی معتبر نیست.',
                'error_key' => 'invalid_token',
            ], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'کاربر یافت نشد.',
                'error_key' => 'user_not_found',
            ], 404);
        }

        $result = $otpService->verify($user, $request->code);

        if (!$result->success) {
            return response()->json([
                'message' => $result->errorMessage,
                'error_key' => $result->errorKey,
                'remaining_attempts' => $result->remainingAttempts,
            ], 422);
        }

        if (!$user->mobile_verified_at) {
            $user->update(['mobile_verified_at' => now()]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'ورود با موفقیت انجام شد.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ]);
    }

    private function findUserByLogin(string $login): ?User
    {
        return User::query()
            ->where('email', $login)
            ->orWhere('mobile', $login)
            ->orWhere('username', $login)
            ->first();
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'full_name' => $validated['full_name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'ثبت‌نام با موفقیت انجام شد.',
            'data' => [
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'خروج با موفقیت انجام شد.',
        ]);
    }

    #[OA\Post(
        path: '/api/auth/forgot-password',
        tags: ['Auth'],
        summary: 'Request password reset code',
        description: 'Creates a hashed password reset code and sends it to user by SMS or email.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login'],
                properties: [
                    new OA\Property(
                        property: 'login',
                        type: 'string',
                        example: 'ali@example.com',
                        description: 'Email, mobile or username'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reset code sent',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'اگر حسابی با این اطلاعات وجود داشته باشد، کد بازیابی ارسال می‌شود.'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 300),
                        new OA\Property(
                            property: 'dev_code',
                            type: 'string',
                            example: '123456',
                            nullable: true,
                            description: 'Only in local environment for testing'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(property: 'errors', type: 'object', example: ['login' => ['The login field is required.']])
                    ]
                )
            ),
        ]
    )]
    public function forgotPassword(ForgotPasswordRequest $request, PasswordResetService $service): JsonResponse
    {
        $user = $this->findUserByLogin($request->login);

        if (!$user) {
            return response()->json([
                'message' => 'اگر حسابی با این اطلاعات وجود داشته باشد، کد بازیابی ارسال می‌شود.',
                'expires_in' => 300,
            ]);
        }

        $service->create($user, $request->ip(), $request->userAgent());

        return response()->json([
            'message' => 'اگر حسابی با این اطلاعات وجود داشته باشد، کد بازیابی ارسال می‌شود.',
            'expires_in' => 300,
        ]);
    }

    #[OA\Post(
        path: '/api/auth/verify-forgot-password-code',
        tags: ['Auth'],
        summary: 'Verify password reset code',
        description: 'Verifies the password reset code and returns a temporary reset token.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'code'],
                properties: [
                    new OA\Property(
                        property: 'login',
                        type: 'string',
                        example: 'ali@example.com',
                        description: 'Email, mobile or username'
                    ),
                    new OA\Property(
                        property: 'code',
                        type: 'string',
                        example: '123456',
                        description: '6-digit verification code'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Code verified',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'کد تایید شد.'),
                        new OA\Property(property: 'reset_token', type: 'string', example: 'aB3dE5fG7hI9jK1lMn2oP3qR4sT5uV6wX7yZ8'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 300),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or expired code',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'کد وارد شده نامعتبر یا منقضی شده است.'),
                        new OA\Property(property: 'error_key', type: 'string', example: 'invalid_code'),
                        new OA\Property(property: 'remaining_attempts', type: 'integer', example: 3),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(property: 'errors', type: 'object', example: ['code' => ['The code must be 6 digits.']])
                    ]
                )
            ),
        ]
    )]
    public function verifyForgotPasswordCode(VerifyForgotPasswordCodeRequest $request, PasswordResetService $service): JsonResponse
    {
        $user = $this->findUserByLogin($request->login);

        if (!$user) {
            return response()->json([
                'message' => 'کد وارد شده نامعتبر یا منقضی شده است.',
            ], 401);
        }

        $result = $service->verify($user, $request->code);

        if (!$result->success) {
            return response()->json([
                'message' => $result->errorMessage,
                'error_key' => $result->errorKey,
                'remaining_attempts' => $result->remainingAttempts,
            ], 401);
        }

        return response()->json([
            'message' => 'کد تایید شد.',
            'reset_token' => $result->resetToken,
            'expires_in' => 300,
        ]);
    }


    #[OA\Post(
        path: '/api/auth/reset-password',
        tags: ['Auth'],
        summary: 'Reset password',
        description: 'Resets user password using the temporary reset token.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'reset_token', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(
                        property: 'login',
                        type: 'string',
                        example: 'ali@example.com',
                        description: 'Email, mobile or username'
                    ),
                    new OA\Property(
                        property: 'reset_token',
                        type: 'string',
                        example: 'aB3dE5fG7hI9jK1lMn2oP3qR4sT5uV6wX7yZ8',
                        description: 'Reset token received from verify endpoint'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'newPassword123'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        format: 'password',
                        example: 'newPassword123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'رمز عبور با موفقیت تغییر کرد.'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or expired reset token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'توکن بازیابی نامعتبر یا منقضی شده است.'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(property: 'errors', type: 'object', example: [
                            'password' => ['The password confirmation does not match.'],
                        ])
                    ]
                )
            ),
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request, PasswordResetService $service): JsonResponse
    {
        $user = $this->findUserByLogin($request->login);

        if (!$user) {
            return response()->json([
                'message' => 'توکن بازیابی نامعتبر یا منقضی شده است.',
            ], 401);
        }

        $result = $service->resetPassword($user, $request->reset_token, $request->password);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 401);
        }

        return response()->json([
            'message' => $result['message'],
        ]);
    }
}