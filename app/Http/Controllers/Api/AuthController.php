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
use App\Http\Requests\Auth\ResendOTPRequest;
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
                            new OA\Property(
                                property: 'data',
                                type: 'array',
                                items: new OA\Items(
                                    ref: '#/components/schemas/UserResource'
                                )
                            ),
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
        description: 'Step 1: Send OTP to user mobile. If OTP already sent and active, returns existing one.',
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
            new OA\Response(
                response: 200,
                description: 'OTP sent or already exists - Two possible messages: 
        1. "کد تأیید برای شماره موبایل ارسال شد." (new OTP sent)
        2. "کد تأیید قبلاً برای این شماره ارسال شده است." (OTP already exists and still valid)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            description: 'Persian message indicating OTP status',
                            example: 'کد تأیید برای شماره موبایل ارسال شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'login_token',
                                    type: 'string',
                                    description: 'Encrypted user ID for OTP verification',
                                    example: 'eyJpdiI6...'
                                ),
                                new OA\Property(
                                    property: 'expires_in',
                                    type: 'integer',
                                    description: 'OTP expiration time in seconds',
                                    example: 180
                                ),
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'کاربر یافت نشد.'),
                        new OA\Property(property: 'error_key', type: 'string', example: 'user_not_found'),
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
                            'mobile' => ['شماره موبایل باید ۱۱ رقم باشد.']
                        ])
                    ]
                )
            ),
        ]
    )]
    public function sendOTP(SendOTPRequest $request, LoginOtpService $otpService): JsonResponse
    {
        $user = $this->findUserByLogin($request->mobile);

        $result = $otpService->create($user, $request->ip(), $request->userAgent());

        $message = $result['already_sent']
            ? 'کد تأیید قبلاً برای این شماره ارسال شده است.'
            : 'کد تأیید برای شماره موبایل ارسال شد.';

        return response()->json([
            'message' => $message,
            'data' => [
                'login_token' => encrypt($user->id),
                'expires_in' => $result['otp']->getExpiresIn(),
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/auth/resend-otp',
        tags: ['Auth'],
        summary: 'Resend OTP code',
        description: 'Resends a new OTP code to user mobile. 
        **Note:** This endpoint only works when there is no active (non-expired) OTP code. 
        If an active OTP code exists, the request will be rejected with error 429.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login_token'],
                properties: [
                    new OA\Property(
                        property: 'login_token',
                        type: 'string',
                        example: 'eyJpdiI6Ik5UWTJZ...',
                        description: 'Encrypted user ID received from register or send-otp endpoint'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'New OTP sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'کد تأیید جدید با موفقیت ارسال شد.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'login_token', type: 'string', example: 'eyJpdiI6Ik5UWTJZ...'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 180, description: 'New OTP expiration time in seconds'),
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid token format',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'توکن ورودی معتبر نیست.'),
                        new OA\Property(property: 'error_key', type: 'string', example: 'invalid_token'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'کاربر یافت نشد.'),
                        new OA\Property(property: 'error_key', type: 'string', example: 'user_not_found'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'login_token' => ['The login token field is required.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 429,
                description: 'Active OTP code exists - Cannot resend',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'کد تأیید فعال وجود دارد. لطفاً منتظر بمانید تا منقضی شود.'),
                        new OA\Property(property: 'error_key', type: 'string', example: 'active_otp_exists'),
                    ]
                )
            ),
        ]
    )]
    public function resendOtp(ResendOTPRequest $request, LoginOtpService $otpService): JsonResponse
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

        try {
            $result = $otpService->resend($user, $request->ip(), $request->userAgent());
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_key' => 'active_otp_exists',
            ], 429);
        }

        return response()->json([
            'message' => 'کد تأیید جدید برای شماره موبایل ارسال شد.',
            'data' => [
                'login_token' => encrypt($user->id),
                'expires_in' => $result['otp']->getExpiresIn(),
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

    #[OA\Post(
        path: '/api/auth/register',
        tags: ['Auth'],
        summary: 'Register a new user and send OTP',
        description: 'Creates a new user account, sends OTP to mobile, and returns login_token for verification',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'username', 'email', 'mobile', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', maxLength: 255, example: 'علی احمدی'),
                    new OA\Property(property: 'username', type: 'string', minLength: 3, maxLength: 50, example: 'ali_ahmadi'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'ali@example.com'),
                    new OA\Property(property: 'mobile', type: 'string', example: '09123456789'),
                    new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true, example: '2000-05-15'),
                    new OA\Property(property: 'national_code', type: 'string', nullable: true, example: '1234567890'),
                    new OA\Property(property: 'postal_code', type: 'string', nullable: true, example: '1234567890'),
                    new OA\Property(property: 'province', type: 'string', nullable: true, example: 'خراسان شمالی'),
                    new OA\Property(property: 'address', type: 'string', nullable: true, example: 'بجنورد، خیابان امام، پلاک ۱۲'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: '12345678'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: '12345678'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully, OTP sent',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'ثبت‌نام با موفقیت انجام شد. کد تأیید به شماره موبایل ارسال شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'login_token', type: 'string'),
                                new OA\Property(property: 'expires_in', type: 'integer', example: 120),
                                new OA\Property(
                                    property: 'user',
                                    ref: '#/components/schemas/UserResource'
                                ),
                            ]
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
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'email' => ['The email has already been taken.'],
                                'mobile' => ['The mobile has already been taken.']
                            ]
                        )
                    ]
                )
            ),
        ]
    )]
    public function register(RegisterRequest $request, LoginOtpService $otpService): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'full_name' => $validated['full_name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'birth_date' => $validated['birth_date'] ?? null,
            'national_code' => $validated['national_code'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'province' => $validated['province'] ?? null,
            'address' => $validated['address'] ?? null,
            'password' => Hash::make($validated['password']),
            'mobile_verified_at' => null,
        ]);

        $otp = $otpService->create($user, $request->ip(), $request->userAgent())['otp'];

        return response()->json([
            'message' => 'ثبت‌نام با موفقیت انجام شد. کد تأیید به شماره موبایل ارسال شد.',
            'data' => [
                'login_token' => encrypt($user->id),
                'expires_in' => $otp->getExpiresIn(),
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    #[OA\Get(
        path: '/api/auth/me',
        tags: ['Auth'],
        summary: 'Get authenticated user profile',
        description: 'Returns the currently authenticated user information',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/UserResource'),
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        tags: ['Auth'],
        summary: 'Logout user',
        description: 'Revokes the current access token',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'خروج با موفقیت انجام شد.'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
        ]
    )]
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

        $resetCode = $service->create($user, $request->ip(), $request->userAgent());

        return response()->json([
            'message' => 'اگر حسابی با این اطلاعات وجود داشته باشد، کد بازیابی ارسال می‌شود.',
            'expires_in' => $resetCode->getExpiresIn(),
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