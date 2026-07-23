<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageItem\StorePageItemRequest;
use App\Http\Resources\Page\PageItemResource;
use App\Models\PageItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PageItemController extends Controller
{
    #[OA\Get(
        path: '/api/page',
        tags: ['Page Items'],
        summary: 'Get all page items',
        description: 'Retrieves all key-value items for a specific page (e.g., about, contact, home)',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: 'about',
                description: 'Page identifier (about, contact, home, etc.)'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/PageItemResource')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error - Missing page parameter',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'پارامتر صفحه الزامی می‌باشد.')
                    ]
                )
            )
        ]
    )]
    public function index(string $page): JsonResponse
    {
        if (!$page) {
            return response()->json([
                'message' => 'پارامتر صفحه الزامی می‌باشد.'
            ], 400);
        }

        $items = PageItem::query()
            ->where('page', '=', $page)
            ->get();

        return response()->json(
            [
                'data' => $items->map(fn($item) => new PageItemResource($item))
            ]
        );
    }

    #[OA\Get(
        path: '/api/page/item',
        tags: ['Page Items'],
        summary: 'Get a specific page item by key',
        description: 'Retrieves a single item from a page using its key identifier',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: 'about',
                description: 'Page identifier'
            ),
            new OA\Parameter(
                name: 'key',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: 'title',
                description: 'Item key (e.g., title, description_top, banner_image)'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'page_item',
                                    ref: '#/components/schemas/PageItemResource'
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error - Missing parameters',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'پارامترهای صفحه و کلید الزامی می‌باشند.')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Item not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'آیتم یافت نشد.')
                    ]
                )
            )
        ]
    )]
    public function show(string $page, string $key): JsonResponse
    {
        if (!$page || !$key) {
            return response()->json([
                'message' => 'پارامترهای صفحه و کلید الزامی می‌باشند.'
            ], 400);
        }

        $item = PageItem::query()
            ->where('page', '=', $page)
            ->where('key', '=', $key)
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'آیتم یافت نشد.'
            ], 404);
        }

        return response()->json(
            [
                'data' => [
                    'page_item' => new PageItemResource($item)
                ]
            ]
        );
    }

    #[OA\Post(
        path: '/api/admin/page',
        tags: ['Admin - Page Items'],
        summary: 'Create or update a page item',
        description: 'Creates a new page item or updates an existing one by page and key',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['page', 'key', 'value', 'type'],
                properties: [
                    new OA\Property(
                        property: 'page',
                        type: 'string',
                        description: 'Page identifier',
                        example: 'about'
                    ),
                    new OA\Property(
                        property: 'key',
                        type: 'string',
                        description: 'Item key identifier',
                        example: 'title'
                    ),
                    new OA\Property(
                        property: 'value',
                        type: 'string',
                        description: 'Item content value',
                        example: 'نادر تکنولوژی فقط یک نام نیست؛ یک نگاه است.'
                    ),
                    new OA\Property(
                        property: 'type',
                        type: 'string',
                        enum: ['text', 'html', 'image_path', 'json', 'number', 'boolean'],
                        description: 'Content type',
                        example: 'text'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated or created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Updated or created successfully'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/PageItemResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The page field is required.'),
                        new OA\Property(
                            property: 'errors',
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
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.')
                    ]
                )
            )
        ]
    )]
    public function updateOrCreate(StorePageItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pageItem = PageItem::updateOrCreate(
            [
                'page' => $validated['page'],
                'key' => $validated['key']
            ],
            [
                'value' => $validated['value'],
                'type' => $validated['type']
            ]
        );

        return response()->json([
            'message' => 'Updated or created successfully',
            'data' => new PageItemResource($pageItem),
        ]);
    }

    #[OA\Delete(
        path: '/api/admin/page/{pageItem}',
        tags: ['Admin - Page Items'],
        summary: 'Delete a page item',
        description: 'Permanently delete a specific page item',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'pageItem',
                in: 'path',
                required: true,
                description: 'Page Item ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Deleted successfully')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Page item not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Page item not found')
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
                response: 403,
                description: 'Forbidden',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Forbidden.')
                    ]
                )
            )
        ]
    )]
    public function destroy(PageItem $pageItem)
    {
        $pageItem->delete();

        return response()->json([
            'message' => 'Deleted successfully',
        ]);
    }
}