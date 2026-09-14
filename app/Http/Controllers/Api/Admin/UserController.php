<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: '/api/admin/users',
        tags: ['Admin - Users'],
        summary: 'Get list of users',
        description: 'Returns a paginated list of all users ordered by latest. **Admin access required.**',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page (max 100)',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 15,
                    maximum: 100,
                    example: 15
                )
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    default: 1,
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of users retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "full_name", type: "string", example: "علی احمدی"),
                                    new OA\Property(property: "username", type: "string", example: "ali_ahmadi"),
                                    new OA\Property(property: "email", type: "string", format: "email", example: "ali@example.com"),
                                    new OA\Property(property: "mobile", type: "string", example: "09123456789"),
                                    new OA\Property(property: "birth_date", type: "string", format: "date", nullable: true, example: "2000-05-15"),
                                    new OA\Property(property: "national_code", type: "string", nullable: true, example: "1234567890"),
                                    new OA\Property(property: "postal_code", type: "string", nullable: true, example: "1234567890"),
                                    new OA\Property(property: "province", type: "string", nullable: true, example: "خراسان شمالی"),
                                    new OA\Property(property: "address", type: "string", nullable: true, example: "بجنورد، خیابان امام، پلاک ۱۲"),
                                    new OA\Property(property: "avatar", type: "string", nullable: true, example: "/avatars/avatar_5_1734567890.webp"),
                                    new OA\Property(property: "mobile_verified_at", type: "string", format: "date-time", nullable: true),
                                    new OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time"),
                                    new OA\Property(property: "updated_at", type: "string", format: "date-time"),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'links',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', example: 'http://localhost/api/admin/users?page=1'),
                                new OA\Property(property: 'last', type: 'string', example: 'http://localhost/api/admin/users?page=10'),
                                new OA\Property(property: 'prev', type: 'string', nullable: true, example: null),
                                new OA\Property(property: 'next', type: 'string', nullable: true, example: 'http://localhost/api/admin/users?page=2'),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'from', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 10),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'to', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 150),
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated - User is not logged in',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - User does not have admin access',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
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
                                'per_page' => ['The per page field must not be greater than 100.']
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min($request->integer('per_page', 15), 100);

        $users = User::query()
            ->latest()
            ->paginate($perPage);

        return UserResource::collection($users);
    }

    #[OA\Get(
        path: '/api/admin/users/{user}',
        tags: ['Admin - Users'],
        summary: 'Get a single user',
        description: 'Returns detailed information about a specific user. **Admin access required.**',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "full_name", type: "string", example: "علی احمدی"),
                                new OA\Property(property: "username", type: "string", example: "ali_ahmadi"),
                                new OA\Property(property: "email", type: "string", format: "email", example: "ali@example.com"),
                                new OA\Property(property: "mobile", type: "string", example: "09123456789"),
                                new OA\Property(property: "birth_date", type: "string", format: "date", nullable: true, example: "2000-05-15"),
                                new OA\Property(property: "national_code", type: "string", nullable: true, example: "1234567890"),
                                new OA\Property(property: "postal_code", type: "string", nullable: true, example: "1234567890"),
                                new OA\Property(property: "province", type: "string", nullable: true, example: "خراسان شمالی"),
                                new OA\Property(property: "address", type: "string", nullable: true, example: "بجنورد، خیابان امام، پلاک ۱۲"),
                                new OA\Property(property: "avatar", type: "string", nullable: true, example: "/avatars/avatar_5_1734567890.webp"),
                                new OA\Property(property: "mobile_verified_at", type: "string", format: "date-time", nullable: true),
                                new OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true),
                                new OA\Property(property: "created_at", type: "string", format: "date-time"),
                                new OA\Property(property: "updated_at", type: "string", format: "date-time"),
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
                        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\User] 1')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated - User is not logged in',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - User does not have admin access',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.')
                    ]
                )
            )
        ]
    )]
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }
}