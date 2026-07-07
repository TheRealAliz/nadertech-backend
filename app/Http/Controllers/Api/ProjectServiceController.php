<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectRequest\ProjectServiceResource;
use App\Models\ProjectService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ProjectServiceController extends Controller
{
    #[OA\Get(
        path: '/api/project-services',
        tags: ['Project Services'],
        summary: 'Get list of all project services',
        description: 'Returns a flat list of all project services ordered by parent_id and sort_order.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of project services retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'services',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
                                            new OA\Property(property: 'title', type: 'string', example: 'طراحی وبسایت'),
                                            new OA\Property(property: 'slug', type: 'string', example: 'web-design'),
                                            new OA\Property(property: 'description', type: 'string', nullable: true, example: 'طراحی وبسایت های شرکتی، فروشگاهی و شخصی'),
                                            new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                                            new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                            new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                            new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                        ],
                                        type: 'object'
                                    )
                                )
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Server Error')
                    ]
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $services = ProjectService::query()
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => [
                'services' => ProjectServiceResource::collection($services),
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/project-services/{service}',
        tags: ['Project Services'],
        summary: 'Get a single project service by slug',
        description: 'Returns detailed information about a specific project service including its children. Only active services are accessible.',
        parameters: [
            new OA\Parameter(
                name: 'service',
                description: 'Project Service slug (unique identifier)',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'web-design'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project service retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'service',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
                                        new OA\Property(property: 'title', type: 'string', example: 'طراحی وبسایت'),
                                        new OA\Property(property: 'slug', type: 'string', example: 'web-design'),
                                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'طراحی وبسایت های شرکتی، فروشگاهی و شخصی'),
                                        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                        new OA\Property(
                                            property: 'children',
                                            type: 'array',
                                            items: new OA\Items(
                                                properties: [
                                                    new OA\Property(property: 'id', type: 'integer', example: 2),
                                                    new OA\Property(property: 'parent_id', type: 'integer', example: 1),
                                                    new OA\Property(property: 'title', type: 'string', example: 'طراحی فروشگاهی'),
                                                    new OA\Property(property: 'slug', type: 'string', example: 'ecommerce-design'),
                                                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'طراحی وبسایت فروشگاهی با ووکامرس'),
                                                    new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                                                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                                    new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                                    new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                                ],
                                                type: 'object'
                                            ),
                                            description: 'List of child services (if any)'
                                        ),
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
                response: 404,
                description: 'Project service not found or inactive',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'خدمت مورد نظر یافت نشد.')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Server Error')
                    ]
                )
            )
        ]
    )]
    public function show(ProjectService $service): JsonResponse
    {
        if (!$service || !$service->is_active) {
            return response()->json([
                'message' => 'خدمت مورد نظر یافت نشد.',
            ], 404);
        }

        $service->load('children');

        return response()->json([
            'data' => [
                'service' => new ProjectServiceResource($service),
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/project-services/tree',
        tags: ['Project Services'],
        summary: 'Get project services tree',
        description: 'Returns a hierarchical tree of project services. Only parent services (with no parent) are returned, each containing their children.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project services tree retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'services',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
                                            new OA\Property(property: 'title', type: 'string', example: 'طراحی وبسایت'),
                                            new OA\Property(property: 'slug', type: 'string', example: 'web-design'),
                                            new OA\Property(property: 'description', type: 'string', nullable: true, example: 'طراحی وبسایت های شرکتی، فروشگاهی و شخصی'),
                                            new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                                            new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                            new OA\Property(
                                                property: 'children',
                                                type: 'array',
                                                items: new OA\Items(
                                                    properties: [
                                                        new OA\Property(property: 'id', type: 'integer', example: 2),
                                                        new OA\Property(property: 'parent_id', type: 'integer', example: 1),
                                                        new OA\Property(property: 'title', type: 'string', example: 'طراحی فروشگاهی'),
                                                        new OA\Property(property: 'slug', type: 'string', example: 'ecommerce-design'),
                                                        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'طراحی وبسایت فروشگاهی با ووکامرس'),
                                                        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                                                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
                                                        new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                                        new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                                    ],
                                                    type: 'object'
                                                ),
                                                description: 'List of child services'
                                            ),
                                            new OA\Property(property: 'created_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                            new OA\Property(property: 'updated_at', type: 'string', format: 'datetime', example: '2026-06-20T10:30:00.000000Z'),
                                        ],
                                        type: 'object'
                                    )
                                )
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Server Error')
                    ]
                )
            )
        ]
    )]
    public function tree(): JsonResponse
    {
        $services = ProjectService::with('children')
            ->parents()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => [
                'services' => ProjectServiceResource::collection($services),
            ]
        ]);
    }
}