<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    #[OA\Get(
        path: '/api/profile',
        tags: ['Profile'],
        summary: 'Get user profile',
        description: 'Returns the authenticated user profile information',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
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
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }

    #[OA\Put(
        path: '/api/profile',
        tags: ['Profile'],
        summary: 'Update user profile',
        description: 'Updates the authenticated user profile information',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', maxLength: 255, example: 'علی احمدی'),
                    new OA\Property(property: 'username', type: 'string', minLength: 3, maxLength: 50, example: 'ali_ahmadi'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ali@example.com'),
                    new OA\Property(property: 'mobile', type: 'string', example: '09123456789'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'اطلاعات با موفقیت به‌روزرسانی شد.'),
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
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            ),
        ]
    )]
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'message' => 'اطلاعات با موفقیت به‌روزرسانی شد.',
            'data' => [
                'user' => new UserResource($user->fresh()),
            ],
        ]);
    }

    #[OA\Post(
        path: '/api/profile/avatar',
        tags: ['Profile'],
        summary: 'Upload user avatar',
        description: 'Uploads a new profile picture for the authenticated user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['avatar'],
                    properties: [
                        new OA\Property(
                            property: 'avatar',
                            type: 'string',
                            format: 'binary',
                            description: 'Image file (jpeg, png, jpg, gif, webp, max 2MB)'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avatar uploaded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'عکس پروفایل با موفقیت آپدیت شد.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'avatar_url', type: 'string', example: '/storage/avatars/avatar_5_1734567890.webp'),
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
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(property: 'errors', type: 'object', example: [
                            'avatar' => ['The avatar must be an image.']
                        ])
                    ]
                )
            ),
        ]
    )]
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $user = $request->user();

        if ($user->avatar) {
            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $fullPath = storage_path('app/public/' . $user->avatar);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return response()->json([
            'message' => 'عکس پروفایل با موفقیت آپدیت شد.',
            'data' => ['avatar_url' => Storage::url($path)],
        ]);
    }

    #[OA\Delete(
        path: '/api/profile/avatar',
        tags: ['Profile'],
        summary: 'Delete user avatar',
        description: 'Deletes the profile picture of the authenticated user',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avatar deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'عکس پروفایل با موفقیت حذف شد.'),
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
            new OA\Response(
                response: 404,
                description: 'Avatar not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'عکس پروفایل یافت نشد.')
                    ]
                )
            ),
        ]
    )]
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $fullPath = storage_path('app/public/' . $user->avatar);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }

            $user->update(['avatar' => null]);
        }

        return response()->json([
            'message' => 'عکس پروفایل با موفقیت حذف شد.',
        ]);
    }

    #[OA\Put(
        path: '/api/profile/change-password',
        tags: ['Profile'],
        summary: 'Change user password',
        description: 'Changes the authenticated user password after verifying current password',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'new_password', 'new_password_confirmation'],
                properties: [
                    new OA\Property(
                        property: 'current_password',
                        type: 'string',
                        format: 'password',
                        example: '12345678',
                        description: 'Current password'
                    ),
                    new OA\Property(
                        property: 'new_password',
                        type: 'string',
                        format: 'password',
                        minLength: 8,
                        example: '87654321',
                        description: 'New password (min 8 characters)'
                    ),
                    new OA\Property(
                        property: 'new_password_confirmation',
                        type: 'string',
                        format: 'password',
                        example: '87654321',
                        description: 'Confirm new password'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password changed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'رمز عبور با موفقیت تغییر کرد.'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Current password is incorrect',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'رمز عبور فعلی نادرست است.'),
                        new OA\Property(property: 'errors', type: 'object', example: [
                            'current_password' => ['رمز عبور فعلی نادرست است.']
                        ])
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
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(property: 'errors', type: 'object', example: [
                            'new_password' => ['The new password must be at least 8 characters.'],
                            'new_password_confirmation' => ['The new password confirmation does not match.']
                        ])
                    ]
                )
            ),
        ]
    )]
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => 'رمز عبور با موفقیت تغییر کرد.',
        ]);
    }
}