<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectService\StoreProjectServiceRequest;
use App\Http\Requests\Admin\ProjectService\UpdateProjectServiceRequest;
use App\Http\Resources\ProjectRequest\ProjectServiceResource;
use App\Models\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
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

    #[OA\Post(
        path: '/api/project-services',
        tags: ['Project Services'],
        summary: 'Create a new project service',
        description: 'Creates a new project service with the provided data.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null, description: 'ID of parent service'),
                    new OA\Property(property: 'title', type: 'string', example: 'خدمت جدید', description: 'Service title'),
                    new OA\Property(property: 'slug', type: 'string', example: 'new-service', description: 'Unique slug'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'توضیحات خدمت', description: 'Service description'),
                    new OA\Property(property: 'sort_order', type: 'integer', example: 1, description: 'Sort order'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true, description: 'Active status'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Service created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'service',
                                    ref: '#/components/schemas/ProjectServiceResource'
                                )
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'خدمت با موفقیت ایجاد شد.')
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
    public function store(StoreProjectServiceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!isset($validated['sort_order'])) {
            $maxSortOrder = ProjectService::where('parent_id', $validated['parent_id'] ?? null)
                ->max('sort_order');
            $validated['sort_order'] = $maxSortOrder !== null ? $maxSortOrder + 1 : 1;
        }

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        if (!isset($validated['slug']) && isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $service = ProjectService::create($validated);

        return response()->json([
            'data' => [
                'service' => new ProjectServiceResource($service->load('children')),
            ],
            'message' => 'خدمت با موفقیت ایجاد شد.'
        ], 201);
    }

    #[OA\Put(
        path: '/api/project-services/{service}',
        tags: ['Project Services'],
        summary: 'Update a project service',
        description: 'Updates an existing project service with the provided data.',
        parameters: [
            new OA\Parameter(
                name: 'service',
                description: 'Project Service ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    example: 1
                )
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null, description: 'ID of parent service'),
                    new OA\Property(property: 'title', type: 'string', example: 'خدمت ویرایش شده', description: 'Service title'),
                    new OA\Property(property: 'slug', type: 'string', example: 'updated-service', description: 'Unique slug'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'توضیحات جدید', description: 'Service description'),
                    new OA\Property(property: 'sort_order', type: 'integer', example: 2, description: 'Sort order'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: false, description: 'Active status'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'service',
                                    ref: '#/components/schemas/ProjectServiceResource'
                                )
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'خدمت با موفقیت به‌روزرسانی شد.')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Service not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'خدمت مورد نظر یافت نشد.')
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
    public function update(UpdateProjectServiceRequest $request, ProjectService $service): JsonResponse
    {
        $validated = $request->validated();

        // اگر slug ارسال شده و با slug فعلی متفاوت است
        if (isset($validated['slug']) && $validated['slug'] !== $service->slug) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // اگر sort_order ارسال نشده، تغییر نمی‌دهیم
        // اگر sort_order ارسال شده و با sort_order فعلی متفاوت است
        if (isset($validated['sort_order']) && $validated['sort_order'] !== $service->sort_order) {
            // می‌توانیم منطقی برای بازآرایی sort_order پیاده‌سازی کنیم
            // به عنوان مثال: تمام آیتم‌های با sort_order بزرگتر را یک عدد افزایش دهیم
            $this->reorderSortOrders($validated['parent_id'] ?? $service->parent_id, $service->sort_order, $validated['sort_order']);
        }

        $service->update($validated);

        return response()->json([
            'data' => new ProjectServiceResource($service->load('children')),
            'message' => 'خدمت با موفقیت به‌روزرسانی شد.'
        ]);
    }

    private function reorderSortOrders(?int $parentId, int $oldSortOrder, int $newSortOrder): void
    {
        if ($oldSortOrder == $newSortOrder) {
            return;
        }

        // اگر sort_order جدید بزرگتر است، آیتم‌های بین old و new را کاهش بده
        if ($newSortOrder > $oldSortOrder) {
            ProjectService::where('parent_id', $parentId)
                ->where('sort_order', '>', $oldSortOrder)
                ->where('sort_order', '<=', $newSortOrder)
                ->decrement('sort_order');
        }
        // اگر sort_order جدید کوچکتر است، آیتم‌های بین new و old را افزایش بده
        else {
            ProjectService::where('parent_id', $parentId)
                ->where('sort_order', '>=', $newSortOrder)
                ->where('sort_order', '<', $oldSortOrder)
                ->increment('sort_order');
        }
    }

    #[OA\Delete(
        path: '/api/project-services/{service}',
        tags: ['Project Services'],
        summary: 'Delete a project service',
        description: 'Deletes a project service and all its children (cascade delete).',
        parameters: [
            new OA\Parameter(
                name: 'service',
                description: 'Project Service ID',
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
                description: 'Service deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'خدمت با موفقیت حذف شد.')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Service not found',
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
    public function destroy(ProjectService $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'message' => 'خدمت با موفقیت حذف شد.'
        ]);
    }
}
