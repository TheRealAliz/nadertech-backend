<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use OpenApi\Attributes as OA;

class RolePermissionController extends Controller
{
    // =========================
    // SYNC PERMISSIONS
    // =========================
    #[OA\Put(
        path: '/api/admin/roles/{role}/permissions',
        tags: ['Admin - Roles & Permissions'],
        summary: 'Sync role permissions',
        description: 'Synchronize permissions for a specific role. This will replace all existing permissions with the new ones.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'path',
                required: true,
                description: 'Role ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['permissions'],
                properties: [
                    new OA\Property(
                        property: 'permissions',
                        type: 'array',
                        description: 'Array of permission names to assign to the role',
                        minItems: 1,
                        items: new OA\Items(
                            type: 'string',
                            example: 'admin.resumes.view'
                        ),
                        example: ['admin.resumes.view', 'admin.resumes.create', 'admin.resumes.update']
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permissions updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Permissions updated successfully.'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The permissions field is required.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'permissions',
                                    type: 'array',
                                    items: new OA\Items(type: 'string'),
                                    example: ['The permissions field is required.']
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
                description: 'Role not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Role not found.'),
                    ]
                )
            ),
        ]
    )]
    public function sync(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message' => 'Permissions updated successfully.',
        ]);
    }

    // =========================
    // SHOW PERMISSIONS
    // =========================
    #[OA\Get(
        path: '/api/admin/roles/{role}/permissions',
        tags: ['Admin - Roles & Permissions'],
        summary: 'Get role permissions',
        description: 'Get all permissions assigned to a specific role',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'path',
                required: true,
                description: 'Role ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Permissions retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'permissions',
                            type: 'array',
                            description: 'Array of permission names assigned to the role',
                            items: new OA\Items(
                                type: 'string',
                                example: 'admin.resumes.view'
                            ),
                            example: ['admin.resumes.view', 'admin.resumes.create', 'admin.resumes.update', 'admin.resumes.delete']
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
                description: 'Role not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Role not found.'),
                    ]
                )
            ),
        ]
    )]
    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'permissions' => $role->permissions()->pluck('name'),
        ]);
    }
}