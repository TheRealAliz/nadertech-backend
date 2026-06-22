<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginWithPasswordRequest;
use App\Http\Resources\Admin\Auth\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/admin/login',
        tags: ['Admin - Auth'],
        summary: 'Admin login',
        description: 'Login admin panel using username and password. Returns access token for admin API access.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login', 'password'],
                properties: [
                    new OA\Property(
                        property: 'login',
                        type: 'string',
                        example: 'admin',
                        description: 'Admin username'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: '12345678',
                        description: 'Admin password (min 8 characters)'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'ورود با موفقیت انجام شد.'
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'access_token',
                                    type: 'string',
                                    example: '1|abc123def456ghi789...',
                                    description: 'Bearer token for admin API access'
                                ),
                                new OA\Property(
                                    property: 'token_type',
                                    type: 'string',
                                    example: 'Bearer'
                                ),
                                new OA\Property(
                                    property: 'admin',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'username', type: 'string', example: 'admin'),
                                        new OA\Property(property: 'email', type: 'string', nullable: true, example: 'admin@example.com'),
                                        new OA\Property(property: 'full_name', type: 'string', example: 'مدیر سیستم'),
                                        new OA\Property(property: 'is_super_admin', type: 'boolean', example: true),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                        new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                    ],
                                    type: 'object'
                                )
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'اطلاعات ورود نادرست است.'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'login' => ['اطلاعات ورود نادرست است.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'The given data was invalid.'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'login' => ['The login field is required.'],
                                'password' => ['The password field is required.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 429,
                description: 'Too many attempts',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Too many login attempts. Please try again later.'
                        )
                    ]
                )
            )
        ]
    )]
    public function login(LoginWithPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $admin = Admin::where('username', $validated['login'])->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'login' => ['اطلاعات ورود نادرست است.'],
            ]);
        }

        $token = $admin->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'ورود با موفقیت انجام شد.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'admin' => new AdminResource($admin),
            ],
        ]);
    }
}