<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use OpenApi\Attributes as OA;

class AdminRoleController extends Controller
{
    // =========================
    // SYNC ROLES
    // =========================
    #[OA\Put(
        path: '/api/admin/admins/{admin}/roles',
        tags: ['Admin - Roles'],
        summary: 'Sync admin roles',
        description: 'Synchronize roles for a specific admin user. This will replace all existing roles with the new ones.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'admin',
                in: 'path',
                required: true,
                description: 'Admin ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['roles'],
                properties: [
                    new OA\Property(
                        property: 'roles',
                        type: 'array',
                        description: 'Array of role names to assign to the admin',
                        minItems: 1,
                        items: new OA\Items(
                            type: 'string',
                            example: 'admin'
                        ),
                        example: ['admin', 'editor', 'viewer']
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Roles updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Roles updated successfully.'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The roles field is required.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'roles',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['The roles field is required.']
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Admin not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Admin not found.'),
                    ]
                )
            ),
        ]
    )]
    public function sync(Request $request, Admin $admin): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $admin->syncRoles($validated['roles']);

        return response()->json([
            'message' => 'Roles updated successfully.',
        ]);
    }

    // =========================
    // SHOW ROLES
    // =========================
    #[OA\Get(
        path: '/api/admin/admins/{admin}/roles',
        tags: ['Admin - Roles'],
        summary: 'Get admin roles',
        description: 'Get all roles assigned to a specific admin user',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'admin',
                in: 'path',
                required: true,
                description: 'Admin ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Roles retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            description: 'Array of role names assigned to the admin',
                            items: new OA\Items(
                                type: 'string',
                                example: 'admin'
                            ),
                            example: ['admin', 'editor', 'viewer']
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Admin not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Admin not found.'),
                    ]
                )
            ),
        ]
    )]
    public function show(Admin $admin): JsonResponse
    {
        return response()->json([
            'roles' => $admin->roles()->pluck('name'),
        ]);
    }
}