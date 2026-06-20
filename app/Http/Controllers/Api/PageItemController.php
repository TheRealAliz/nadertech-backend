<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'key', type: 'string', example: 'title', description: 'Item key identifier'),
                            new OA\Property(property: 'value', type: 'string', example: 'نادر تکنولوژی فقط یک نام نیست؛ یک نگاه است.', description: 'Item content value'),
                            new OA\Property(property: 'type', type: 'string', enum: ['text', 'html', 'image_path', 'json', 'number', 'boolean'], example: 'text', description: 'Content type'),
                            new OA\Property(property: 'page', type: 'string', example: 'about', description: 'Page name')
                        ]
                    )
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
            ->get(['key', 'value', 'type', 'page']);

        return response()->json($items);
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
                example: 'about-us',
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
                        new OA\Property(property: 'key', type: 'string', example: 'title', description: 'Item key identifier'),
                        new OA\Property(property: 'value', type: 'string', example: 'نادر تکنولوژی فقط یک نام نیست؛ یک نگاه است.', description: 'Item content value'),
                        new OA\Property(property: 'type', type: 'string', enum: ['text', 'html', 'image_path', 'json', 'number', 'boolean'], example: 'text', description: 'Content type'),
                        new OA\Property(property: 'page', type: 'string', example: 'about', description: 'Page name')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error - Missing parameters',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'پارامتر صفحه و کلید الزامی می‌باشند.')
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
            ->first(['key', 'value', 'type', 'page']);

        if (!$item) {
            return response()->json([
                'message' => 'آیتم یافت نشد.'
            ], 404);
        }

        return response()->json($item);
    }

    public function updateSingle(Request $request, string $page, string $key)
    {
        $validated = $request->validate([
            'value' => 'required'
        ]);

        $pageItem = PageItem::updateOrCreate(
            [
                'page' => $page,
                'key' => $key
            ],
            [
                'value' => $validated['value']
            ]
        );

        return response()->json([
            'message' => 'updated successfully',
            'data' => $pageItem
        ]);
    }

    public function updateBulk(Request $request, string $page)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            PageItem::updateOrCreate(
                [
                    'page' => $page,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        $pageItems = PageItem::query()
            ->where('page', '=', $page)
            ->get(['key', 'value', 'type', 'page']);

        return response()->json([
            'message' => 'updated successfully',
            'data' => $pageItems
        ]);
    }
}