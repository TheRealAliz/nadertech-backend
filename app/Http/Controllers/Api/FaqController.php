<?php

namespace App\Http\Controllers\Api;

use App\Models\Faq;
use App\Http\Controllers\Controller;
use App\Http\Resources\Faq\FaqResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class FaqController extends Controller
{
    #[OA\Get(
        path: '/api/faqs',
        tags: ['FAQs'],
        summary: 'Get list of active FAQs',
        description: 'Returns a list of all active FAQs ordered by sort order (ordered method).',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of FAQs retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'faqs',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'question', type: 'string', example: 'چگونه می‌توانم ثبت‌نام کنم؟'),
                                            new OA\Property(property: 'answer', type: 'string', example: 'برای ثبت‌نام کافی است به صفحه ثبت‌نام مراجعه کرده و اطلاعات خود را وارد کنید.'),
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
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $faqs = Faq::query()
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'data' => [
                'faqs' => FaqResource::collection($faqs),
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/faqs/{faq}',
        tags: ['FAQs'],
        summary: 'Get a single FAQ',
        description: 'Returns detailed information about a specific FAQ. Only active FAQs are accessible.',
        parameters: [
            new OA\Parameter(
                name: 'faq',
                description: 'FAQ ID',
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
                description: 'FAQ retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'faq',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'question', type: 'string', example: 'چگونه می‌توانم ثبت‌نام کنم؟'),
                                        new OA\Property(property: 'answer', type: 'string', example: 'برای ثبت‌نام کافی است به صفحه ثبت‌نام مراجعه کرده و اطلاعات خود را وارد کنید.'),
                                        new OA\Property(property: 'sort_order', type: 'integer', example: 1),
                                        new OA\Property(property: 'is_active', type: 'boolean', example: true),
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
                description: 'FAQ not found or inactive',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'سوال مورد نظر یافت نشد.')
                    ]
                )
            )
        ]
    )]
    public function show(Faq $faq): JsonResponse
    {
        if (!$faq || !$faq->is_active) {
            return response()->json([
                'message' => 'سوال مورد نظر یافت نشد.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'faq' => new FaqResource($faq),
            ]
        ]);
    }
}